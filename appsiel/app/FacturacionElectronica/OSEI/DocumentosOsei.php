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

    /**
     * Esquema y host de la URL de emisión (WSDL = https://osei.com.co/api/v1/invoices).
     * No usar url_bot_osei: es la URL del bot y puede traer el token en la ruta.
     */
    private function baseUrl()
    {
        $partes = parse_url((string) config('facturacion_electronica.WSDL'));

        if (empty($partes['scheme']) || empty($partes['host'])) {
            return 'https://osei.com.co';
        }

        return $partes['scheme'] . '://' . $partes['host'] . (isset($partes['port']) ? ':' . $partes['port'] : '');
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
