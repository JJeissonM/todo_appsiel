<?php

namespace App\FacturacionElectronica\Services;

use App\Core\ConsecutivoDocumento;
use App\Core\TipoDocApp;
use App\FacturacionElectronica\ConversionTrace;
use App\Sistema\TipoTransaccion;
use App\Ventas\VtasDocEncabezado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentInvoiceElectronicConversionService
{
    public function candidatos(array $filters)
    {
        $standardTransactionId = $this->standardTransactionId();

        $query = VtasDocEncabezado::with(['tipo_documento_app', 'tercero', 'datos_auxiliares_estudiante'])
            ->where('core_tipo_transaccion_id', $standardTransactionId)
            ->whereHas('datos_auxiliares_estudiante');

        if (!empty($filters['empresa_id'])) {
            $query->where('core_empresa_id', (int)$filters['empresa_id']);
        }

        if (!empty($filters['ids'])) {
            $query->whereIn('id', $filters['ids']);
        }

        if (!empty($filters['lote'])) {
            $query->where('descripcion', 'LIKE', '%Lote: ' . $filters['lote'] . '%');
        }

        if (!empty($filters['fecha'])) {
            $query->where('fecha', $filters['fecha']);
        }

        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        return $query->orderBy('id');
    }

    public function convertir($documentHeaderId, array $filters, $dryRun)
    {
        if ($dryRun) {
            return $this->simular($documentHeaderId, $filters);
        }

        $modificadoPor = $this->usuarioActual();
        $feTransactionId = $this->electronicTransactionId();
        $feDocumentTypeId = $this->electronicDocumentTypeId();

        return DB::transaction(function () use ($documentHeaderId, $filters, $modificadoPor, $feTransactionId, $feDocumentTypeId) {
            $documento = VtasDocEncabezado::where('id', $documentHeaderId)->lockForUpdate()->first();

            if (is_null($documento)) {
                return $this->resultado('error', $documentHeaderId, '', '', 'Factura no encontrada.');
            }

            $origen = $this->datosDocumento($documento);

            if ((int)$documento->core_tipo_transaccion_id === $feTransactionId) {
                $this->registrarTraza($documento, $origen, $origen, 'omitido', $filters, 'La factura ya era electronica.', $modificadoPor);
                return $this->resultado('omitido', $documento->id, $this->label($origen), $this->label($origen), 'La factura ya era electronica.');
            }

            if ((int)$documento->core_tipo_transaccion_id !== $this->standardTransactionId()) {
                $this->registrarTraza($documento, $origen, [], 'omitido', $filters, 'La factura no es una factura estandar de estudiantes.', $modificadoPor);
                return $this->resultado('omitido', $documento->id, $this->label($origen), '', 'La factura no es una factura estandar de estudiantes.');
            }

            if (is_null($documento->datos_auxiliares_estudiante)) {
                $this->registrarTraza($documento, $origen, [], 'omitido', $filters, 'La factura no tiene relacion con cartera de estudiante.', $modificadoPor);
                return $this->resultado('omitido', $documento->id, $this->label($origen), '', 'La factura no tiene relacion con cartera de estudiante.');
            }

            $consecutivo = ConsecutivoDocumento::where('core_empresa_id', $documento->core_empresa_id)
                ->where('core_documento_app_id', $feDocumentTypeId)
                ->lockForUpdate()
                ->first();

            if (is_null($consecutivo)) {
                $consecutivo = new ConsecutivoDocumento();
                $consecutivo->core_empresa_id = $documento->core_empresa_id;
                $consecutivo->core_documento_app_id = $feDocumentTypeId;
                $consecutivo->consecutivo_actual = 0;
                $consecutivo->save();
            }

            $nuevoConsecutivo = (int)$consecutivo->consecutivo_actual + 1;
            $consecutivo->consecutivo_actual = $nuevoConsecutivo;
            $consecutivo->save();

            $destino = [
                'core_empresa_id' => $documento->core_empresa_id,
                'core_tipo_transaccion_id' => $feTransactionId,
                'core_tipo_doc_app_id' => $feDocumentTypeId,
                'consecutivo' => $nuevoConsecutivo,
            ];

            $existeDestino = VtasDocEncabezado::where($destino)->where('id', '<>', $documento->id)->exists();
            if ($existeDestino) {
                $this->registrarTraza($documento, $origen, $destino, 'error', $filters, 'Ya existe un documento con el destino asignado.', $modificadoPor);
                throw new \Exception('Ya existe un documento con el destino asignado: ' . $this->label($destino));
            }

            $conteos = $this->actualizarMovimientos($origen, $destino, $modificadoPor);

            $documento->core_tipo_transaccion_id = $feTransactionId;
            $documento->core_tipo_doc_app_id = $feDocumentTypeId;
            $documento->consecutivo = $nuevoConsecutivo;
            $documento->estado = 'Contabilizado - Sin enviar';
            $documento->modificado_por = $modificadoPor;
            $documento->save();

            $this->registrarTraza($documento, $origen, $destino, 'convertido', $filters, 'Convertida para envio posterior a la DIAN.', $modificadoPor, $conteos);

            return $this->resultado('convertido', $documento->id, $this->label($origen), $this->label($destino), 'Contabilizado - Sin enviar');
        });
    }

    protected function simular($documentHeaderId, array $filters)
    {
        $documento = VtasDocEncabezado::with(['tipo_documento_app', 'datos_auxiliares_estudiante'])->find($documentHeaderId);

        if (is_null($documento)) {
            return $this->resultado('error', $documentHeaderId, '', '', 'Factura no encontrada.');
        }

        $origen = $this->datosDocumento($documento);

        if ((int)$documento->core_tipo_transaccion_id !== $this->standardTransactionId()) {
            return $this->resultado('omitido', $documento->id, $this->label($origen), '', 'No es factura estandar.');
        }

        if (is_null($documento->datos_auxiliares_estudiante)) {
            return $this->resultado('omitido', $documento->id, $this->label($origen), '', 'No esta asociada a cartera de estudiante.');
        }

        $destino = [
            'core_empresa_id' => $documento->core_empresa_id,
            'core_tipo_transaccion_id' => $this->electronicTransactionId(),
            'core_tipo_doc_app_id' => $this->electronicDocumentTypeId(),
            'consecutivo' => 0,
        ];

        return $this->resultado('simulado', $documento->id, $this->label($origen), $this->label($destino, true), 'Se convertiria y quedaria Contabilizado - Sin enviar.');
    }

    protected function actualizarMovimientos(array $origen, array $destino, $modificadoPor)
    {
        $updates = [
            'core_tipo_transaccion_id' => $destino['core_tipo_transaccion_id'],
            'core_tipo_doc_app_id' => $destino['core_tipo_doc_app_id'],
            'consecutivo' => $destino['consecutivo'],
            'modificado_por' => $modificadoPor,
        ];

        $conteos = [];
        $tablas = ['vtas_movimientos', 'cxc_movimientos', 'contab_movimientos', 'teso_movimientos'];

        foreach ($tablas as $tabla) {
            if (!Schema::hasTable($tabla)) {
                $conteos[$tabla] = 0;
                continue;
            }

            $conteos[$tabla] = DB::table($tabla)->where($origen)->update($this->agregarUpdatedAtSiExiste($tabla, $updates));
        }

        $conteos['cxc_abonos_doc_cxc'] = DB::table('cxc_abonos')
            ->where([
                'core_empresa_id' => $origen['core_empresa_id'],
                'doc_cxc_transacc_id' => $origen['core_tipo_transaccion_id'],
                'doc_cxc_tipo_doc_id' => $origen['core_tipo_doc_app_id'],
                'doc_cxc_consecutivo' => $origen['consecutivo'],
            ])
            ->update([
                'doc_cxc_transacc_id' => $destino['core_tipo_transaccion_id'],
                'doc_cxc_tipo_doc_id' => $destino['core_tipo_doc_app_id'],
                'doc_cxc_consecutivo' => $destino['consecutivo'],
                'modificado_por' => $modificadoPor,
            ] + $this->updatedAtSiExiste('cxc_abonos'));

        $conteos['cxc_abonos_doc_cruce'] = DB::table('cxc_abonos')
            ->where([
                'core_empresa_id' => $origen['core_empresa_id'],
                'doc_cruce_transacc_id' => $origen['core_tipo_transaccion_id'],
                'doc_cruce_tipo_doc_id' => $origen['core_tipo_doc_app_id'],
                'doc_cruce_consecutivo' => $origen['consecutivo'],
            ])
            ->update([
                'doc_cruce_transacc_id' => $destino['core_tipo_transaccion_id'],
                'doc_cruce_tipo_doc_id' => $destino['core_tipo_doc_app_id'],
                'doc_cruce_consecutivo' => $destino['consecutivo'],
                'modificado_por' => $modificadoPor,
            ] + $this->updatedAtSiExiste('cxc_abonos'));

        return $conteos;
    }

    protected function agregarUpdatedAtSiExiste($tabla, array $values)
    {
        return $values + $this->updatedAtSiExiste($tabla);
    }

    protected function updatedAtSiExiste($tabla)
    {
        if (Schema::hasColumn($tabla, 'updated_at')) {
            return ['updated_at' => date('Y-m-d H:i:s')];
        }

        return [];
    }

    protected function registrarTraza($documento, array $origen, array $destino, $estado, array $filters, $motivo, $usuario, array $metadata = [])
    {
        if (!Schema::hasTable('fe_conversion_traces')) {
            return;
        }

        ConversionTrace::create([
            'core_empresa_id' => $origen['core_empresa_id'],
            'vtas_doc_encabezado_id' => $documento->id,
            'origen_core_tipo_transaccion_id' => $origen['core_tipo_transaccion_id'],
            'origen_core_tipo_doc_app_id' => $origen['core_tipo_doc_app_id'],
            'origen_consecutivo' => $origen['consecutivo'],
            'destino_core_tipo_transaccion_id' => isset($destino['core_tipo_transaccion_id']) ? $destino['core_tipo_transaccion_id'] : null,
            'destino_core_tipo_doc_app_id' => isset($destino['core_tipo_doc_app_id']) ? $destino['core_tipo_doc_app_id'] : null,
            'destino_consecutivo' => isset($destino['consecutivo']) ? $destino['consecutivo'] : null,
            'estado' => $estado,
            'referencia' => $this->referencia($filters),
            'motivo' => $motivo,
            'metadata' => json_encode(['filters' => $filters, 'updates' => $metadata]),
            'creado_por' => $usuario,
            'modificado_por' => $usuario,
        ]);
    }

    protected function datosDocumento($documento)
    {
        return [
            'core_empresa_id' => (int)$documento->core_empresa_id,
            'core_tipo_transaccion_id' => (int)$documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => (int)$documento->core_tipo_doc_app_id,
            'consecutivo' => (int)$documento->consecutivo,
        ];
    }

    protected function label(array $documento, $pendiente = false)
    {
        if (empty($documento)) {
            return '';
        }

        $tipoDocumento = TipoDocApp::find((int)$documento['core_tipo_doc_app_id']);
        $prefijo = is_null($tipoDocumento) ? ('DocApp ' . $documento['core_tipo_doc_app_id']) : $tipoDocumento->prefijo;
        $consecutivo = $pendiente ? 'siguiente consecutivo' : $documento['consecutivo'];

        return $prefijo . ' ' . $consecutivo;
    }

    protected function referencia(array $filters)
    {
        if (!empty($filters['lote'])) {
            return 'Lote: ' . $filters['lote'];
        }

        if (!empty($filters['fecha'])) {
            return 'Fecha: ' . $filters['fecha'];
        }

        if (!empty($filters['ids'])) {
            return 'Ids: ' . implode(',', $filters['ids']);
        }

        return 'Conversion facturas estudiantes';
    }

    protected function resultado($estado, $id, $origen, $destino, $mensaje)
    {
        return (object)[
            'estado' => $estado,
            'id' => $id,
            'origen' => $origen,
            'destino' => $destino,
            'mensaje' => $mensaje,
        ];
    }

    protected function usuarioActual()
    {
        if (Auth::check()) {
            return Auth::user()->email;
        }

        return 'console';
    }

    protected function standardTransactionId()
    {
        return (int)config('matriculas.transaccion_id_factura_estudiante', 23);
    }

    protected function electronicTransactionId()
    {
        $transactionId = (int)config('facturacion_electronica.transaction_type_id_default', 52);

        return $transactionId > 0 ? $transactionId : 52;
    }

    protected function electronicDocumentTypeId()
    {
        $documentTypeId = (int)config('facturacion_electronica.document_type_id_default');

        if ($documentTypeId > 0) {
            return $documentTypeId;
        }

        $transaction = TipoTransaccion::find($this->electronicTransactionId());
        if (!is_null($transaction) && !is_null($transaction->tipos_documentos->first())) {
            return (int)$transaction->tipos_documentos->first()->id;
        }

        throw new \Exception('No hay tipo de documento configurado para factura electronica.');
    }
}
