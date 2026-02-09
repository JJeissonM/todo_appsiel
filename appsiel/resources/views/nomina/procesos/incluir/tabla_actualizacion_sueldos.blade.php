@if(count($lineas) == 0)
    <div class="alert alert-warning">No se encontraron empleados con contrato activo para la selección realizada.</div>
@else
    {{ Form::open(['url'=>'nomina/procesos/actualizar_sueldos/guardar','id'=>'formulario_guardar','files' => false]) }}
        <input type="hidden" name="grupo_empleado_id" value="{{ $grupo_empleado_id }}">
        <input type="hidden" name="porcentaje" value="{{ $porcentaje }}">

        <div class="row" style="padding:5px;">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <strong>Porcentaje de aumento:</strong> {{ number_format($porcentaje, 2, ',', '.') }}%
                    @if(!is_null($grupo))
                        <br>
                        <strong>Grupo seleccionado:</strong> {{ $grupo->descripcion }}
                    @endif
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="tabla_actualizacion_sueldos">
                <thead>
                    <tr>
                        <th>Grupo empleado</th>
                        <th>Nombre completo</th>
                        <th>Cargo</th>
                        <th>Fecha ingreso</th>
                        <th class="text-right">Sueldo actual</th>
                        <th class="text-right">Nuevo sueldo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lineas as $linea)
                        <tr>
                            <td>{{ $linea->grupo_empleado }}</td>
                            <td>{{ $linea->empleado }}</td>
                            <td>{{ $linea->cargo }}</td>
                            <td>{{ $linea->fecha_ingreso }}</td>
                            <td class="text-right">
                                <span class="salario-actual" data-value="{{ $linea->sueldo }}">{{ number_format($linea->sueldo, 0, ',', '.') }}</span>
                                <input type="hidden" name="salario_anterior[]" value="{{ $linea->sueldo }}">
                            </td>
                            <td class="text-right">
                                <input type="hidden" name="nom_contrato_id[]" value="{{ $linea->contrato_id }}">
                                <input type="hidden" name="nuevo_sueldo[]" class="nuevo-sueldo-raw" value="{{ $linea->nuevo_sueldo }}">
                                <input type="text" class="form-control input-sm text-right input-nuevo-sueldo" value="{{ number_format($linea->nuevo_sueldo, 0, ',', '.') }}">
                            </td>
                            <td>
                                <button class="btn btn-xs btn-danger btn-remover-fila">Remover fila</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-right">Totales</th>
                        <th class="text-right"><span id="total_sueldo_actual" data-value="0">0</span></th>
                        <th class="text-right"><span id="total_sueldo_nuevo" data-value="0">0</span></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row" style="padding:5px; text-align: center;">
            <div class="col-md-12">
                <button class="btn btn-primary" id="btn_guardar_actualizacion"> <i class="fa fa-save"></i> Guardar actualización </button>
            </div>
        </div>
    {{ Form::close() }}
@endif
