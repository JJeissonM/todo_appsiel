<?php

namespace App\FacturacionElectronica\OSEI;

use Exception;
use GuzzleHttp\Client;

/**
 * Consulta en OSEI lo que la DIAN tiene de un adquiriente, para sugerirlo al crear un cliente.
 *
 * La DIAN responde con lo que otros facturadores informaron en sus facturas, no con el RUT:
 * el resultado es una sugerencia que el usuario confirma y nunca impide guardar.
 */
class ConsultaAdquiriente
{
    const DESHABILITADA = 'deshabilitada';
    const NO_DISPONIBLE = 'no_disponible';

    // OSEI espera hasta 5 s a la DIAN y firma la consulta antes de enviarla.
    const TIMEOUT = 8;
    const CONNECT_TIMEOUT = 3;

    /**
     * @return array la respuesta de OSEI, o ['status' => DESHABILITADA | NO_DISPONIBLE]
     */
    public function consultar($tipo, $numero)
    {
        $url = $this->url();

        if (is_null($url)) {
            return ['status' => self::DESHABILITADA];
        }

        try {
            $response = (new Client())->get($url . '/' . rawurlencode($tipo) . '/' . rawurlencode($numero), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . config('facturacion_electronica.tokenEmpresa'),
                ],
                'timeout' => self::TIMEOUT,
                'connect_timeout' => self::CONNECT_TIMEOUT,
                'http_errors' => false,
            ]);
        } catch (Exception $e) {
            \Log::warning('No se pudo consultar el adquiriente en OSEI: ' . $e->getMessage());
            return ['status' => self::NO_DISPONIBLE];
        }

        $respuesta = json_decode((string) $response->getBody(), true);

        if (!is_array($respuesta) || !isset($respuesta['status']) || $respuesta['status'] === 'error') {
            \Log::warning('OSEI rechazó la consulta del adquiriente. HTTP ' . $response->getStatusCode());
            return ['status' => self::NO_DISPONIBLE];
        }

        return $respuesta;
    }

    /**
     * Las instalaciones existentes no tienen url_adquirientes en su configuración:
     * se deriva de la URL de facturas.
     */
    protected function url()
    {
        if (config('facturacion_electronica.proveedor_tecnologico_default') != 'OSEI') {
            return null;
        }

        $url = config('facturacion_electronica.url_adquirientes');

        if (!empty($url)) {
            return rtrim($url, '/');
        }

        $url_facturas = rtrim((string) config('facturacion_electronica.WSDL'), '/');

        if (!preg_match('#/invoices$#', $url_facturas)) {
            return null;
        }

        return preg_replace('#/invoices$#', '/acquirers', $url_facturas);
    }
}
