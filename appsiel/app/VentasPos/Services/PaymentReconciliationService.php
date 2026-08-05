<?php

namespace App\VentasPos\Services;

class PaymentReconciliationService
{
    /**
     * Descuenta el cambio de las lineas de efectivo cuando el excedente de los
     * recaudos coincide exactamente con el cambio registrado en la factura.
     *
     * Si la informacion no cumple esa condicion no se modifica. Esto evita
     * reinterpretar recaudos historicos o excedentes recibidos por banco.
     */
    public function normalizar_cambio_en_efectivo($lineas_json, $total_documento, $valor_cambio, $tolerancia = 0.01)
    {
        $resultado = [
            'lineas_json' => $lineas_json,
            'normalizado' => false,
            'total_recaudos' => 0.0
        ];

        $lineas = json_decode((string)$lineas_json, true);
        if (!is_array($lineas) || empty($lineas)) {
            return $resultado;
        }

        $total_recaudos = $this->sumar_recaudos($lineas);
        $resultado['total_recaudos'] = $total_recaudos;

        $total_documento = round((float)$total_documento, 2);
        $valor_cambio = round((float)$valor_cambio, 2);
        $excedente = round($total_recaudos - $total_documento, 2);

        if ($valor_cambio <= 0 || $excedente <= 0 || abs($excedente - $valor_cambio) > (float)$tolerancia) {
            return $resultado;
        }

        $pendiente = $excedente;
        for ($index = count($lineas) - 1; $index >= 0 && $pendiente > (float)$tolerancia; $index--) {
            if (!$this->es_linea_efectivo($lineas[$index])) {
                continue;
            }

            $valor_linea = $this->parsear_valor(isset($lineas[$index]['valor']) ? $lineas[$index]['valor'] : 0);
            if ($valor_linea <= 0) {
                continue;
            }

            $descuento = min($valor_linea, $pendiente);
            $nuevo_valor = round($valor_linea - $descuento, 2);
            $pendiente = round($pendiente - $descuento, 2);

            if ($nuevo_valor <= (float)$tolerancia) {
                unset($lineas[$index]);
            } else {
                $lineas[$index]['valor'] = $this->formatear_valor($nuevo_valor);
            }
        }

        // No se aplica una correccion parcial si no habia suficiente efectivo.
        if ($pendiente > (float)$tolerancia) {
            return $resultado;
        }

        $lineas = array_values($lineas);
        $resultado['lineas_json'] = json_encode($lineas);
        $resultado['normalizado'] = true;
        $resultado['total_recaudos'] = $this->sumar_recaudos($lineas);

        return $resultado;
    }

    public function sumar_recaudos(array $lineas)
    {
        $total = 0.0;
        foreach ($lineas as $linea) {
            if (is_array($linea)) {
                $total += $this->parsear_valor(isset($linea['valor']) ? $linea['valor'] : 0);
            }
        }

        return round($total, 2);
    }

    public function parsear_valor($valor)
    {
        $valor = trim(str_replace(['$', ' '], '', (string)$valor));
        if ($valor === '') {
            return 0.0;
        }

        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $valor)) {
            $valor = str_replace('.', '', $valor);
        }

        return (float)$valor;
    }

    protected function es_linea_efectivo(array $linea)
    {
        $caja_id = isset($linea['teso_caja_id']) ? (int)explode('-', (string)$linea['teso_caja_id'])[0] : 0;
        $cuenta_bancaria_id = isset($linea['teso_cuenta_bancaria_id']) ? (int)explode('-', (string)$linea['teso_cuenta_bancaria_id'])[0] : 0;

        return $caja_id > 0 && $cuenta_bancaria_id === 0;
    }

    protected function formatear_valor($valor)
    {
        $formateado = number_format((float)$valor, 2, '.', '');
        $formateado = rtrim(rtrim($formateado, '0'), '.');

        return '$' . ($formateado === '' ? '0' : $formateado);
    }
}
