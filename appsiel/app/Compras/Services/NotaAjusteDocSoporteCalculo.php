<?php

namespace App\Compras\Services;

/**
 * Valores de la nota de ajuste al documento soporte, sin BD.
 *
 * Cada línea del DS llega como arreglo con: id, cantidad, base (base_impuesto), iva (valor_impuesto),
 * tasa_impuesto, valor_retencion y lo ya ajustado por notas activas: cantidad_ajustada, base_ajustada.
 * Todo en valores positivos; quien persiste decide los signos.
 */
class NotaAjusteDocSoporteCalculo
{
    const DEVOLUCION = 1;
    const ANULACION = 2;

    /**
     * @param int   $concepto  1 devolución, 2 anulación, 3-5 por valor
     * @param array $lineas    líneas del DS (ver encabezado de la clase)
     * @param array $solicitud [id línea DS => cantidad a devolver (concepto 1) o base sin IVA a rebajar (3-5)]
     * @return array líneas de la nota: origen_id, cantidad, base, iva, total, retencion
     */
    public static function calcular($concepto, array $lineas, array $solicitud)
    {
        $concepto = (int)$concepto;
        if ($concepto < 1 || $concepto > 5) {
            throw new \InvalidArgumentException('El concepto de la nota debe estar entre 1 y 5.');
        }

        $resultado = [];
        foreach ($lineas as $linea) {
            $cantidadDs = abs((float)$linea['cantidad']);
            $baseDs = abs((float)$linea['base']);
            $cantidadDisponible = round($cantidadDs - abs((float)$linea['cantidad_ajustada']), 4);
            $baseDisponible = round($baseDs - abs((float)$linea['base_ajustada']), 2);
            $pedido = isset($solicitud[$linea['id']]) ? (float)$solicitud[$linea['id']] : 0;

            if ($concepto == self::ANULACION) {
                if (abs((float)$linea['cantidad_ajustada']) > 0 || abs((float)$linea['base_ajustada']) > 0) {
                    throw new \InvalidArgumentException('El documento soporte ya tiene notas de ajuste activas: no se puede anular completo. Use una devolución o una rebaja por el saldo.');
                }
                $cantidad = $cantidadDs;
                $base = $baseDs;
                $iva = abs((float)$linea['iva']);
            } elseif ($concepto == self::DEVOLUCION) {
                if ($pedido < 0) {
                    throw new \InvalidArgumentException('La cantidad a devolver no puede ser negativa.');
                }
                if ($pedido == 0) {
                    continue;
                }
                if ($pedido > $cantidadDisponible + 0.00001) {
                    throw new \InvalidArgumentException('La cantidad a devolver (' . $pedido . ') supera la disponible (' . $cantidadDisponible . ') en una línea del documento soporte.');
                }
                $cantidad = $pedido;
                // Precio unitario vigente: lo que queda de base entre lo que queda de cantidad (respeta rebajas previas).
                $base = abs($pedido - $cantidadDisponible) < 0.00001
                    ? $baseDisponible
                    : round($pedido * $baseDisponible / $cantidadDisponible, 2);
                $iva = round($base * (float)$linea['tasa_impuesto'] / 100, 2);
            } else {
                if ($pedido < 0) {
                    throw new \InvalidArgumentException('El valor a rebajar no puede ser negativo.');
                }
                if ($pedido == 0) {
                    continue;
                }
                if (round($pedido, 2) > $baseDisponible) {
                    throw new \InvalidArgumentException('El valor a rebajar ($' . number_format($pedido, 2, ',', '.') . ') supera el disponible ($' . number_format($baseDisponible, 2, ',', '.') . ') en una línea del documento soporte.');
                }
                $cantidad = 0;
                $base = round($pedido, 2);
                $iva = round($base * (float)$linea['tasa_impuesto'] / 100, 2);
            }

            if ($base <= 0 && $cantidad <= 0) {
                continue;
            }

            $resultado[] = [
                'origen_id' => $linea['id'],
                'cantidad' => $cantidad,
                'base' => $base,
                'iva' => $iva,
                'total' => round($base + $iva, 2),
                'tasa_impuesto' => (float)$linea['tasa_impuesto'],
                'retencion' => self::proporcional((float)$linea['valor_retencion'], $base, $baseDs),
            ];
        }

        if (empty($resultado)) {
            throw new \InvalidArgumentException('La nota no tiene valores: indique cantidades o valores en al menos una línea.');
        }

        return $resultado;
    }

    /**
     * Parte de $valor que corresponde a $base sobre $baseTotal (reverso proporcional de retenciones).
     */
    public static function proporcional($valor, $base, $baseTotal)
    {
        if ($valor == 0 || $baseTotal <= 0) {
            return 0.0;
        }

        return round(abs($valor) * min(1, $base / $baseTotal), 2);
    }

    /**
     * Totales de la nota. ReteICA proporcional a la base de la nota sobre la base del DS.
     */
    public static function totales(array $lineasNota, $reteicaDs, $baseTotalDs)
    {
        $base = round(array_sum(array_column($lineasNota, 'base')), 2);
        $iva = round(array_sum(array_column($lineasNota, 'iva')), 2);
        $total = round($base + $iva, 2);
        $retefuente = round(array_sum(array_column($lineasNota, 'retencion')), 2);
        $reteica = self::proporcional((float)$reteicaDs, $base, (float)$baseTotalDs);

        return [
            'base' => $base,
            'iva' => $iva,
            'total' => $total,
            'retefuente' => $retefuente,
            'reteica' => $reteica,
            'neto' => round($total - $retefuente - $reteica, 2),
        ];
    }

    /**
     * Regla de negocio 1: en un DS a crédito el neto de la nota no puede superar el saldo de la CxP.
     */
    public static function validar_saldo($formaPago, $neto, $saldoPendiente)
    {
        if ($formaPago != 'credito') {
            return;
        }
        $saldo = round(max(0, (float)$saldoPendiente), 2);
        if ($saldo <= 0) {
            throw new \InvalidArgumentException('El documento soporte no tiene saldo pendiente por pagar: no se puede hacer la nota de ajuste.');
        }
        if ((int)round($neto * 100) > (int)round($saldo * 100)) {
            throw new \InvalidArgumentException('El valor neto de la nota ($' . number_format($neto, 2, ',', '.') . ') supera el saldo pendiente por pagar del documento soporte ($' . number_format($saldo, 2, ',', '.') . ').');
        }
    }
}
