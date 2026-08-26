<?php
    $cantidadDisponibles = collect($lineas)->where('seleccionable', true)->count();
    $totalDisponible = collect($lineas)->where('seleccionable', true)->sum('saldo_pendiente');
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <b>{{ $documento->get_label_documento() }} — {{ $documento->descripcion }}</b>
    </div>
    <div class="panel-body">
        @if (empty($lineas))
            <div class="alert alert-warning">El documento no tiene empleados asociados.</div>
        @else
            {{ Form::open(['url' => 'nomina/procesos/pagos/generar', 'id' => 'form_generar_pago_nomina']) }}
                {{ Form::hidden('nom_doc_encabezado_id', '', ['id' => 'pago_nomina_documento_id']) }}
                {{ Form::hidden('fecha_pago', '', ['id' => 'pago_nomina_fecha']) }}
                {{ Form::hidden('teso_medio_recaudo_id', '', ['id' => 'pago_nomina_medio_id']) }}
                {{ Form::hidden('teso_medio_recaudo_destino_id', '', ['id' => 'pago_nomina_destino_id']) }}
                {{ Form::hidden('token_solicitud', '', ['id' => 'pago_nomina_token']) }}

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="tabla_empleados_pago_nomina">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:45px">
                                    <input type="checkbox" id="seleccionar_todos_empleados" {{ $cantidadDisponibles > 0 ? 'checked' : '' }} title="Seleccionar/desmarcar todos">
                                </th>
                                <th>Identificación</th>
                                <th>Empleado</th>
                                <th>Nro. Cuenta</th>
                                <th class="text-right">Valor causado</th>
                                <th class="text-right">Saldo a pagar</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lineas as $linea)
                                <tr class="{{ $linea->seleccionable ? '' : 'text-muted' }}">
                                    <td class="text-center">
                                        <input type="checkbox" name="empleados[]" value="{{ $linea->core_tercero_id }}"
                                            class="empleado-pago" data-saldo="{{ $linea->saldo_pendiente }}"
                                            {{ $linea->seleccionable ? 'checked' : 'disabled' }}>
                                    </td>
                                    <td>{{ $linea->numero_identificacion }}</td>
                                    <td>{{ $linea->empleado }}</td>
                                    <td>{{ $linea->cuenta_bancaria }}</td>
                                    <td class="text-right">$ {{ number_format($linea->valor_documento, 2, ',', '.') }}</td>
                                    <td class="text-right">$ {{ number_format($linea->saldo_pendiente, 2, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($linea->estado_pago === 'Disponible')
                                            <span class="label label-success">Disponible</span>
                                        @elseif ($linea->estado_pago === 'Pagado')
                                            <span class="label label-default">Pagado</span>
                                        @else
                                            <span class="label label-warning">Sin CxP</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-right">Seleccionados: <span id="cantidad_seleccionados">{{ $cantidadDisponibles }}</span></th>
                                <th class="text-right">$ <span id="total_seleccionado">{{ number_format($totalDisponible, 2, ',', '.') }}</span></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success" id="btn_pagar_nomina" {{ $cantidadDisponibles === 0 ? 'disabled' : '' }}>
                        <i class="fa fa-money"></i> Pagar seleccionados
                    </button>
                    <span class="pago-nomina-spinner" style="display:none; margin-left:10px;">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </span>
                </div>
            {{ Form::close() }}
        @endif
    </div>
</div>

@if ($historial->count() > 0)
<div class="panel panel-info">
    <div class="panel-heading"><b>Últimas ejecuciones para este documento</b></div>
    <div class="table-responsive">
        <table class="table table-condensed table-bordered" style="margin-bottom:0">
            <thead><tr><th>Fecha</th><th>Pago CxP</th><th>Empleados</th><th class="text-right">Valor</th><th>Estado</th><th>Usuario</th></tr></thead>
            <tbody>
                @foreach ($historial as $proceso)
                    <tr>
                        <td>{{ $proceso->fecha_pago }}</td>
                        <td>
                            @if (!is_null($proceso->documento_pago))
                                <a target="_blank" href="{{ url('tesoreria/pagos_cxp/' . $proceso->teso_doc_encabezado_id . '?id=3&id_modelo=' . $modeloPagoCxpId . '&id_transaccion=33') }}">
                                    {{ $proceso->documento_pago->get_label_documento() }}
                                </a>
                            @else
                                No disponible
                            @endif
                        </td>
                        <td>{{ $proceso->cantidad_empleados }}</td>
                        <td class="text-right">$ {{ number_format($proceso->valor_total, 2, ',', '.') }}</td>
                        <td>{{ $proceso->estado }}</td>
                        <td>{{ $proceso->creado_por }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
