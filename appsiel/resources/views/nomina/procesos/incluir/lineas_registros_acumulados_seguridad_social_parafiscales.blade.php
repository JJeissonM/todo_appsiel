<div id="div_ingreso_registros">
    <br>
    <h4>Líneas de registros a ingresar </h4>
    <hr>
    <div class="table-responsive" id="table_content">
    
        <h5> Seguridad Social </h5>
        <hr>
        <table class="table table-striped table-hover" id="ingreso_registros">
            <thead>
                <tr>
                    <th data-override="con_errores" style="display: none;"></th>
                    <th data-override="nom_entidad_id" style="display: none;"></th>
                    <th data-override="nom_concepto_id" style="display: none;"></th>
                    <th data-override="lapso" style="display: none;"></th>
                    <th data-override="valor_acumulado" style="display: none;"></th>
                    <th>Línea</th>
                    <th>Entidad</th>
                    <th>Núm. identificación</th>
                    <th>Concepto</th>
                    <th>Valor acumulado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $l = 1;
                    $cantidad_registros = 0;
                ?>
                @foreach( $lineas_archivo_plano as $linea )
                    <?php
                        $con_errores = 0;
                        $clase_danger = '';
                        $mensaje_error_valores = '';

                        if ( $linea->tercero->error != '' || $linea->contrato->error != '' || $linea->concepto->error != '' )
                        {
                            $clase_danger = 'danger';
                            $con_errores = 1;
                        }

                        if ( $linea->tercero->id == 0 || $linea->contrato->id == 0 || $linea->concepto->id == 0 )
                        {
                            $clase_danger = 'danger';
                            $con_errores = 1;
                        }

                        if ( ($linea->cantidad_horas + $linea->valor) == 0 )
                        {
                            $clase_danger = 'danger';
                            $con_errores = 1;
                            $mensaje_error_valores = '<br><span style="color: red;">No se han ingresado datos de Cantidad de horas ni de Valor para el concepto.</span>';
                        }
                    ?>

                    <tr id="linea_ingreso_default" class="{{ $clase_danger }}">
                        <td style="display: none;" class="con_errores">{{ $con_errores }}</td>
                        <td style="display: none;">{{ $linea->tercero->id }}</td>
                        <td style="display: none;">{{ $linea->contrato->id }}</td>
                        <td style="display: none;">{{ $linea->concepto->id }}</td>
                        <td style="display: none;">{{ $linea->cantidad_horas }}</td>
                        <td style="display: none;">{{ $linea->valor }}</td>
                        <td> {{ $l }} </td>
                        <td> {!! $linea->tercero->descripcion !!} </td>
                        <td> {{ number_format( $linea->tercero->numero_identificacion, 0,',','.' ) }} </td>
                        <td> {!! $linea->contrato->cargo->descripcion !!} </td>
                        <td> {{ $linea->concepto->id }} - {!! $linea->concepto->descripcion !!} </td>
                        <td> {{ $linea->concepto->naturaleza }} </td>
                        <td style="text-align: right;"> {{ number_format( $linea->cantidad_horas, 2,',','.' ) }} </td>
                        <td style="text-align: right;"> ${{ number_format( $linea->valor, 2,',','.' ) }} </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-trash" title="Eliminar fila"></i></button>
                            {!! $mensaje_error_valores !!}
                        </td>
                    </tr>
                    <?php
                        $l++;
                        $cantidad_registros = $cantidad_registros + 1 - $con_errores;
                    ?>
                 @endforeach
            </tbody>
        </table>
    </div>
</div>

<div style="text-align: center;">
    {{ Form::open( [ 'url' => 'nom_almacenar_registros_via_interface', 'id' => 'form_almacenar_registros' ] ) }}

        <input type="hidden" name="documento_encabezado_id" id="documento_encabezado_id" value="{{ $nom_doc_encabezado_id }}">
        <input type="hidden" name="lineas_registros" id="lineas_registros" value="0">

        <b>Número de registros correctos: <div id="div_cantidad_registros" style="display: inline;">{{ $cantidad_registros }} </div></b>

        <br><br>
        
        @if( $cantidad_registros > 0 )
            <button class="btn btn-primary" id="btn_almacenar_registros"> <i class="fa fa-save"></i> Almacenar Registros </button>
        @endif

    {{ Form::close() }}        
</div>