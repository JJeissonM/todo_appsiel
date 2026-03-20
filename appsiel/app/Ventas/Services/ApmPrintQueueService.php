<?php

namespace App\Ventas\Services;

use App\Ventas\ApmPrintJob;
use App\Ventas\ApmPrintStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ApmPrintQueueService
{
    protected $statusIds = [];

    public function getPendingJobs()
    {
        return ApmPrintJob::where('apm_print_status_id', $this->getStatusId('pending'))
            ->orderBy('queued_at', 'asc')
            ->get();
    }

    public function prepareJob(array $payload, array $documentMeta)
    {
        $meta = $this->normalizeDocumentMeta($documentMeta);

        $pendingJob = ApmPrintJob::where('core_tipo_transaccion_id', $meta['core_tipo_transaccion_id'])
            ->where('core_tipo_doc_app_id', $meta['core_tipo_doc_app_id'])
            ->where('consecutivo', $meta['consecutivo'])
            ->where('document_type', $meta['document_type'])
            ->where('apm_print_status_id', $this->getStatusId('pending'))
            ->first();

        if (!is_null($pendingJob)) {
            throw new \RuntimeException('Este documento ya esta pendiente en la cola de APM.');
        }

        $copyNumber = (int) ApmPrintJob::where('core_tipo_transaccion_id', $meta['core_tipo_transaccion_id'])
            ->where('core_tipo_doc_app_id', $meta['core_tipo_doc_app_id'])
            ->where('consecutivo', $meta['consecutivo'])
            ->where('document_type', $meta['document_type'])
            ->max('copy_number') + 1;

        $copyLabel = 'COPIA # ' . $copyNumber;
        $payload = $this->applyCopyLabel($payload, $copyLabel);

        $user = Auth::user();
        $job = ApmPrintJob::create([
            'core_empresa_id' => isset($meta['core_empresa_id']) ? $meta['core_empresa_id'] : 1,
            'core_tipo_transaccion_id' => $meta['core_tipo_transaccion_id'],
            'core_tipo_doc_app_id' => $meta['core_tipo_doc_app_id'],
            'consecutivo' => $meta['consecutivo'],
            'apm_print_status_id' => $this->getStatusId('pending'),
            'document_type' => $meta['document_type'],
            'document_label' => $meta['document_label'],
            'copy_number' => $copyNumber,
            'copy_label' => $copyLabel,
            'printer_id' => isset($payload['PrinterId']) ? $payload['PrinterId'] : null,
            'station_id' => isset($payload['StationId']) ? $payload['StationId'] : null,
            'payload_json' => json_encode($payload),
            'attempts_count' => 0,
            'last_error' => null,
            'queued_by' => is_null($user) ? null : $user->email,
            'queued_at' => Carbon::now()->toDateTimeString()
        ]);

        return [
            'job' => $job,
            'payload' => $payload
        ];
    }

    public function prepareReprint($jobId)
    {
        $job = ApmPrintJob::findOrFail($jobId);

        if ((int) $job->apm_print_status_id !== (int) $this->getStatusId('pending')) {
            throw new \RuntimeException('El trabajo seleccionado ya no esta pendiente en la cola de APM.');
        }

        return [
            'job' => $job,
            'payload' => json_decode($job->payload_json, true)
        ];
    }

    public function markPrinted($jobId)
    {
        $job = ApmPrintJob::findOrFail($jobId);
        $user = Auth::user();

        $job->apm_print_status_id = $this->getStatusId('printed');
        $job->attempts_count = (int) $job->attempts_count + 1;
        $job->last_attempt_at = Carbon::now()->toDateTimeString();
        $job->printed_at = Carbon::now()->toDateTimeString();
        $job->printed_by = is_null($user) ? null : $user->email;
        $job->last_error = null;
        $job->save();

        return $job;
    }

    public function markFailed($jobId, $errorMessage)
    {
        $job = ApmPrintJob::findOrFail($jobId);

        $job->apm_print_status_id = $this->getStatusId('pending');
        $job->attempts_count = (int) $job->attempts_count + 1;
        $job->last_attempt_at = Carbon::now()->toDateTimeString();
        $job->last_error = $errorMessage;
        $job->save();

        return $job;
    }

    public function serializeJob(ApmPrintJob $job)
    {
        return [
            'id' => $job->id,
            'core_tipo_transaccion_id' => (int) $job->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => (int) $job->core_tipo_doc_app_id,
            'consecutivo' => (int) $job->consecutivo,
            'document_type' => $job->document_type,
            'document_label' => $job->document_label,
            'copy_number' => (int) $job->copy_number,
            'copy_label' => $job->copy_label,
            'printer_id' => $job->printer_id,
            'station_id' => $job->station_id,
            'attempts_count' => (int) $job->attempts_count,
            'last_error' => $job->last_error,
            'queued_at' => $job->queued_at,
            'last_attempt_at' => $job->last_attempt_at,
            'printed_at' => $job->printed_at
        ];
    }

    protected function normalizeDocumentMeta(array $documentMeta)
    {
        $coreTipoTransaccionId = isset($documentMeta['core_tipo_transaccion_id']) ? (int) $documentMeta['core_tipo_transaccion_id'] : 0;
        $coreTipoDocAppId = isset($documentMeta['core_tipo_doc_app_id']) ? (int) $documentMeta['core_tipo_doc_app_id'] : 0;
        $consecutivo = isset($documentMeta['consecutivo']) ? (int) $documentMeta['consecutivo'] : 0;
        $documentType = isset($documentMeta['document_type']) ? trim($documentMeta['document_type']) : '';
        $documentLabel = isset($documentMeta['document_label']) ? trim($documentMeta['document_label']) : '';
        $coreEmpresaId = isset($documentMeta['core_empresa_id']) ? (int) $documentMeta['core_empresa_id'] : 1;

        if ($coreTipoTransaccionId === 0 || $coreTipoDocAppId === 0 || $consecutivo === 0 || $documentType === '') {
            throw new \InvalidArgumentException('No se pudo identificar el documento para la cola de APM.');
        }

        if ($documentLabel === '') {
            $documentLabel = strtoupper($documentType) . ' ' . $consecutivo;
        }

        return [
            'core_empresa_id' => $coreEmpresaId,
            'core_tipo_transaccion_id' => $coreTipoTransaccionId,
            'core_tipo_doc_app_id' => $coreTipoDocAppId,
            'consecutivo' => $consecutivo,
            'document_type' => $documentType,
            'document_label' => $documentLabel
        ];
    }

    protected function applyCopyLabel(array $payload, $copyLabel)
    {
        if (!isset($payload['Document']) || !is_array($payload['Document'])) {
            $payload['Document'] = [];
        }

        if (isset($payload['Document']['order']) && is_array($payload['Document']['order'])) {
            $payload['Document']['order']['COPY'] = $copyLabel;
        }

        if (isset($payload['Document']['sale']) && is_array($payload['Document']['sale'])) {
            $payload['Document']['sale']['COPY'] = $copyLabel;
        }

        if (isset($payload['Document']['header']) && is_array($payload['Document']['header'])) {
            $payload['Document']['header']['COPY'] = $copyLabel;
        }

        if (isset($payload['Document']['egreso']) && is_array($payload['Document']['egreso'])) {
            $payload['Document']['egreso']['COPY'] = $copyLabel;
        }

        if (isset($payload['Document']['cheque']) && is_array($payload['Document']['cheque'])) {
            $payload['Document']['cheque']['COPY'] = $copyLabel;
        }

        if (!isset($payload['Document']['COPY'])) {
            $payload['Document']['COPY'] = $copyLabel;
        }

        return $payload;
    }

    protected function getStatusId($code)
    {
        if (isset($this->statusIds[$code])) {
            return $this->statusIds[$code];
        }

        $defaults = [
            'pending' => 'Pendiente de reimpresion manual',
            'printed' => 'Impreso correctamente',
            'cancelled' => 'Cancelado manualmente'
        ];

        if (!isset($defaults[$code])) {
            throw new untimeException('No existe la definicion del estado de cola APM: ' . $code);
        }

        $status = ApmPrintStatus::firstOrCreate(
            ['code' => $code],
            ['description' => $defaults[$code]]
        );

        $this->statusIds[$code] = (int) $status->id;

        return $this->statusIds[$code];
    }
}
