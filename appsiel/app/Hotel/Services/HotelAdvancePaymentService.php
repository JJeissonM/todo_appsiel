<?php

namespace App\Hotel\Services;

use App\Contabilidad\Services\DocumentLinesService;
use App\Core\EncabezadoDocumentoTransaccion;
use App\CxC\CxcMovimiento;
use App\CxC\Services\AccountingServices;
use App\CxC\Services\CxcAccountingAccountResolver;
use App\Http\Controllers\CxC\DocCruceController;
use App\VentasPos\Services\CxCService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotelAdvancePaymentService
{
    // Solo se admiten anticipos del huésped del pedido o del destinatario.
    // La consulta bloquea los movimientos hasta finalizar la factura.
    public function validate($objectAnticipos, $empresaId, $guestTerceroId, $invoiceTerceroId, $invoiceTotal)
    {
        $advances = json_decode('[' . $objectAnticipos . ']', true);
        if (!is_array($advances) || count($advances) == 0) {
            throw new \InvalidArgumentException('Los anticipos aplicados no tienen un formato valido.');
        }

        $amounts = array();
        foreach ($advances as $advance) {
            $id = isset($advance['cxc_movimiento_id']) ? (int)$advance['cxc_movimiento_id'] : 0;
            $amount = isset($advance['valor_aplicar']) ? abs((float)$advance['valor_aplicar']) : 0;
            if ($id <= 0 || !is_finite($amount) || $amount <= 0) {
                throw new \InvalidArgumentException('Hay anticipos sin movimiento o valor valido.');
            }
            $amounts[$id] = (isset($amounts[$id]) ? $amounts[$id] : 0) + $amount;
        }
        ksort($amounts); // Orden estable para adquirir los bloqueos.
        if (array_sum($amounts) - (float)$invoiceTotal > 0.01) {
            throw new \InvalidArgumentException('Los anticipos aplicados superan el total de la factura hotelera.');
        }

        $selection = array();
        foreach ($amounts as $id => $amount) {
            $movement = CxcMovimiento::where('id', $id)
                ->where('core_empresa_id', $empresaId)->lockForUpdate()->first();
            if (is_null($movement)) {
                throw new \InvalidArgumentException('No se encontró el anticipo seleccionado en la empresa del pedido.');
            }
            if (!in_array((int)$movement->core_tercero_id, array((int)$guestTerceroId, (int)$invoiceTerceroId), true)) {
                throw new \InvalidArgumentException('El anticipo no pertenece al huésped del pedido ni al cliente a facturar.');
            }
            if ($movement->estado != 'Pendiente' || (float)$movement->valor_documento >= 0 ||
                (float)$movement->saldo_pendiente >= 0 || $amount - abs((float)$movement->saldo_pendiente) > 0.01) {
                throw new \InvalidArgumentException('El anticipo seleccionado no tiene saldo disponible suficiente.');
            }
            $movement->validar_saldo_disponible_para_abono(-$amount);
            $accountId = null;
            if ((int)$movement->core_tercero_id !== (int)$invoiceTerceroId) {
                $accountId = (new CxcAccountingAccountResolver())->getAdvanceAccountId($movement);
                if (empty($accountId)) {
                    throw new \RuntimeException('No se pudo determinar la cuenta contable del anticipo a trasladar.');
                }
            }
            $selection[] = array('movement' => $movement, 'amount' => $amount, 'account_id' => $accountId);
        }
        return $selection;
    }

    public function apply($invoice, $objectAnticipos, $guestTerceroId, $orderLabel)
    {
        return DB::transaction(function () use ($invoice, $objectAnticipos, $guestTerceroId, $orderLabel) {
            $selection = $this->validate($objectAnticipos, $invoice->core_empresa_id, $guestTerceroId,
                $invoice->core_tercero_id, $invoice->valor_total);
            $sourceAdvances = array();
            $invoiceAdvances = array();
            $accounts = array();
            foreach ($selection as $item) {
                $advance = array('cxc_movimiento_id' => $item['movement']->id, 'valor_aplicar' => -$item['amount']);
                if ((int)$item['movement']->core_tercero_id === (int)$invoice->core_tercero_id) {
                    $invoiceAdvances[] = $advance;
                } else {
                    $sourceAdvances[] = $advance;
                    $account = $item['account_id'];
                    $accounts[$account] = (isset($accounts[$account]) ? $accounts[$account] : 0) + $item['amount'];
                }
            }

            $note = null;
            $sourceCross = null;
            if (count($sourceAdvances) > 0) {
                $detail = 'Se hace nota contable para trasladar saldo del anticipo a nombre del huested hacia el cliente de la factura. Pedido ' . $orderLabel .
                    '. Factura ' . $invoice->get_label_documento();
                $note = $this->createTransferNote($invoice, $guestTerceroId, $accounts, $detail);
                $noteMovements = CxcMovimiento::where('core_empresa_id', $note->core_empresa_id)
                    ->where('core_tipo_transaccion_id', $note->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $note->core_tipo_doc_app_id)
                    ->where('consecutivo', $note->consecutivo)->orderBy('id')->get();
                $receivables = array();
                $transferredAdvances = array();
                foreach ($noteMovements as $movement) {
                    $row = array('cxc_movimiento_id' => $movement->id, 'valor_aplicar' => $movement->valor_documento);
                    if ((int)$movement->core_tercero_id === (int)$guestTerceroId && (float)$movement->valor_documento > 0) {
                        $receivables[] = $row;
                    } elseif ((int)$movement->core_tercero_id === (int)$invoice->core_tercero_id && (float)$movement->valor_documento < 0) {
                        $transferredAdvances[] = $row;
                    }
                }
                if (count($receivables) !== count($accounts) || count($transferredAdvances) !== count($accounts)) {
                    throw new \RuntimeException('La nota de traslado no generó los movimientos de CxC esperados.');
                }
                $sourceCross = $this->createSourceCross($note, $receivables, $sourceAdvances, $detail);
                $invoiceAdvances = array_merge($invoiceAdvances, $transferredAdvances);
            }

            $parts = array_map('json_encode', $invoiceAdvances);
            $request = (new CxCService())->build_request_object($invoice, implode(',', $parts));
            if (!is_null($note)) {
                $request['descripcion'] = $detail;
                $request['documento_soporte'] = $note->core_tipo_transaccion_id . '-' . $note->core_tipo_doc_app_id . '-' . $note->consecutivo;
            }
            $invoiceCross = (new DocCruceController())->guardar_cruce($request);
            return array('note' => $note, 'source_cross' => $sourceCross, 'invoice_cross' => $invoiceCross);
        });
    }

    protected function createTransferNote($invoice, $guestTerceroId, array $accounts, $detail)
    {
        $transactionId = (int)config('contabilidad.transaction_type_id_default');
        $documentTypeId = (int)config('contabilidad.document_type_id_default');
        if ($transactionId <= 0 || $documentTypeId <= 0) {
            throw new \RuntimeException('Configure el tipo de transacción y documento de la nota contable para trasladar anticipos.');
        }
        $note = (new EncabezadoDocumentoTransaccion(AccountingServices::CONTAB_DOC_HEADER_MODEL_ID))->crear_nuevo(array(
            'core_empresa_id' => $invoice->core_empresa_id,
            'core_tipo_transaccion_id' => $transactionId,
            'core_tipo_doc_app_id' => $documentTypeId,
            'core_tercero_id' => $guestTerceroId,
            'fecha' => $invoice->fecha,
            'documento_soporte' => $invoice->core_tipo_transaccion_id . '-' . $invoice->core_tipo_doc_app_id . '-' . $invoice->consecutivo,
            'descripcion' => $detail,
            'valor_total' => array_sum($accounts),
            'estado' => 'Activo',
            'creado_por' => $invoice->creado_por,
            'modificado_por' => '0',
        ));
        $lines = array();
        foreach ($accounts as $accountId => $amount) {
            $common = array('Cuenta' => $accountId . '-Anticipos clientes', 'Detalle' => $detail,
                'fecha_vencimiento' => $invoice->fecha, 'documento_soporte_tercero' => $note->documento_soporte);
            $lines[] = $common + array('Tercero' => $guestTerceroId . '-Huésped', 'tipo_transaccion' => 'crear_cxc',
                'debito' => '$' . $amount, 'credito' => '$0');
            $lines[] = $common + array('Tercero' => $invoice->core_tercero_id . '-Cliente factura', 'tipo_transaccion' => 'crear_anticipo_cxc',
                'debito' => '$0', 'credito' => '$' . $amount);
        }
        (new DocumentLinesService())->store_lines_and_accounting($note, json_encode($lines));
        return $note;
    }

    protected function createSourceCross($note, array $receivables, array $advances, $detail)
    {
        // La última fila es el total que espera el controlador de cruces.
        $rows = array_merge($advances, $receivables, array(array('cxc_movimiento_id' => '', 'valor_aplicar' => 0)));
        $request = new Request(array(
            'core_empresa_id' => $note->core_empresa_id,
            'core_tercero_id' => $note->core_tercero_id,
            'core_tipo_transaccion_id' => config('ventas.cruces_cxc_tipo_transaccion_id'),
            'core_tipo_doc_app_id' => config('ventas.cruces_cxc_tipo_doc_app_id'),
            'fecha' => $note->fecha,
            'descripcion' => $detail,
            'documento_soporte' => $note->documento_soporte,
            'estado' => 'Activo',
            'creado_por' => $note->creado_por,
            'modificado_por' => '0',
            'tabla_documentos_a_cancelar' => json_encode($rows),
            'url_id_modelo' => config('ventas.cruces_cxc_modelo_id'),
        ));
        return (new DocCruceController())->guardar_cruce($request);
    }
}
