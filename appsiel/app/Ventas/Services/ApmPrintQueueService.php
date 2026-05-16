<?php

namespace App\Ventas\Services;

use App\Ventas\ApmPrintJob;
use App\Ventas\ApmPrintStatus;
use App\Ventas\ApmDevice;
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

    public function getManageableJobs($limit = 30)
    {
        return ApmPrintJob::whereIn('apm_print_status_id', [
                $this->getStatusId('pending'),
                $this->getStatusId('printed'),
                $this->getStatusId('retired')
            ])
            ->orderByRaw('CASE WHEN apm_print_status_id = ? THEN 0 ELSE 1 END', [$this->getStatusId('pending')])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
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

        $documentJobs = ApmPrintJob::where('core_tipo_transaccion_id', $meta['core_tipo_transaccion_id'])
            ->where('core_tipo_doc_app_id', $meta['core_tipo_doc_app_id'])
            ->where('consecutivo', $meta['consecutivo'])
            ->where('document_type', $meta['document_type'])
            ->orderBy('copy_number', 'desc')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();
        $lastDocumentJob = $documentJobs->first();
        $documentDataJob = $this->findJobWithDocumentData($documentJobs);

        $copyNumber = is_null($lastDocumentJob) ? 0 : ((int) $lastDocumentJob->copy_number + 1);
        $payload = $this->fillMissingDocumentDataFromPreviousJob($payload, $documentDataJob);

        $copyLabel = $this->buildCopyLabel($copyNumber);
        $payload = $this->applyCopyLabel($payload, $copyLabel);
        $payload = $this->normalizePayloadTextFields($payload);
        $payload = $this->applyCurrentDeviceConfig($payload);

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

    public function prepareReprint($jobId, $forceCopy = false, $retryOnly = false)
    {
        $job = ApmPrintJob::findOrFail($jobId);

        if ($retryOnly) {
            $this->validateRetryableFailedJob($job);
        }

        if (!$forceCopy && (int) $job->apm_print_status_id === (int) $this->getStatusId('pending')) {
            $storedPayload = json_decode($job->payload_json, true);
            $payload = is_array($storedPayload) ? $storedPayload : [];
            $payload = $this->fillMissingDocumentDataFromPreviousJob($payload, $this->findJobWithDocumentData($this->getDocumentJobs($job)));
            $payload = $this->applyCopyLabel($payload, $job->copy_label);
            $payload = $this->normalizePayloadTextFields($payload);
            $payload = $this->applyCurrentDeviceConfig($payload);
            $job->payload_json = json_encode($payload);
            $job->save();

            return [
                'job' => $job,
                'payload' => $payload
            ];
        }

        $payload = json_decode($job->payload_json, true);
        $copyNumber = $this->nextCopyNumber($job);
        $copyLabel = $this->buildCopyLabel($copyNumber);
        $payload = is_array($payload) ? $payload : [];
        $payload = $this->fillMissingDocumentDataFromPreviousJob($payload, $this->findJobWithDocumentData($this->getDocumentJobs($job)));
        $payload = $this->applyCopyLabel($payload, $copyLabel);
        $payload = $this->normalizePayloadTextFields($payload);
        $payload = $this->applyCurrentDeviceConfig($payload);

        $user = Auth::user();
        $newJob = ApmPrintJob::create([
            'core_empresa_id' => $job->core_empresa_id,
            'core_tipo_transaccion_id' => $job->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $job->core_tipo_doc_app_id,
            'consecutivo' => $job->consecutivo,
            'apm_print_status_id' => $this->getStatusId('pending'),
            'document_type' => $job->document_type,
            'document_label' => $job->document_label,
            'copy_number' => $copyNumber,
            'copy_label' => $copyLabel,
            'printer_id' => isset($payload['PrinterId']) ? $payload['PrinterId'] : $job->printer_id,
            'station_id' => isset($payload['StationId']) ? $payload['StationId'] : $job->station_id,
            'payload_json' => json_encode($payload),
            'attempts_count' => 0,
            'last_error' => null,
            'queued_by' => is_null($user) ? null : $user->email,
            'queued_at' => Carbon::now()->toDateTimeString()
        ]);

        return [
            'job' => $newJob,
            'payload' => $payload
        ];
    }

    protected function validateRetryableFailedJob(ApmPrintJob $job)
    {
        if (!is_null($job->printed_at)) {
            throw new \RuntimeException('Imprimir ahora solo aplica para trabajos con error que nunca se han impreso.');
        }

        if (trim((string) $job->last_error) === '') {
            throw new \RuntimeException('Imprimir ahora solo aplica para trabajos con error de impresion.');
        }

        if ((int) $job->apm_print_status_id !== (int) $this->getStatusId('pending')) {
            throw new \RuntimeException('Imprimir ahora solo aplica para trabajos pendientes en la cola APM.');
        }
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

    public function markAttempted($jobId, $message)
    {
        $job = ApmPrintJob::findOrFail($jobId);

        if ((int) $job->apm_print_status_id !== (int) $this->getStatusId('pending')) {
            throw new \RuntimeException('El trabajo seleccionado ya no esta pendiente en la cola de APM.');
        }

        $job->attempts_count = (int) $job->attempts_count + 1;
        $job->last_attempt_at = Carbon::now()->toDateTimeString();
        $job->last_error = $message;
        $job->save();

        return $job;
    }

    public function markPending($jobId, $retryOnly = false)
    {
        $job = ApmPrintJob::findOrFail($jobId);

        if ($retryOnly) {
            $this->validateRetryableFailedJob($job);
        }

        $payload = json_decode($job->payload_json, true);

        $payload = is_array($payload) ? $payload : [];
        $payload = $this->fillMissingDocumentDataFromPreviousJob($payload, $this->findJobWithDocumentData($this->getDocumentJobs($job)));
        $payload = $this->applyCopyLabel($payload, $job->copy_label);
        $payload = $this->normalizePayloadTextFields($payload);
        $payload = $this->applyCurrentDeviceConfig($payload);

        $job->apm_print_status_id = $this->getStatusId('pending');
        $job->payload_json = json_encode($payload);
        $job->last_error = null;
        $job->retired_at = null;
        $job->retired_by = null;
        $job->queued_at = Carbon::now()->toDateTimeString();
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

    public function markRetired($jobId)
    {
        $job = ApmPrintJob::findOrFail($jobId);

        if ((int) $job->apm_print_status_id !== (int) $this->getStatusId('pending')) {
            throw new \RuntimeException('El trabajo seleccionado ya no esta pendiente en la cola de APM.');
        }

        $user = Auth::user();

        $job->apm_print_status_id = $this->getStatusId('retired');
        $job->retired_at = Carbon::now()->toDateTimeString();
        $job->retired_by = is_null($user) ? null : $user->email;
        $job->save();

        return $job;
    }

    public function markCancelled($jobId)
    {
        $job = ApmPrintJob::findOrFail($jobId);
        $user = Auth::user();

        $job->apm_print_status_id = $this->getStatusId('cancelled');
        $job->retired_at = Carbon::now()->toDateTimeString();
        $job->retired_by = is_null($user) ? null : $user->email;
        $job->save();

        return $job;
    }

    public function deleteJob($jobId)
    {
        $job = ApmPrintJob::findOrFail($jobId);
        $job->delete();
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
            'printed_at' => $job->printed_at,
            'retired_by' => $job->retired_by,
            'retired_at' => $job->retired_at
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

        $payload['COPY'] = $copyLabel;
        $payload['CopyLabel'] = $copyLabel;

        if (isset($payload['Document']['order']) && is_array($payload['Document']['order'])) {
            $payload['Document']['order'] = $this->removeLegacyCopyLabelsFromOrder($payload['Document']['order'], $copyLabel);
            $payload['Document']['order']['COPY'] = $copyLabel;
            $payload['Document']['order']['CopyLabel'] = $copyLabel;
            unset($payload['Document']['order']['RestaurantLabel']);
        }

        if (isset($payload['Document']['sale']) && is_array($payload['Document']['sale'])) {
            unset($payload['Document']['sale']['COPY']);
            unset($payload['Document']['sale']['CopyLabel']);
        }

        if (isset($payload['Document']['header']) && is_array($payload['Document']['header'])) {
            unset($payload['Document']['header']['COPY']);
            unset($payload['Document']['header']['CopyLabel']);
        }

        if (isset($payload['Document']['egreso']) && is_array($payload['Document']['egreso'])) {
            unset($payload['Document']['egreso']['COPY']);
            unset($payload['Document']['egreso']['CopyLabel']);
        }

        if (isset($payload['Document']['cheque']) && is_array($payload['Document']['cheque'])) {
            unset($payload['Document']['cheque']['COPY']);
            unset($payload['Document']['cheque']['CopyLabel']);
        }

        $payload['Document']['COPY'] = $copyLabel;
        $payload['Document']['CopyLabel'] = $copyLabel;

        return $payload;
    }

    protected function removeLegacyCopyLabelsFromOrder(array $order, $currentCopyLabel)
    {
        $restaurantName = isset($order['RestaurantName']) ? trim((string) $order['RestaurantName']) : '';
        $restaurantLabel = isset($order['RestaurantLabel']) ? trim((string) $order['RestaurantLabel']) : '';

        if ($restaurantName !== '' && $this->isCopyLabelText($restaurantName, $currentCopyLabel)) {
            if ($restaurantLabel !== '' && !$this->isCopyLabelText($restaurantLabel, $currentCopyLabel)) {
                $order['RestaurantName'] = $restaurantLabel;
            } else {
                unset($order['RestaurantName']);
            }
        }

        return $order;
    }

    protected function isCopyLabelText($value, $currentCopyLabel)
    {
        $value = trim((string) $value);
        $currentCopyLabel = trim((string) $currentCopyLabel);

        if ($value === '' || $value === $currentCopyLabel || strtoupper($value) === 'ORIGINAL') {
            return true;
        }

        return preg_match('/^COPIA\s*#\s*\d+$/i', $value) === 1;
    }

    protected function normalizePayloadTextFields($payload)
    {
        if (!is_array($payload)) {
            return [];
        }

        foreach (['JobId', 'StationId', 'PrinterId', 'DocumentType', 'COPY', 'CopyLabel'] as $field) {
            if (isset($payload[$field])) {
                $payload[$field] = (string)$payload[$field];
            }
        }

        if (!isset($payload['Document']) || !is_array($payload['Document'])) {
            return $payload;
        }

        foreach (['company', 'customer'] as $section) {
            if (!isset($payload['Document'][$section]) || !is_array($payload['Document'][$section])) {
                continue;
            }

            foreach (['Name', 'Nit', 'Address', 'Phone', 'Email', 'City', 'IdType'] as $field) {
                if (isset($payload['Document'][$section][$field])) {
                    $payload['Document'][$section][$field] = (string)$payload['Document'][$section][$field];
                }
            }
        }

        if (isset($payload['Document']['seller']) && is_array($payload['Document']['seller']) && isset($payload['Document']['seller']['Name'])) {
            $payload['Document']['seller']['Name'] = (string)$payload['Document']['seller']['Name'];
        }

        if (isset($payload['Document']['sale']) && is_array($payload['Document']['sale'])) {
            foreach (['Number', 'Date'] as $field) {
                if (isset($payload['Document']['sale'][$field])) {
                    $payload['Document']['sale'][$field] = (string)$payload['Document']['sale'][$field];
                }
            }

            if (isset($payload['Document']['sale']['Items']) && is_array($payload['Document']['sale']['Items'])) {
                foreach ($payload['Document']['sale']['Items'] as $key => $item) {
                    if (is_array($item) && isset($item['Name'])) {
                        $payload['Document']['sale']['Items'][$key]['Name'] = (string)$item['Name'];
                    }
                }
            }
        }

        if (isset($payload['Document']['order']) && is_array($payload['Document']['order'])) {
            foreach (['Number', 'Date', 'COPY', 'CopyLabel', 'RestaurantName'] as $field) {
                if (isset($payload['Document']['order'][$field])) {
                    $payload['Document']['order'][$field] = (string)$payload['Document']['order'][$field];
                }
            }
        }

        foreach (['COPY', 'CopyLabel'] as $field) {
            if (isset($payload['Document'][$field])) {
                $payload['Document'][$field] = (string)$payload['Document'][$field];
            }
        }

        return $payload;
    }

    protected function fillMissingDocumentDataFromPreviousJob(array $payload, $previousJob)
    {
        if (is_null($previousJob) || trim((string) $previousJob->payload_json) === '') {
            return $payload;
        }

        $previousPayload = json_decode($previousJob->payload_json, true);
        if (!is_array($previousPayload) || !isset($previousPayload['Document']) || !is_array($previousPayload['Document'])) {
            return $payload;
        }

        if (!isset($payload['Document']) || !is_array($payload['Document'])) {
            $payload['Document'] = [];
        }

        $payload = $this->fillMissingCompanyData($payload, $previousPayload);
        $payload = $this->fillMissingOrderData($payload, $previousPayload);

        foreach (['labels', 'resolution'] as $section) {
            if ($this->isEmptyPayloadValue(isset($payload['Document'][$section]) ? $payload['Document'][$section] : null)
                && isset($previousPayload['Document'][$section])) {
                $payload['Document'][$section] = $previousPayload['Document'][$section];
            }
        }

        return $payload;
    }

    protected function findJobWithDocumentData($jobs)
    {
        foreach ($jobs as $job) {
            if (trim((string) $job->payload_json) === '') {
                continue;
            }

            $payload = json_decode($job->payload_json, true);
            if (!is_array($payload) || !isset($payload['Document']) || !is_array($payload['Document'])) {
                continue;
            }

            $companyName = isset($payload['Document']['company']['Name']) ? $payload['Document']['company']['Name'] : null;
            $restaurantName = isset($payload['Document']['order']['RestaurantName']) ? $payload['Document']['order']['RestaurantName'] : null;

            if (!$this->isEmptyPayloadValue($companyName) || !$this->isEmptyPayloadValue($restaurantName)) {
                return $job;
            }
        }

        return null;
    }

    protected function getDocumentJobs(ApmPrintJob $job)
    {
        return ApmPrintJob::where('core_tipo_transaccion_id', $job->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $job->core_tipo_doc_app_id)
            ->where('consecutivo', $job->consecutivo)
            ->where('document_type', $job->document_type)
            ->orderBy('copy_number', 'desc')
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();
    }

    protected function fillMissingCompanyData(array $payload, array $previousPayload)
    {
        if (!isset($previousPayload['Document']['company']) || !is_array($previousPayload['Document']['company'])) {
            return $payload;
        }

        if (!isset($payload['Document']['company']) || !is_array($payload['Document']['company'])) {
            $payload['Document']['company'] = [];
        }

        foreach (['Name', 'Nit', 'Address', 'Phone', 'Email', 'City', 'IdType'] as $field) {
            if ($this->isEmptyPayloadValue(isset($payload['Document']['company'][$field]) ? $payload['Document']['company'][$field] : null)
                && !$this->isEmptyPayloadValue(isset($previousPayload['Document']['company'][$field]) ? $previousPayload['Document']['company'][$field] : null)) {
                $payload['Document']['company'][$field] = $previousPayload['Document']['company'][$field];
            }
        }

        return $payload;
    }

    protected function fillMissingOrderData(array $payload, array $previousPayload)
    {
        if (!isset($previousPayload['Document']['order']) || !is_array($previousPayload['Document']['order'])) {
            return $payload;
        }

        if (!isset($payload['Document']['order']) || !is_array($payload['Document']['order'])) {
            $payload['Document']['order'] = [];
        }

        foreach (['RestaurantName', 'RestaurantLabel'] as $field) {
            if ($this->isEmptyPayloadValue(isset($payload['Document']['order'][$field]) ? $payload['Document']['order'][$field] : null)
                && !$this->isEmptyPayloadValue(isset($previousPayload['Document']['order'][$field]) ? $previousPayload['Document']['order'][$field] : null)) {
                $payload['Document']['order'][$field] = $previousPayload['Document']['order'][$field];
            }
        }

        return $payload;
    }

    protected function isEmptyPayloadValue($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return count($value) === 0;
        }

        return false;
    }

    protected function applyCurrentDeviceConfig(array $payload)
    {
        $printerId = isset($payload['PrinterId']) ? trim((string) $payload['PrinterId']) : '';

        if ($printerId === '') {
            return $payload;
        }

        $device = ApmDevice::where('device_id', $printerId)->first();

        if (is_null($device)) {
            unset($payload['DeviceConfig']);
            return $payload;
        }

        $payload['DeviceConfig'] = [
            'device_type' => $device->device_type,
            'device_id' => $device->device_id,
            'name' => $device->device_name,
            'beep_after_print' => (int) $device->beep_after_print,
            'open_drawer_after_print' => (int) $device->open_drawer_after_print,
            'cut_after_print' => (int) $device->cut_after_print,
            'serial_port' => $device->serial_port,
            'baud_rate' => (int) $device->baud_rate,
            'data_bits' => (int) $device->data_bits,
            'parity' => $device->parity,
            'stop_bits' => $device->stop_bits
        ];

        $payload['BeepAfterPrint'] = (int) $device->beep_after_print === 1;
        $payload['OpenDrawerAfterPrint'] = (int) $device->open_drawer_after_print === 1;
        $payload['OpenDrawer'] = (int) $device->open_drawer_after_print === 1;
        $payload['CutAfterPrint'] = (int) $device->cut_after_print === 1;

        if (!isset($payload['Document']) || !is_array($payload['Document'])) {
            $payload['Document'] = [];
        }

        $payload['Document']['OpenDrawerAfterPrint'] = $payload['OpenDrawerAfterPrint'];
        $payload['Document']['OpenDrawer'] = $payload['OpenDrawer'];
        $payload['Document']['CutAfterPrint'] = $payload['CutAfterPrint'];

        return $payload;
    }

    protected function buildCopyLabel($copyNumber)
    {
        if ((int) $copyNumber === 0) {
            return 'ORIGINAL';
        }

        return 'COPIA # ' . (int) $copyNumber;
    }

    protected function nextCopyNumber(ApmPrintJob $job)
    {
        $maxCopyNumber = ApmPrintJob::where('core_tipo_transaccion_id', $job->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $job->core_tipo_doc_app_id)
            ->where('consecutivo', $job->consecutivo)
            ->where('document_type', $job->document_type)
            ->max('copy_number');

        return is_null($maxCopyNumber) ? 1 : ((int) $maxCopyNumber + 1);
    }

    protected function getStatusId($code)
    {
        if (isset($this->statusIds[$code])) {
            return $this->statusIds[$code];
        }

        $defaults = [
            'pending' => 'Pendiente de reimpresion manual',
            'printed' => 'Impreso correctamente',
            'cancelled' => 'Cancelado manualmente',
            'retired' => 'Retirado manualmente'
        ];

        if (!isset($defaults[$code])) {
            throw new \RuntimeException('No existe la definicion del estado de cola APM: ' . $code);
        }

        $status = ApmPrintStatus::firstOrCreate(
            ['code' => $code],
            ['description' => $defaults[$code]]
        );

        $this->statusIds[$code] = (int) $status->id;

        return $this->statusIds[$code];
    }
}
