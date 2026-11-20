<?php

namespace App\FacturacionElectronica\OSEI;

use Exception;
use GuzzleHttp\Client;

/**
 * PDF y reenvío de correo de un documento (FEV, NC, ND o DS) ya emitido en OSEI,
 * por el endpoint para ERP aliados: GET/POST /api/v1/documents/{prefix}/{number}/...
 */
class DocumentosOsei
{
    const TIMEOUT = 30;

    /**
     * @return array{ok: bool, content: ?string, message: string}
     */
    public function pdf($prefijo, $numero)
    {
        try {
            $response = $this->client()->get($this->url($prefijo, $numero, 'pdf'), [
                'headers' => $this->headers(),
                'http_errors' => false,
                'timeout' => self::TIMEOUT,
            ]);
        } catch (Exception $e) {
            \Log::warning('No se pudo consultar el PDF en OSEI: ' . $e->getMessage());
            return ['ok' => false, 'content' => null, 'message' => 'No se pudo conectar con OSEI.'];
        }

        if ($response->getStatusCode() !== 200) {
            return ['ok' => false, 'content' => null, 'message' => $this->errorMessage($response)];
        }

        return ['ok' => true, 'content' => (string) $response->getBody(), 'message' => ''];
    }

    /**
     * @return array JSON de OSEI decodificado, con la llave 'http_status' agregada.
     */
    public function reenviarCorreo($prefijo, $numero, array $emails = [])
    {
        $body = [];
        if (!empty($emails)) {
            $body['recipients'] = $emails;
        }

        try {
            $response = $this->client()->post($this->url($prefijo, $numero, 'resend-mail'), [
                'headers' => $this->headers(),
                'json' => $body,
                'http_errors' => false,
                'timeout' => self::TIMEOUT,
            ]);
        } catch (Exception $e) {
            \Log::warning('No se pudo reenviar el correo en OSEI: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'No se pudo conectar con OSEI.', 'http_status' => 0];
        }

        $decoded = json_decode((string) $response->getBody(), true);
        if (!is_array($decoded)) {
            $decoded = ['status' => 'error', 'message' => 'OSEI envió una respuesta inválida.'];
        }

        $decoded['http_status'] = $response->getStatusCode();

        return $decoded;
    }

    protected function client()
    {
        return new Client(['base_uri' => $this->baseUrl()]);
    }

    private function baseUrl()
    {
        return rtrim((string) config('facturacion_electronica.url_bot_osei', 'https://osei.com.co/'), '/');
    }

    private function url($prefijo, $numero, $accion)
    {
        return $this->baseUrl() . '/api/v1/documents/' . rawurlencode($prefijo) . '/' . rawurlencode($numero) . '/' . $accion;
    }

    private function headers()
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . config('facturacion_electronica.tokenEmpresa'),
        ];
    }

    private function errorMessage($response)
    {
        $decoded = json_decode((string) $response->getBody(), true);

        if (is_array($decoded) && !empty($decoded['message'])) {
            return $decoded['message'];
        }

        return 'OSEI respondió HTTP ' . $response->getStatusCode() . '.';
    }
}
