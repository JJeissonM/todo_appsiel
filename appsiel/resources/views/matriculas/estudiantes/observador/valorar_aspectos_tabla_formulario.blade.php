<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th rowspan="2">No.</th>
                <th rowspan="2">ASPECTOS</th>
                <th colspan="4">Periodos</th>
            </tr>
            <tr>
                <th>1°</th>
                <th>2°</th>
                <th>3°</th>
                <th>4°</th>
            </tr>
        </thead>
        <tbody>
            {{ Form::hidden('id_estudiante',$estudiante->id) }}
            {{ Form::hidden('matricula_id', $matricula_a_mostrar->id ) }}

            {{ Form::hidden('fecha_valoracion', date( $anio_matricula . '-' . 'm-d') ) }}

            @foreach ($tipos_aspectos as $tipo_aspecto)
                <tr><td colspan="6"><b>{{ $tipo_aspecto->descripcion }}</b></td></tr>
                @php 
                    $aspectos = App\Matriculas\CatalogoAspecto::where('id_tipo_aspecto','=',$tipo_aspecto->id)->orderBy('orden','ASC')->get()
                @endphp
                
                @foreach ($aspectos as $aspecto)
                    <?php 
                        $val_per1 = "";
                        $val_per2 = "";
                        $val_per3 = "";
                        $val_per4 = "";
                        $aspecto_estudiante_id="";
                        
                        $aspecto_estudiante = App\Matriculas\AspectosObservador::where('id_aspecto','=',$aspecto->id)->where('id_estudiante','=',$estudiante->id)->where('fecha_valoracion','like', $anio_matricula.'%')->get()->first();
                        
                        if( !is_null($aspecto_estudiante) )
                        {
                            $val_per1 = $aspecto_estudiante->valoracion_periodo1;
                            $val_per2 = $aspecto_estudiante->valoracion_periodo2;
                            $val_per3 = $aspecto_estudiante->valoracion_periodo3;
                            $val_per4 = $aspecto_estudiante->valoracion_periodo4;
                            $aspecto_estudiante_id = $aspecto_estudiante->id;
                        }

                    ?>
                    <tr>
                        {{ Form::hidden('aspecto_estudiante_id[]',$aspecto_estudiante_id) }}
                        {{ Form::hidden('id_aspecto[]',$aspecto->id) }}
                        <td>{{ $aspecto->orden }}</td>
                        <td>{{ $aspecto->descripcion }}</td>
                        <td>{{ Form::text('valoracion_periodo1[]', $val_per1, ['size' => 1]) }}</td>
                        <td>{{ Form::text('valoracion_periodo2[]', $val_per2, ['size' => 1]) }}</td>
                        <td>{{ Form::text('valoracion_periodo3[]', $val_per3, ['size' => 1]) }}</td>
                        <td>{{ Form::text('valoracion_periodo4[]', $val_per4, ['size' => 1]) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>

    </table>
</div>