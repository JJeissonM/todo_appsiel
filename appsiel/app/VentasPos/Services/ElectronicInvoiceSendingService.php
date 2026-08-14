<?php

namespace App\VentasPos\Services;

use App\FacturacionElectronica\Factura;
use App\FacturacionElectronica\Services\InvoiceTotalsService;
use App\VentasPos\FacturaPos;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ElectronicInvoiceSendingService
{
    public function send(Factura $invoice, $maxAttempts = 1, $retryDelayMs = 0)
    {
        try {
            $this->validate($invoice);
        } catch (\UnexpectedValueException $e) {
            Log::warning('POS_FE_TOTALS_MISMATCH', [
                'factura_id' => (int)$invoice->id,
                'contenido' => $e->getMessage(),
            ]);

            return $this->error($e->getMessage(), 0, false);
        }

        $maxAttempts = max(1, min(5, (int)$maxAttempts));
        $retryDelayMs = max(0, min(5000, (int)$retryDelayMs));
        $lastMessage = $this->error('No fue posible enviar la factura electronica.', 0, false);

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                $lastMessage = $this->normalizeMessage($invoice->enviar_al_proveedor_tecnologico());
            } catch (\Throwable $e) {
                $lastMessage = $this->error('Error inesperado durante el envio: ' . $e->getMessage(), $attempt, true);
            }

            $lastMessage->intentos = $attempt;
            if ($lastMessage->tipo != 'mensaje_error') {
                Log::info('POS_FE_SEND_SUCCESS', [
                    'factura_id' => (int)$invoice->id,
                    'intento' => $attempt,
                ]);

                return $lastMessage;
            }

            $retryable = $this->isRetryableError($lastMessage->contenido);
            $lastMessage->reintentable = $retryable;

            Log::warning('POS_FE_SEND_ERROR', [
                'factura_id' => (int)$invoice->id,
                'intento' => $attempt,
                'max_intentos' => $maxAttempts,
                'reintentable' => $retryable,
                'contenido' => (string)$lastMessage->contenido,
            ]);

            if (!$retryable || $attempt >= $maxAttempts) {
                return $lastMessage;
            }

            $this->waitBeforeRetry($retryDelayMs * $attempt);
        }

        return $lastMessage;
    }

    public function markAsSent(Factura $invoice, FacturaPos $posInvoice = null)
    {
        $posInvoiceId = !is_null($posInvoice)
            ? (int)$posInvoice->id
            : (int)$invoice->ventas_doc_relacionado_id;

        DB::transaction(function () use ($invoice, $posInvoiceId) {
            Factura::where('id', (int)$invoice->id)->update(['estado' => 'Enviada']);

            if ($posInvoiceId > 0) {
                FacturaPos::where('id', $posInvoiceId)->update(['estado' => 'Enviada']);
            }
        });

        $invoice->estado = 'Enviada';
        if (!is_null($posInvoice)) {
            $posInvoice->estado = 'Enviada';
        }
    }

    public function isRetryableError($content)
    {
        $content = strtolower(trim(strip_tags((string)$content)));
        $transientPatterns = [
            'error de red',
            'error de servidor',
            'error inesperado durante el envio',
            'timeout',
            'timed out',
            'curl error',
            'connection',
            'conexi',
            'http 429',
            'http 5',
            'batch en proceso',
            'proceso de validacion',
            'proceso de validación',
            'respuesta no valida',
            'respuesta no válida',
            'json invalido',
            'json inválido',
            'zip_key',
            'companynit',
        ];

        foreach ($transientPatterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function validate(Factura $invoice)
    {
        (new InvoiceTotalsService())->validateBeforeSend($invoice);
    }

    protected function waitBeforeRetry($milliseconds)
    {
        if ($milliseconds > 0) {
            usleep((int)$milliseconds * 1000);
        }
    }

    protected function normalizeMessage($message)
    {
        if (is_array($message)) {
            $message = (object)$message;
        }

        if (!is_object($message)) {
            return $this->error('Respuesta no valida del proveedor tecnologico.', 0, true);
        }

        if (!property_exists($message, 'tipo')) {
            $message->tipo = 'mensaje_error';
        }

        if (!property_exists($message, 'contenido')) {
            $message->contenido = '';
        }

        $content = trim((string)$message->contenido);
        if ($message->tipo == 'mensaje_error' && ($content == '' || $content == 'Error de Empresa:' || $content == 'Error de Empresa')) {
            $message->contenido = 'Respuesta no valida del proveedor tecnologico.';
        }

        return $message;
    }

    protected function error($content, $attempts, $retryable)
    {
        return (object)[
            'tipo' => 'mensaje_error',
            'contenido' => $content,
            'intentos' => (int)$attempts,
            'reintentable' => (bool)$retryable,
        ];
    }
}
