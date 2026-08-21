<div id="div_ingreso_registros">
    <br>
    <h4>Registros encontrados en el libro de Excel</h4>
    <hr>
    <div class="table-responsive" id="table_content">
        <p class="small text-danger">
            Las filas en rojo presentan inconsistencias y no serán almacenadas. El número de fila corresponde al libro cargado.
        </p>

        <table class="table table-striped table-hover" id="ingreso_registros">
            <thead>
                <tr>
                    <th data-override="con_errores" style="display: none;"></th>
                    <th data-override="core_tercero_id" style="display: none;"></th>
                    <th data-override="nom_contrato_id" style="display: none;"></th>
                    <th data-override="nom_concepto_id" style="display: none;"></th>
                    <th data-override="cantidad_horas" style="display: none;"></th>
                    <th data-override="valor" style="display: none;"></th>
                    <th data-override="numero_fila_excel" style="display: none;"></th>
                    <th>Fila de Excel</th>
                    <th>Empleado</th>
                    <th>Núm. identificación</th>
                    <th>Cargo</th>
                    <th>Concepto</th>
                    <th>Naturaleza</th>
                    <th>Cant. horas</th>
                    <th>Valor</th>
                    <th>Validación / acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php $cantidad_registros = 0; ?>
                @foreach($lineas_libro_excel as $linea)
                    <?php
                        $con_errores = count($linea->errores) > 0 ? 1 : 0;
                        $clase_danger = $con_errores ? 'danger' : '';
                        $cargo = isset($linea->contrato->cargo) && !is_null($linea->contrato->cargo)
                            ? $linea->contrato->cargo->descripcion
                            : 'Sin cargo';
                        $cantidad_registros += 1 - $con_errores;
                    ?>

                    <tr class="{{ $clase_danger }}">
                        <td style="display: none;" class="con_errores">{{ $con_errores }}</td>
                        <td style="display: none;">{{ $linea->tercero->id }}</td>
                        <td style="display: none;">{{ $linea->contrato->id }}</td>
                        <td style="display: none;">{{ $linea->concepto->id }}</td>
                        <td style="display: none;">{{ $linea->cantidad_horas }}</td>
                        <td style="display: none;">{{ $linea->valor }}</td>
                        <td style="display: none;">{{ $linea->numero_fila }}</td>
                        <td>{{ $linea->numero_fila }}</td>
                        <td>{{ $linea->tercero->descripcion }}</td>
                        <td>{{ $linea->numero_identificacion }}</td>
                        <td>{{ $cargo }}</td>
                        <td>{{ $linea->concepto->id }} - {{ $linea->concepto->descripcion }}</td>
                        <td>{{ $linea->concepto->naturaleza }}</td>
                        <td class="text-right">{{ number_format($linea->cantidad_horas, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($linea->valor, 2, ',', '.') }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-xs btn_eliminar" title="Excluir fila">
                                <i class="fa fa-trash"></i>
                            </button>

                            @if($linea->cantidad_horas > 0 && $linea->valor > 0 && !$con_errores)
                                <div class="text-warning small" style="margin-top: 5px;">
                                    Se calculará el valor usando las horas; el valor del libro será ignorado.
                                </div>
                            @endif

                            @if($con_errores)
                                <ul class="text-danger small" style="margin: 5px 0 0; padding-left: 18px;">
                                    @foreach($linea->errores as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="text-center">
    {{ Form::open(['url' => 'nom_almacenar_registros_via_interface', 'id' => 'form_almacenar_registros']) }}
        <input type="hidden" name="documento_encabezado_id" id="documento_encabezado_id" value="{{ $nom_doc_encabezado_id }}">
        <input type="hidden" name="lineas_registros" id="lineas_registros" value="0">

        <b>Número de registros correctos: <span id="div_cantidad_registros">{{ $cantidad_registros }}</span></b>
        <br><br>

        @if($cantidad_registros > 0)
            <button class="btn btn-primary" id="btn_almacenar_registros">
                <i class="fa fa-save"></i> Almacenar registros
            </button>
        @endif
    {{ Form::close() }}
</div>
