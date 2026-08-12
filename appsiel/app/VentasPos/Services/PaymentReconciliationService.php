<?php

namespace App\VentasPos\Services;

class PaymentReconciliationService
{
    /**
     * Ajusta las lineas marcadas con un motivo de recargo para que su suma sea
     * exactamente el valor calculado. El excedente conserva medio de recaudo y
     * destino, pero vuelve al motivo normal de venta.
     */
    public function normalizar_lineas_recargo($lineas_json, $valor_recargo, $motivo_recargo_id, $motivo_recargo_label, $motivo_default_id, $motivo_default_label, $tolerancia = 0.01)
    {
        $resultado = [
            'lineas_json' => $lineas_json,
            'normalizado' => false,
            'valor_recargo' => 0.0,
            'total_recaudos' => 0.0
        ];

        $lineas = json_decode((string)$lineas_json, true);
        $motivo_recargo_id = (int)$motivo_recargo_id;
        $motivo_default_id = (int)$motivo_default_id;
        $valor_recargo = round((float)$valor_recargo, 2);
        if (!is_array($lineas) || empty($lineas) || $motivo_recargo_id <= 0 || $motivo_default_id <= 0 || $valor_recargo < 0) {
            return $resultado;
        }

        $total_recaudos = $this->sumar_recaudos($lineas);
        $total_recargo_actual = 0.0;
        foreach ($lineas as $linea) {
            if ($this->get_motivo_id($linea) === $motivo_recargo_id) {
                $total_recargo_actual += $this->parsear_valor(isset($linea['valor']) ? $linea['valor'] : 0);
            }
        }
        $total_recargo_actual = round($total_recargo_actual, 2);

        // No se agrega un recargo que no venia seleccionado en el recaudo.
        if ($total_recargo_actual <= (float)$tolerancia) {
            return $resultado;
        }

        if (abs($total_recargo_actual - $valor_recargo) <= (float)$tolerancia) {
            $resultado['valor_recargo'] = $total_recargo_actual;
            $resultado['total_recaudos'] = $total_recaudos;
            return $resultado;
        }

        $normalizadas = [];
        $pendiente_recargo = $valor_recargo;

        // Primero reduce lineas de recargo sobredimensionadas y devuelve el
        // excedente a ventas sin cambiar caja, banco ni medio de recaudo.
        foreach ($lineas as $linea) {
            if ($this->get_motivo_id($linea) !== $motivo_recargo_id) {
                $normalizadas[] = $linea;
                continue;
            }

            $valor_linea = $this->parsear_valor(isset($linea['valor']) ? $linea['valor'] : 0);
            $valor_aplicado = min($valor_linea, max(0, $pendiente_recargo));
            $valor_excedente = round($valor_linea - $valor_aplicado, 2);
            $pendiente_recargo = round($pendiente_recargo - $valor_aplicado, 2);

            if ($valor_excedente > (float)$tolerancia) {
                $linea_venta = $linea;
                $linea_venta['teso_motivo_id'] = $motivo_default_id . '-' . $motivo_default_label;
                $linea_venta['valor'] = $this->formatear_valor($valor_excedente);
                $normalizadas[] = $linea_venta;
            }

            if ($valor_aplicado > (float)$tolerancia) {
                $linea['teso_motivo_id'] = $motivo_recargo_id . '-' . $motivo_recargo_label;
                $linea['valor'] = $this->formatear_valor($valor_aplicado);
                $normalizadas[] = $linea;
            }
        }

        // Si las lineas marcadas eran menores al recargo, toma la diferencia
        // de lineas normales conservando el mismo destino del recaudo.
        if ($pendiente_recargo > (float)$tolerancia) {
            for ($index = count($normalizadas) - 1; $index >= 0 && $pendiente_recargo > (float)$tolerancia; $index--) {
                if ($this->get_motivo_id($normalizadas[$index]) === $motivo_recargo_id) {
                    continue;
                }

                $valor_linea = $this->parsear_valor(isset($normalizadas[$index]['valor']) ? $normalizadas[$index]['valor'] : 0);
                $valor_aplicado = min($valor_linea, $pendiente_recargo);
                if ($valor_aplicado <= (float)$tolerancia) {
                    continue;
                }

                $linea_recargo = $normalizadas[$index];
                $linea_recargo['teso_motivo_id'] = $motivo_recargo_id . '-' . $motivo_recargo_label;
                $linea_recargo['valor'] = $this->formatear_valor($valor_aplicado);
                $nuevo_valor = round($valor_linea - $valor_aplicado, 2);
                $pendiente_recargo = round($pendiente_recargo - $valor_aplicado, 2);

                if ($nuevo_valor <= (float)$tolerancia) {
                    array_splice($normalizadas, $index, 1, [$linea_recargo]);
                } else {
                    $normalizadas[$index]['valor'] = $this->formatear_valor($nuevo_valor);
                    array_splice($normalizadas, $index + 1, 0, [$linea_recargo]);
                }
            }
        }

        // Nunca deja una correccion parcial.
        if ($pendiente_recargo > (float)$tolerancia) {
            return $resultado;
        }

        $resultado['lineas_json'] = json_encode(array_values($normalizadas));
        $resultado['normalizado'] = true;
        $resultado['valor_recargo'] = $valor_recargo;
        $resultado['total_recaudos'] = $total_recaudos;

        return $resultado;
    }

    /**
     * Reconstruye una linea unica que fue guardada completa con el motivo de
     * un recargo porcentual. Ese formato es ambiguo y hace que todo el recaudo
     * se contabilice como comision; el formato valido tiene venta + recargo.
     */
    public function reconstruir_recargo_porcentual_linea_unica(
        $lineas_json,
        $valor_productos,
        $valor_bolsas,
        $porcentaje_recargo,
        $motivo_recargo_id,
        $motivo_recargo_label,
        $motivo_default_id,
        $motivo_default_label,
        $redondear_centena = true
    ) {
        $resultado = [
            'lineas_json' => $lineas_json,
            'normalizado' => false,
            'ajuste' => 0.0,
            'valor_recargo' => 0.0,
            'total_recaudos' => 0.0
        ];

        $lineas = json_decode((string)$lineas_json, true);
        if (!is_array($lineas) || count($lineas) !== 1 || (int)$motivo_recargo_id <= 0 ||
            (int)$motivo_default_id <= 0 || (float)$porcentaje_recargo <= 0) {
            return $resultado;
        }

        $linea = $lineas[0];
        $motivo_linea = isset($linea['teso_motivo_id'])
                        ? (int)explode('-', (string)$linea['teso_motivo_id'])[0]
                        : 0;
        if ($motivo_linea !== (int)$motivo_recargo_id) {
            return $resultado;
        }

        $valor_productos = (float)$valor_productos;
        $valor_bolsas = (float)$valor_bolsas;
        $valor_recargo = round($valor_productos * (float)$porcentaje_recargo / 100, 0);
        if ($valor_recargo <= 0) {
            return $resultado;
        }

        $total_sin_redondear = round($valor_productos + $valor_bolsas + $valor_recargo, 2);
        $total_redondeado = $redondear_centena
                            ? round($total_sin_redondear / 100, 0) * 100
                            : round($total_sin_redondear, 0);
        $valor_venta = round($total_redondeado - $valor_recargo, 2);
        if ($valor_venta <= 0) {
            return $resultado;
        }

        $linea_venta = $linea;
        $linea_venta['teso_motivo_id'] = (int)$motivo_default_id . '-' . $motivo_default_label;
        $linea_venta['valor'] = $this->formatear_valor($valor_venta);

        $linea_recargo = $linea;
        $linea_recargo['teso_motivo_id'] = (int)$motivo_recargo_id . '-' . $motivo_recargo_label;
        $linea_recargo['valor'] = $this->formatear_valor($valor_recargo);

        $resultado['lineas_json'] = json_encode([$linea_venta, $linea_recargo]);
        $resultado['normalizado'] = true;
        $resultado['ajuste'] = round($total_redondeado - $total_sin_redondear, 2);
        $resultado['valor_recargo'] = $valor_recargo;
        $resultado['total_recaudos'] = round($total_redondeado, 2);

        return $resultado;
    }

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

    /**
     * Corrige el ajuste calculado antes de agregar la comision de datafono.
     * Solo actua cuando los recaudos coinciden con el total final redondeado.
     */
    public function normalizar_ajuste_datafono($lineas_json, $valor_productos, $valor_bolsas, $ajuste_actual, $motivo_datafono_id, $redondear_centena = true, $tolerancia = 0.01)
    {
        $resultado = $this->normalizar_ajuste_recargos(
            $lineas_json,
            $valor_productos,
            $valor_bolsas,
            $ajuste_actual,
            [$motivo_datafono_id],
            $redondear_centena,
            $tolerancia
        );
        $resultado['valor_datafono'] = isset($resultado['valores_por_motivo'][(int)$motivo_datafono_id])
                                        ? $resultado['valores_por_motivo'][(int)$motivo_datafono_id]
                                        : 0.0;

        return $resultado;
    }

    public function normalizar_ajuste_recargos($lineas_json, $valor_productos, $valor_bolsas, $ajuste_actual, array $motivos_recargos, $redondear_centena = true, $tolerancia = 0.01)
    {
        $motivos = [];
        foreach ($motivos_recargos as $motivo_id) {
            if ((int)$motivo_id > 0) {
                $motivos[(int)$motivo_id] = (int)$motivo_id;
            }
        }

        $resultado = [
            'normalizado' => false,
            'ajuste' => round((float)$ajuste_actual, 2),
            'valor_recargos' => 0.0,
            'valores_por_motivo' => [],
            'total_recaudos' => 0.0
        ];

        $lineas = json_decode((string)$lineas_json, true);
        if (empty($motivos) || !is_array($lineas) || empty($lineas)) {
            return $resultado;
        }

        foreach ($lineas as $linea) {
            if (!is_array($linea)) {
                continue;
            }

            $motivo_id = isset($linea['teso_motivo_id']) ? (int)explode('-', (string)$linea['teso_motivo_id'])[0] : 0;
            if (!isset($motivos[$motivo_id])) {
                continue;
            }

            if (!isset($resultado['valores_por_motivo'][$motivo_id])) {
                $resultado['valores_por_motivo'][$motivo_id] = 0.0;
            }
            $resultado['valores_por_motivo'][$motivo_id] += $this->parsear_valor(isset($linea['valor']) ? $linea['valor'] : 0);
        }

        foreach ($resultado['valores_por_motivo'] as $motivo_id => $valor) {
            $resultado['valores_por_motivo'][$motivo_id] = round($valor, 2);
            $resultado['valor_recargos'] += $valor;
        }
        $resultado['valor_recargos'] = round($resultado['valor_recargos'], 2);
        $resultado['total_recaudos'] = $this->sumar_recaudos($lineas);

        if ($resultado['valor_recargos'] <= 0) {
            return $resultado;
        }

        $total_sin_redondear = round((float)$valor_productos + (float)$valor_bolsas + $resultado['valor_recargos'], 2);
        $total_redondeado = $redondear_centena
                            ? round($total_sin_redondear / 100, 0) * 100
                            : round($total_sin_redondear, 0);
        $ajuste_correcto = round($total_redondeado - $total_sin_redondear, 2);

        if (abs($resultado['total_recaudos'] - $total_redondeado) > (float)$tolerancia) {
            return $resultado;
        }

        if (abs($ajuste_correcto - (float)$ajuste_actual) <= (float)$tolerancia) {
            return $resultado;
        }

        $resultado['normalizado'] = true;
        $resultado['ajuste'] = $ajuste_correcto;

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

    protected function get_motivo_id($linea)
    {
        if (!is_array($linea) || !isset($linea['teso_motivo_id'])) {
            return 0;
        }

        return (int)explode('-', (string)$linea['teso_motivo_id'])[0];
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
