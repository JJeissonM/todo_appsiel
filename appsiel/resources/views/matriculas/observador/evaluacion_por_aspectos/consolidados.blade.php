<div class="container-fluid">
	<div class="marco_formulario">
	    <h4 style="text-align: center;">
	    	Ingreso de valoraciones de estudiantes 
			<br> 
			Año lectivo: {{ $periodo_lectivo->descripcion }}
	    </h4>
	    <hr>

		<div class="row">
			<div class="col-sm-12">
				<b>Fecha:</b><code> desde {{ $fecha_desde }} hasta {{ $fecha_hasta }} </code>
				<b>Curso:</b><code>{{ $curso->descripcion }}</code>
				<b>Asignatura:</b><code>{{ $datos_asignatura->descripcion }}</code>
			</div>							
		</div>

		<div class="row">
			<div class="col-sm-12">
				<div class="table-responsive">
					{{ Form::open( [ 'url' => 'sga_observador_evaluacion_por_aspectos_almacenar_consolidado', 'method'=> 'POST', 'class' => 'form-horizontal', 'id' => 'form_create'] ) }}

						{{ Form::hidden('id_colegio', $id_colegio, []) }}
						{{ Form::hidden('creado_por', $creado_por, []) }}
						{{ Form::hidden('modificado_por', $modificado_por, []) }}
						{{ Form::hidden('curso_id', $curso->id, []) }}
						{{ Form::hidden('id_asignatura', $datos_asignatura->id, []) }}
						{{ Form::hidden('cantidad_estudiantes', $cantidad_estudiantes, []) }}
						{{ Form::hidden('cantidad_items_aspectos', $cantidad_items_aspectos, []) }}	

						{{ Form::hidden('url_id',Input::get('id')) }}

						<table class="table table-striped" id="tabla_registros">
							<thead>
								<tr>
									<th>&nbsp;</th>
									@foreach( $tipos_aspectos AS $tipo_aspecto )
										<?php $cant_items_del_tipo = $items_aspectos->where('id_tipo_aspecto', $tipo_aspecto->id )->count() ?>
										<th colspan="{{$cant_items_del_tipo}}" align="center">{{$tipo_aspecto->descripcion}}</th>
									@endforeach
									<th> &nbsp; </th>
									<th> &nbsp; </th>
								</tr>
								<tr>
									<th>Estudiantes</th>
									@foreach( $items_aspectos AS $item_aspecto )
										<th class="celda_C{{$item_aspecto->id}}" align="center">
											{{$item_aspecto->descripcion}}
										</th>
									@endforeach
									<th> Frecuencia </th>
									<th> Observación </th>
								</tr>
							</thead>
							<tbody>
								<?php 

									$linea=1;
								?>

								@for( $k = 0; $k < $cantidad_estudiantes; $k++)

									<tr valign="{{$linea}}" title="{{$vec_estudiantes[$k]['nombre']}}">									

										<td width="250px" style="font-size:12px">
											<b> {{$linea}} {{ $vec_estudiantes[$k]['nombre'] }}</b>
											{{ Form::hidden('codigo_matricula[]',$vec_estudiantes[$k]['codigo_matricula'],[]) }}
											{{ Form::hidden('id_estudiante[]',$vec_estudiantes[$k]['id_estudiante'],[]) }}
										</td>				
										
										@for ( $c=1; $c <= $cantidad_items_aspectos; $c++ )
											<td class="celda_C{{$c}}">
												{!! $vec_estudiantes[$k]['valoraciones_aspectos']['valores_item_'.$c] !!}
											</td>
										@endfor

										<td>
											<?php 
												$valoracion = get_frecuencia( $vec_estudiantes[$k]['valoraciones_aspectos_ids'], $k );
												echo $valoracion->lbl_valoracion;
											?>
											{{ Form::hidden('frecuencia[]',$valoracion->value_valoracion,[]) }}
										</td>
										<td title="{{$vec_estudiantes[$k]['nombre']}}">
											{{ Form::select( 'observacion[]', $observaciones, [], array_merge( [ 'id' => 'observacion_'.$k ], [] )) }}
										</td>
									</tr>
									<?php $linea++; ?>
								@endfor
								
							</tbody>
						</table>

						<div style="text-align: center; width: 100%;">
							<button class="btn btn-primary btn-xs" id="bs_boton_guardar">Guardar</button>
						</div>
					{{Form::close()}}
				</div>				
			</div>
		</div>

	</div>
</div>

<?php

	function get_frecuencia( $valoraciones_est, $numero_fila )
	{
		$array_convenciones = ['','Alto','Medio','Bajo'];

        $array_valoracion = [];
        $title = '';
        $hay_alto = 0;
        $hay_medio = 0;
        $hay_bajo = 0;
        foreach ( $valoraciones_est as $key => $convencion_valoracion_id )
        {
            //

            if ( $numero_fila == 1 )
	        {
	        	//dd( $valoraciones_est, $convencion_valoracion_id );
	        }

            switch ( $convencion_valoracion_id )
            {
                case '1':
                    $hay_alto++;
                break;
              
                case '2':
                    $hay_medio++;
                break;
              
                case '3':
                    $hay_bajo++;
                break;
              
                default:
                    break;
            }
        }

        if ( $numero_fila == 1 )
        {
        	//dd([$hay_alto, $hay_medio, $hay_bajo]);
        }
        	
        $color_fondo = 'yellow';
        $color_texto = 'black';
        $lbl_valoracion = '--';
        $value_valoracion = 0;

        if ( $hay_alto == 2 && $hay_medio == 1 )
        {
            $color_fondo = 'purple';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 1 ];
            $value_valoracion = 1;
        }

        if ( ($hay_alto == 1 || $hay_alto == 2 || $hay_alto == 3 ) && ($hay_medio == 0 || $hay_bajo == 0 ) )
        {
            $color_fondo = 'purple';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 1 ];
            $value_valoracion = 1;
        }

        if ( $hay_alto == 1 && $hay_medio == 2 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( ($hay_medio == 1 || $hay_medio == 2 || $hay_medio == 3 ) && ($hay_alto == 0 || $hay_bajo == 0 ) )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_alto == 1 && $hay_medio == 1 && $hay_bajo == 1 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_alto == 2 && $hay_bajo == 1 )
        {
            $color_fondo = 'yellow';
            $color_texto = 'black';
            $lbl_valoracion = $array_convenciones[ 2 ];
            $value_valoracion = 2;
        }

        if ( $hay_medio == 2 && $hay_bajo == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( $hay_bajo == 2 && $hay_alto == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( $hay_bajo == 2 && $hay_medio == 1 )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        if ( ($hay_bajo == 1 || $hay_bajo == 2 || $hay_bajo == 3 ) && ($hay_alto == 0 || $hay_medio == 0 ) )
        {
            $color_fondo = 'red';
            $color_texto = 'white';
            $lbl_valoracion = $array_convenciones[ 3 ];
            $value_valoracion = 3;
        }

        $valoracion = '<span style="background: ' . $color_fondo . '; color:' . $color_texto . ';" title="' . $title . '">' . $lbl_valoracion . '</span>';

        return (object)[ 'lbl_valoracion' => $valoracion, 'value_valoracion' => $value_valoracion ];
	}
?>