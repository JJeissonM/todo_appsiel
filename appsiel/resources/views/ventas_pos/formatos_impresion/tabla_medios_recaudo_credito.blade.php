<?php
    $condicion_pago_impresion = isset($datos_factura->lbl_condicion_pago) ? strtolower((string)$datos_factura->lbl_condicion_pago) : '';
    $condicion_pago_credito = in_array($condicion_pago_impresion, array('credito', 'crédito'));
    $lineas_medios_recaudo = array();
    $total_medios_recaudo = 0;

    $extraer_descripcion_medio = function($valor) {
        $valor = trim((string)$valor);
        if ($valor == '' || $valor == '0-') {
            return '';
        }

        $partes = explode('-', $valor, 2);
        if (isset($partes[1])) {
            return trim($partes[1]);
        }

        return $valor;
    };

    $extraer_valor_medio = function($valor) {
        $valor = str_replace(array('$', ' '), '', (string)$valor);
        $valor = str_replace(',', '', $valor);

        return abs((float)$valor);
    };

    if ($condicion_pago_credito && isset($doc_encabezado->lineas_registros_medios_recaudos)) {
        $json_medios_recaudo = str_replace('$', '', (string)$doc_encabezado->lineas_registros_medios_recaudos);
        $lineas_decodificadas = json_decode($json_medios_recaudo);

        if (is_array($lineas_decodificadas)) {
            foreach ($lineas_decodificadas as $linea_recaudo) {
                if (!is_object($linea_recaudo)) {
                    continue;
                }

                $valor_medio = isset($linea_recaudo->valor) ? $extraer_valor_medio($linea_recaudo->valor) : 0;
                if ($valor_medio <= 0) {
                    continue;
                }

                $medio = isset($linea_recaudo->teso_medio_recaudo_id) ? $extraer_descripcion_medio($linea_recaudo->teso_medio_recaudo_id) : '';
                $motivo = isset($linea_recaudo->teso_motivo_id) ? $extraer_descripcion_medio($linea_recaudo->teso_motivo_id) : '';
                $caja_banco = '';

                if (isset($linea_recaudo->teso_cuenta_bancaria_id) && trim((string)$linea_recaudo->teso_cuenta_bancaria_id) != '0-') {
                    $caja_banco = $extraer_descripcion_medio($linea_recaudo->teso_cuenta_bancaria_id);
                } elseif (isset($linea_recaudo->teso_caja_id) && trim((string)$linea_recaudo->teso_caja_id) != '0-') {
                    $caja_banco = $extraer_descripcion_medio($linea_recaudo->teso_caja_id);
                }

                $lineas_medios_recaudo[] = (object)array(
                    'medio' => $medio,
                    'motivo' => $motivo,
                    'caja_banco' => $caja_banco,
                    'valor' => $valor_medio
                );

                $total_medios_recaudo += $valor_medio;
            }
        }
    }
?>

@if($condicion_pago_credito && count($lineas_medios_recaudo) > 0)
    <br>
    <div style="text-align: center; width: 100%; font-weight: bold; font-size: 12px; background: #ddd;">
        Medios de recaudo
    </div>
    <table class="table-bordered" style="width: 100%; font-size: {{ $tamanino_fuente_2 }};">
        <thead>
            <tr style="font-weight: bold; text-align: center;">
                <td>Medio</td>
                <td>Motivo</td>
                <td>Caja/Banco</td>
                <td>Valor</td>
            </tr>
        </thead>
        <tbody>
            @foreach($lineas_medios_recaudo as $linea_medio_recaudo)
                <tr>
                    <td>{{ $linea_medio_recaudo->medio }}</td>
                    <td>{{ $linea_medio_recaudo->motivo }}</td>
                    <td>{{ $linea_medio_recaudo->caja_banco }}</td>
                    <td style="text-align: right;">${{ number_format($linea_medio_recaudo->valor, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="3" style="text-align: right;">Total</td>
                <td style="text-align: right;">${{ number_format($total_medios_recaudo, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endif
