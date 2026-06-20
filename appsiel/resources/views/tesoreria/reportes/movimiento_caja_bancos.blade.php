<style>
    .teso-movimiento-caja-bancos-report {
        color: #111111;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 10px;
        line-height: 1.22;
    }

    .teso-movimiento-caja-bancos-report .report-actions {
        margin-bottom: 8px;
    }

    @media print {
        .teso-movimiento-caja-bancos-report .report-actions {
            display: none;
        }
    }

    .teso-movimiento-caja-bancos-report .report-header {
        border-bottom: 2px solid #111111;
        margin-bottom: 9px;
        padding-bottom: 7px;
        text-align: center;
    }

    .teso-movimiento-caja-bancos-report .report-title {
        color: #111111;
        font-size: 16px;
        font-weight: bold;
        margin: 0 0 3px;
        text-transform: uppercase;
    }

    .teso-movimiento-caja-bancos-report .company-name {
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .teso-movimiento-caja-bancos-report .company-data {
        color: #333333;
        font-size: 10px;
    }

    .teso-movimiento-caja-bancos-report .meta-box {
        background-color: #ffffff;
        border: 1px solid #777777;
        margin-bottom: 9px;
        padding: 6px 8px;
    }

    .teso-movimiento-caja-bancos-report .meta-box table {
        border-collapse: collapse;
        width: 100%;
    }

    .teso-movimiento-caja-bancos-report .meta-box td {
        border: none;
        color: #111111;
        padding: 1px 4px;
        vertical-align: top;
    }

    .teso-movimiento-caja-bancos-report .meta-label {
        color: #222222;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        width: 70px;
    }

    .teso-movimiento-caja-bancos-report .warning-box {
        background-color: #ffffff;
        border: 1px solid #777777;
        color: #111111;
        margin-bottom: 8px;
        padding: 5px 7px;
    }

    .teso-movimiento-caja-bancos-report .summary-table {
        border-collapse: collapse;
        margin-bottom: 9px;
        width: 100%;
    }

    .teso-movimiento-caja-bancos-report .summary-table td {
        border: 1px solid #777777;
        padding: 5px 6px;
    }

    .teso-movimiento-caja-bancos-report .summary-label {
        background-color: #ffffff;
        color: #111111;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .teso-movimiento-caja-bancos-report .summary-value {
        color: #000000;
        font-size: 12px;
        font-weight: bold;
        text-align: right;
    }

    .teso-movimiento-caja-bancos-report .report-table {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }

    .teso-movimiento-caja-bancos-report .report-table th {
        background-color: #ffffff;
        border-bottom: 1px solid #333333;
        border-top: 1px solid #333333;
        color: #000000;
        font-size: 9px;
        font-weight: bold;
        padding: 4px 3px;
        text-align: left;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .teso-movimiento-caja-bancos-report .report-table td {
        border-bottom: 1px solid #b5b5b5;
        color: #111111;
        font-size: 9px;
        padding: 3px;
        vertical-align: top;
        word-wrap: break-word;
    }

    .teso-movimiento-caja-bancos-report .report-table tbody tr:nth-child(even) td {
        background-color: #ffffff;
    }

    .teso-movimiento-caja-bancos-report .report-table tfoot td {
        background-color: #ffffff;
        border-bottom: 1px solid #333333;
        border-top: 2px solid #111111;
        color: #000000;
        font-size: 9px;
        font-weight: bold;
        padding: 5px 3px;
    }

    .teso-movimiento-caja-bancos-report .text-right {
        text-align: right;
    }

    .teso-movimiento-caja-bancos-report .text-center {
        text-align: center;
    }

    .teso-movimiento-caja-bancos-report .muted {
        color: #333333;
    }

    .teso-movimiento-caja-bancos-report .date-col {
        width: 7%;
    }

    .teso-movimiento-caja-bancos-report .doc-col {
        width: 10%;
    }

    .teso-movimiento-caja-bancos-report .third-col {
        width: 13%;
    }

    .teso-movimiento-caja-bancos-report .account-col {
        width: 14%;
    }

    .teso-movimiento-caja-bancos-report .detail-col {
        width: 13%;
    }

    .teso-movimiento-caja-bancos-report .reason-col {
        width: 10%;
    }

    .teso-movimiento-caja-bancos-report .user-col {
        width: 8%;
    }

    .teso-movimiento-caja-bancos-report .money-col {
        width: 6%;
    }

    .teso-movimiento-caja-bancos-report .initial-row td {
        background-color: #ffffff;
        font-weight: bold;
    }

    .teso-movimiento-caja-bancos-report .empty-row td {
        color: #333333;
        padding: 13px 4px;
        text-align: center;
    }
</style>

@php
    $fecha_hasta = isset($fecha_hasta) ? $fecha_hasta : $fecha_desde;
    $caja = isset($caja) ? $caja : null;
    $cuenta_bancaria = isset($cuenta_bancaria) ? $cuenta_bancaria : null;
    $pdv = isset($pdv) ? $pdv : null;
    $motivo = isset($motivo) ? $motivo : null;
    $tercero = isset($tercero) ? $tercero : null;
    $medio_recaudo = isset($medio_recaudo) ? $medio_recaudo : null;
    $usuario_filtro = isset($usuario_filtro) ? $usuario_filtro : null;
    $empresa = isset($empresa) ? $empresa : null;
    $usuario_tiene_restriccion_movimientos = isset($usuario_tiene_restriccion_movimientos) ? $usuario_tiene_restriccion_movimientos : false;
@endphp

<div class="teso-movimiento-caja-bancos-report">

<div class="report-actions">
    {{ Form::bsBtnExcel('movimiento_tesoreria') }}
</div>

@php
    $saldo = $saldo_inicial;
    $total_entradas = 0;
    $total_salidas = 0;
    $cantidad_movimientos = count($movimiento);
    $fecha_saldo_inicial = date('Y-m-d', strtotime($fecha_desde . ' -1 day'));
@endphp

<div class="report-header">
    <h1 class="report-title">Movimiento de Cajas / Bancos</h1>
    @if(!is_null($empresa))
        <div class="company-name">{{ $empresa->descripcion }}</div>
        <div class="company-data">
            {{ config("configuracion.tipo_identificador") }}:
            @if( config("configuracion.tipo_identificador") == 'NIT')
                {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}
            @else
                {{ $empresa->numero_identificacion }}
            @endif
            - {{ $empresa->digito_verificacion }}
            @if($empresa->direccion1)
                | {{ $empresa->direccion1 }}
            @endif
            @if($empresa->telefono1)
                | Teléfono(s): {{ $empresa->telefono1 }}
            @endif
        </div>
    @endif
</div>

<div class="meta-box">
    <table>
        <tr>
            <td class="meta-label">Periodo</td>
            <td>{{ $fecha_desde }} hasta {{ $fecha_hasta }}</td>
            <td class="meta-label">Registros</td>
            <td class="text-right">{{ number_format($cantidad_movimientos, 0, ',', '.') }}</td>
            <td class="meta-label">PDV</td>
            <td>{{ !is_null($pdv) ? $pdv->descripcion : 'Todos' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Caja</td>
            <td>{{ !is_null($caja) ? $caja->descripcion : 'Todas' }}</td>
            <td class="meta-label">Cuenta</td>
            <td colspan="3">
                @if(!is_null($cuenta_bancaria))
                    {{ $cuenta_bancaria->tipo_cuenta }} {{ $cuenta_bancaria->entidad_financiera->descripcion }} No. {{ $cuenta_bancaria->descripcion }}
                @else
                    Todas
                @endif
            </td>
        </tr>
        <tr>
            <td class="meta-label">Motivo</td>
            <td>{{ !is_null($motivo) ? $motivo->descripcion : 'Todos' }}</td>
            <td class="meta-label">Tercero</td>
            <td>{{ !is_null($tercero) ? $tercero->descripcion : 'Todos' }}</td>
            <td class="meta-label">Usuario</td>
            <td>{{ !is_null($usuario_filtro) ? $usuario_filtro->email : 'Todos' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Medio</td>
            <td colspan="5">{{ !is_null($medio_recaudo) ? $medio_recaudo->descripcion : 'Todos' }}</td>
        </tr>
    </table>
</div>

@if( isset($usuario_tiene_restriccion_movimientos) && $usuario_tiene_restriccion_movimientos )
    <div class="warning-box">
        Mostrando solo movimientos creados por el usuario logueado.
    </div>
@endif

<table class="summary-table">
    <tr>
        <td class="summary-label">Saldo inicial</td>
        <td class="summary-value">${{ number_format($saldo_inicial, 0, ',','.') }}</td>
        <td class="summary-label">Entradas</td>
        <td class="summary-value">
            @php
                foreach ($movimiento as $fila_resumen) {
                    if ($fila_resumen->valor_movimiento >= 0) {
                        $total_entradas += $fila_resumen->valor_movimiento;
                    } else {
                        $total_salidas += abs($fila_resumen->valor_movimiento);
                    }
                }
                $saldo_final_resumen = $saldo_inicial + $total_entradas - $total_salidas;
            @endphp
            ${{ number_format($total_entradas, 0, ',','.') }}
        </td>
        <td class="summary-label">Salidas</td>
        <td class="summary-value">${{ number_format($total_salidas, 0, ',','.') }}</td>
        <td class="summary-label">Saldo final</td>
        <td class="summary-value">${{ number_format($saldo_final_resumen, 0, ',','.') }}</td>
    </tr>
</table>

<table id="movimiento_tesoreria" class="report-table">
    <thead>
        <tr>
            <th class="date-col text-center">Fecha</th>
            <th class="doc-col">Documento</th>
            <th class="third-col">Tercero</th>
            <th class="account-col">Caja/Banco</th>
            <th class="detail-col">Detalle</th>
            <th class="reason-col">Motivo</th>
            <th class="user-col">Creado por</th>
            <th class="money-col text-right">Entradas</th>
            <th class="money-col text-right">Salidas</th>
            <th class="money-col text-right">Saldo</th>
        </tr>
    </thead>
    <tbody>
        <tr class="initial-row">
            <td class="text-center">{{ $fecha_saldo_inicial }}</td>
            <td colspan="6">Saldo inicial</td>
            <td></td>
            <td></td>
            <td class="text-right">${{ number_format($saldo_inicial, 0, ',','.') }}</td>
        </tr>

        @foreach($movimiento as $fila)
            @php
                $entrada = '';
                $salida = '';

                if ($fila->valor_movimiento >= 0) {
                    $entrada = '$' . number_format($fila->valor_movimiento, 0, ',','.');
                } else {
                    $salida = '$' . number_format(abs($fila->valor_movimiento), 0, ',','.');
                }

                $saldo += $fila->valor_movimiento;

                $caja_label = '';
                if (!is_null($fila->caja)) {
                    $caja_label = $fila->caja->descripcion;
                }

                $cuenta_bancaria_label = '';
                if (!is_null($fila->cuenta_bancaria)) {
                    $cuenta_bancaria_label = 'Cuenta ' . $fila->cuenta_bancaria->tipo_cuenta . ' ' . $fila->cuenta_bancaria->entidad_financiera->descripcion . ' No. ' . $fila->cuenta_bancaria->descripcion;
                }

                $detalle_operacion = $fila->descripcion;
                $registro_linea = $fila->get_registro_linea_movimiento($fila->teso_motivo_id, $fila->valor_movimiento);

                if ($registro_linea != null && $fila->descripcion != $registro_linea->detalle_operacion) {
                    $detalle_operacion = trim($fila->descripcion . ' ' . $registro_linea->detalle_operacion);
                }

                if($fila->core_tipo_transaccion_id == 43 ) { // Traslado de efectivo

                    if($detalle_operacion == 0 || is_null($detalle_operacion) ) {
                        $detalle_operacion = '';
                    }

                    if( $fila->pdv_id > 0 && !is_null($fila->pdv) ) {
                        $detalle_operacion .= ' - ' . $fila->pdv->descripcion;
                    }
                }

                $referencia_tercero = $fila->get_datos_referencia_tercero();
            @endphp

            <tr>
                <td class="text-center">{{ $fila->fecha }}</td>
                <td>{!! $fila->enlace_show_documento() !!}</td>
                <td>
                    {{ $fila->tercero_descripcion }}
                    @if( !is_null($referencia_tercero) )
                        <br>
                        <span class="muted"><b>{{ $referencia_tercero->etiqueta }}:</b> {{ $referencia_tercero->valor }}</span>
                    @endif
                </td>
                <td>{{ trim($caja_label . ' ' . $cuenta_bancaria_label) }}</td>
                <td>{{ $detalle_operacion }}</td>
                <td>{{ $fila->motivo_descripcion }}</td>
                <td>{{ substr($fila->creado_por, 0, 18) }}</td>
                <td class="text-right">{{ $entrada }}</td>
                <td class="text-right">{{ $salida }}</td>
                <td class="text-right">${{ number_format( $saldo, 0, ',','.') }}</td>
            </tr>
        @endforeach

        @if($cantidad_movimientos == 0)
            <tr class="empty-row">
                <td colspan="10">No hay movimientos para los filtros seleccionados.</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" class="text-right">Totales</td>
            <td class="text-right">${{ number_format( $total_entradas, 0, ',','.') }}</td>
            <td class="text-right">${{ number_format( $total_salidas, 0, ',','.') }}</td>
            <td class="text-right">${{ number_format( $saldo_final_resumen, 0, ',','.') }}</td>
        </tr>
    </tfoot>
</table>

</div>
