<?php

namespace App\Hotel\Services;

use App\Contabilidad\ContabMovimiento;
use App\Core\EncabezadoDocumentoTransaccion;
use App\CxC\CxcMovimiento;
use App\Hotel\HotelStay;
use App\Http\Controllers\CxC\DocCruceController;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelReceivableService
{
    const TOLERANCE = 0.01;

    public function pendingInvoices(HotelStay $stay)
    {
        return $this->customerMovements($stay)
            ->where('cxc_movimientos.saldo_pendiente', '>', 0.1)
            ->orderBy('cxc_movimientos.fecha')
            ->get();
    }

    public function availableAdvances(HotelStay $stay)
    {
        return $this->customerMovements($stay)
            ->where('cxc_movimientos.saldo_pendiente', '<', -0.1)
            ->orderBy('cxc_movimientos.fecha')
            ->get();
    }

    public function register(HotelStay $stay, array $invoiceIds, array $advanceIds, array $paymentMethods)
    {
        $service = $this;

        return DB::transaction(function () use ($service, $stay, $invoiceIds, $advanceIds, $paymentMethods) {
            $invoices = $service->lockMovements($stay, $invoiceIds, true);
            if ($invoices->count() == 0) {
                throw new \InvalidArgumentException('Debe seleccionar al menos una factura pendiente por cobrar.');
            }

            $invoiceTerceroIds = $invoices->pluck('core_tercero_id')->unique()->values();
            if ($invoiceTerceroIds->count() != 1) {
                throw new \InvalidArgumentException('Seleccione facturas de un solo huésped por recaudo.');
            }

            $terceroId = (int)$invoiceTerceroIds->first();

            $invoiceTotal = (float)$invoices->sum('saldo_pendiente');
            $advances = $service->lockMovements($stay, $advanceIds, false, $terceroId);
            $advanceAvailable = abs((float)$advances->sum('saldo_pendiente'));
            $advanceApplied = min($invoiceTotal, $advanceAvailable);
            $cashRequired = $invoiceTotal - $advanceApplied;
            $paymentTotal = $service->validatePaymentMethods($stay, $paymentMethods, $cashRequired);

            $crossDocumentId = null;
            if ($advanceApplied > self::TOLERANCE) {
                $crossDocumentId = $service->applyAdvances($stay, $invoices, $advances, $advanceApplied, $terceroId);
            }

            $receiptId = null;
            if ($paymentTotal > self::TOLERANCE) {
                $pdv = (new HotelService())->currentCashierPdvOrFail();
                if ((int)$pdv->core_empresa_id != (int)$stay->empresa_id) {
                    throw new \InvalidArgumentException('El punto de venta asociado al usuario no pertenece a la empresa de la estadía.');
                }

                $manager = app(\App\Core\Services\TurnoManager::class);
                $turno = null;
                if ($manager->enabledForContext($stay->empresa_id, 'hotel', 'pdv', $pdv->id)
                    || $manager->enabledForContext($stay->empresa_id, 'tesoreria', 'pdv', $pdv->id)) {
                    $turno = $manager->requireCurrent($stay->empresa_id, 'hotel', 'pdv', $pdv->id);
                } else {
                    $turno = $manager->currentForContext($stay->empresa_id, 'pdv', $pdv->id);
                }

                if (is_null($turno)) {
                    $receiptId = $service->createReceipt($stay, $invoices, $advanceApplied, $paymentMethods, $terceroId, $pdv->id);
                } else {
                    $receiptId = app(\App\Core\Services\TurnoContext::class)->run($turno, function () use ($service, $stay, $invoices, $advanceApplied, $paymentMethods, $terceroId, $pdv) {
                        return $service->createReceipt($stay, $invoices, $advanceApplied, $paymentMethods, $terceroId, $pdv->id);
                    });
                }
            }

            return array(
                'receipt_id' => $receiptId,
                'cross_document_id' => $crossDocumentId,
                'invoice_total' => $invoiceTotal,
                'advance_applied' => $advanceApplied,
                'payment_total' => $paymentTotal,
            );
        });
    }

    private function customerMovements(HotelStay $stay)
    {
        $terceroIds = $this->terceroIds($stay);

        return CxcMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_movimientos.core_tercero_id')
            ->where('cxc_movimientos.core_empresa_id', $stay->empresa_id)
            ->whereIn('cxc_movimientos.core_tercero_id', $terceroIds)
            ->where('cxc_movimientos.fecha', '<=', date('Y-m-d'))
            ->select(
                'cxc_movimientos.*',
                'core_terceros.descripcion AS guest_name',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo, " ", cxc_movimientos.consecutivo) AS documento')
            );
    }

    private function lockMovements(HotelStay $stay, array $ids, $positive, $terceroId = null)
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (count($ids) == 0) {
            return collect();
        }

        $query = CxcMovimiento::where('core_empresa_id', $stay->empresa_id)
            ->whereIn('core_tercero_id', $this->terceroIds($stay))
            ->whereIn('id', $ids)
            ->lockForUpdate();

        if (!is_null($terceroId)) {
            $query->where('core_tercero_id', (int)$terceroId);
        }

        if ($positive) {
            $query->where('saldo_pendiente', '>', 0.1);
        } else {
            $query->where('saldo_pendiente', '<', -0.1);
        }

        $rows = $query->get();
        if ($rows->count() != count($ids)) {
            throw new \InvalidArgumentException('Uno de los documentos seleccionados ya no está disponible o no pertenece al huésped. Actualice la página e intente nuevamente.');
        }

        return $rows;
    }

    private function validatePaymentMethods(HotelStay $stay, array $paymentMethods, $required)
    {
        $total = 0;
        foreach ($paymentMethods as $index => $row) {
            $value = isset($row['value']) ? (float)$row['value'] : 0;
            if ($value <= 0) {
                throw new \InvalidArgumentException('El valor de cada medio de pago debe ser mayor que cero.');
            }

            $medium = TesoMedioRecaudo::where('id', isset($row['medium_id']) ? (int)$row['medium_id'] : 0)
                ->where('estado', TesoMedioRecaudo::ESTADO_ACTIVO)
                ->first();
            if (is_null($medium)) {
                throw new \InvalidArgumentException('El medio de pago de la fila ' . ($index + 1) . ' no está disponible.');
            }
            if ($this->isCash($medium)) {
                $this->validCashBox($stay, isset($row['cash_box_id']) ? $row['cash_box_id'] : 0);
            } else {
                $this->validBankAccount($stay, isset($row['bank_account_id']) ? $row['bank_account_id'] : 0);
            }

            $total += $value;
        }

        if (abs($total - $required) > self::TOLERANCE) {
            if ($total < $required) {
                throw new \InvalidArgumentException('El recaudo está incompleto. Falta registrar $ ' . number_format($required - $total, 2, ',', '.') . ' en medios de pago. Los anticipos son opcionales.');
            }

            throw new \InvalidArgumentException('Los medios de pago superan en $ ' . number_format($total - $required, 2, ',', '.') . ' el valor pendiente del recaudo. Ajuste el valor para continuar.');
        }

        return $total;
    }

    private function applyAdvances(HotelStay $stay, $invoices, $advances, $amount, $terceroId)
    {
        $remaining = $amount;
        $rows = array();

        foreach ($advances as $advance) {
            if ($remaining <= self::TOLERANCE) {
                break;
            }
            $value = min(abs((float)$advance->saldo_pendiente), $remaining);
            $rows[] = array('cxc_movimiento_id' => $advance->id, 'valor_aplicar' => $value * -1);
            $remaining -= $value;
        }

        $remaining = $amount;
        foreach ($invoices as $invoice) {
            if ($remaining <= self::TOLERANCE) {
                break;
            }
            $value = min((float)$invoice->saldo_pendiente, $remaining);
            $rows[] = array('cxc_movimiento_id' => $invoice->id, 'valor_aplicar' => $value);
            $remaining -= $value;
        }
        $rows[] = array('cxc_movimiento_id' => '', 'valor_aplicar' => '');

        $request = new Request(array(
            'core_empresa_id' => $stay->empresa_id,
            'core_tipo_doc_app_id' => (int)config('ventas.cruces_cxc_tipo_doc_app_id'),
            'core_tipo_transaccion_id' => (int)config('ventas.cruces_cxc_tipo_transaccion_id'),
            'core_tercero_id' => $terceroId,
            'fecha' => date('Y-m-d'),
            'descripcion' => 'Cruce de anticipos desde estadía #' . $stay->id,
            'documento_soporte' => 'Estadía #' . $stay->id,
            'estado' => 'Activo',
            'creado_por' => Auth::user()->email,
            'modificado_por' => '0',
            'tabla_documentos_a_cancelar' => json_encode($rows),
            'url_id' => 22,
            'url_id_modelo' => (int)config('ventas.cruces_cxc_modelo_id'),
            'url_id_transaccion' => (int)config('ventas.cruces_cxc_tipo_transaccion_id'),
        ));

        (new DocCruceController())->store($request);

        return (int)DB::table('cxc_doc_encabezados')
            ->where('core_empresa_id', $stay->empresa_id)
            ->where('core_tipo_transaccion_id', (int)config('ventas.cruces_cxc_tipo_transaccion_id'))
            ->where('core_tercero_id', $terceroId)
            ->orderBy('id', 'DESC')
            ->value('id');
    }

    private function createReceipt(HotelStay $stay, $invoices, $advanceApplied, array $paymentMethods, $terceroId, $pdvId)
    {
        $receiptRequest = new Request(array(
            'core_empresa_id' => $stay->empresa_id,
            'core_tipo_transaccion_id' => (int)config('tesoreria.recaudos_cxc_tipo_transaccion_id'),
            'core_tipo_doc_app_id' => (int)config('tesoreria.recaudos_cxc_tipo_doc_app_id'),
            'fecha' => date('Y-m-d'),
            'core_tercero_id' => $terceroId,
            'codigo_referencia_tercero' => '',
            'teso_tipo_motivo' => 'recaudo-cartera',
            'documento_soporte' => 'Estadía #' . $stay->id,
            'descripcion' => 'Pago de facturas crédito desde estadía #' . $stay->id,
            'valor_total' => 0,
            'estado' => 'Activo',
            'creado_por' => Auth::user()->email,
            'modificado_por' => '0',
            'pdv_id' => (int)$pdvId,
        ));

        $headerFactory = new EncabezadoDocumentoTransaccion((int)config('tesoreria.recaudos_cxc_modelo_id'));
        $receipt = $headerFactory->crear_nuevo($receiptRequest->all());

        $remainingAdvance = $advanceApplied;
        $lines = array();
        foreach ($invoices as $invoice) {
            $balance = (float)$invoice->saldo_pendiente;
            $coveredByAdvance = min($balance, $remainingAdvance);
            $remainingAdvance -= $coveredByAdvance;
            $cashValue = $balance - $coveredByAdvance;
            if ($cashValue > self::TOLERANCE) {
                $lines[] = array('id_doc' => $invoice->id, 'abono' => $cashValue);
            }
        }
        $lines[] = array('id_doc' => '', 'abono' => '');
        $receiptRequest['lineas_registros'] = json_encode($lines);
        $receipt->almacenar_y_contabilizar_abonos_cxc($receiptRequest);

        $motivo = $this->receiptReason($stay);
        $total = 0;
        foreach ($paymentMethods as $row) {
            $total += $this->storePaymentMethod($stay, $receipt, $motivo, $row, $pdvId);
        }
        $receipt->valor_total = $total;
        $receipt->save();

        return $receipt->id;
    }

    private function storePaymentMethod(HotelStay $stay, $receipt, TesoMotivo $motivo, array $row, $pdvId)
    {
        $value = (float)$row['value'];
        $medium = TesoMedioRecaudo::find((int)$row['medium_id']);
        $cashBoxId = 0;
        $bankAccountId = 0;

        if ($this->isCash($medium)) {
            $destination = $this->validCashBox($stay, $row['cash_box_id']);
            $cashBoxId = $destination->id;
        } else {
            $destination = $this->validBankAccount($stay, $row['bank_account_id']);
            $bankAccountId = $destination->id;
        }

        $detail = 'recaudo-cartera';
        $common = array(
            'fecha' => $receipt->fecha,
            'core_empresa_id' => $receipt->core_empresa_id,
            'core_tercero_id' => $receipt->core_tercero_id,
            'core_tipo_transaccion_id' => $receipt->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $receipt->core_tipo_doc_app_id,
            'consecutivo' => $receipt->consecutivo,
            'teso_medio_recaudo_id' => $medium->id,
            'teso_motivo_id' => $motivo->id,
            'teso_caja_id' => $cashBoxId,
            'teso_cuenta_bancaria_id' => $bankAccountId,
            'estado' => 'Activo',
            'creado_por' => Auth::user()->email,
            'modificado_por' => '0',
        );

        TesoDocRegistro::create(array(
            'teso_encabezado_id' => $receipt->id,
            'detalle_operacion' => $detail,
            'valor' => $value,
        ) + $common);

        TesoMovimiento::create(array(
            'valor_movimiento' => $value,
            'descripcion' => $detail,
            'documento_soporte' => isset($row['reference']) ? trim($row['reference']) : '',
            'pdv_id' => (int)$pdvId,
        ) + $common);

        $accountingData = $common + array(
            'documento_soporte' => isset($row['reference']) ? trim($row['reference']) : '',
            'tipo_transaccion' => '',
        );
        (new ContabMovimiento())->contabilizar_linea_registro($accountingData, $destination->contab_cuenta_id, 'Contabilización recaudo estadía #' . $stay->id, $value, 0);

        return $value;
    }

    private function receiptReason(HotelStay $stay)
    {
        $query = TesoMotivo::where('core_empresa_id', $stay->empresa_id)
            ->where('estado', 'Activo')
            ->where('teso_tipo_motivo', 'recaudo-cartera')
            ->where('movimiento', 'entrada');

        $configured = (int)config('tesoreria.recaudos_cxc_motivo_id');
        $motivo = $configured > 0 ? (clone $query)->where('id', $configured)->first() : null;
        if (is_null($motivo)) {
            $motivo = $query->orderBy('id')->first();
        }
        if (is_null($motivo)) {
            throw new \InvalidArgumentException('No existe un motivo activo de recaudo de cartera para la empresa.');
        }

        return $motivo;
    }

    private function validCashBox(HotelStay $stay, $id)
    {
        $box = TesoCaja::where('id', (int)$id)
            ->where('core_empresa_id', $stay->empresa_id)
            ->where('estado', 'Activo')
            ->first();
        $allowedIds = TesoCaja::get_cajas_permitidas()->pluck('id')->toArray();
        if (is_null($box) || empty($box->contab_cuenta_id) || !in_array((int)$box->id, array_map('intval', $allowedIds))) {
            throw new \InvalidArgumentException('Debe seleccionar una caja activa con cuenta contable.');
        }

        return $box;
    }

    private function validBankAccount(HotelStay $stay, $id)
    {
        $account = TesoCuentaBancaria::where('id', (int)$id)
            ->where('core_empresa_id', $stay->empresa_id)
            ->where('estado', 'Activo')
            ->first();
        $allowedIds = TesoCuentaBancaria::get_cuentas_permitidas()->pluck('id')->toArray();
        if (is_null($account) || empty($account->contab_cuenta_id) || !in_array((int)$account->id, array_map('intval', $allowedIds))) {
            throw new \InvalidArgumentException('Debe seleccionar una cuenta bancaria activa con cuenta contable.');
        }

        return $account;
    }

    private function isCash(TesoMedioRecaudo $medium)
    {
        return strtolower(trim($medium->comportamiento)) == 'efectivo';
    }

    private function terceroIds(HotelStay $stay)
    {
        $ids = array();

        if (!is_null($stay->mainGuest) && !empty($stay->mainGuest->core_tercero_id)) {
            $ids[] = (int)$stay->mainGuest->core_tercero_id;
        }

        foreach ($stay->guests as $guest) {
            if (!is_null($guest->cliente) && !empty($guest->cliente->core_tercero_id)) {
                $ids[] = (int)$guest->cliente->core_tercero_id;
            }
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if (count($ids) == 0) {
            throw new \InvalidArgumentException('La estadía no tiene huéspedes válidos para consultar CxC.');
        }

        return $ids;
    }
}
