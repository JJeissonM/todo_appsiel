@extends('calificaciones.encabezados_fijos.lineal.calificaciones_form_v2', ['titulo'=>'calificaciones'])

@section('tabla')
	
	<?php 
		$cantidad_calificaciones = 16;

        $arr_labels = [
            [ '#cdddd8', 'Tareas'],
            [ '#ee8f6a', 'Quiz'],
            [ '#fffc2e', 'Exposición'],
            [ '#5b94e9', 'Mesa trabajo, apreciativa, participación'],
            [ '#e070e0', 'Ex. Final'],
            [ '#8df8a5', 'Prueba externa']
        ];
	?>	
	<div class="table-responsive">
		<p style="color: #ff4d4d;" id="nota_hay_pesos">
			<span style="background-color: #50B794; color: #454b44;">Nota:</span> Algunos encabezados tienen un Peso asignado. La definitiva será calculada con base en la ponderación de estos pesos; si algún encabezado no tiene un peso, esa calificación NO se tendrá en cuenta para la definitiva.
		</p>
		<table class="table table-striped" id="tabla_registros">
			@include('calificaciones.incluir.encabezados_fijos.lineal.encabezados_tabla', ['arr_labels_adicionales'=> ['Def.','Logros adicionales','']])
			<tbody>
				<?php
					$linea=1;
				?>

				@for( $k = 0; $k < $cantidad_estudiantes; $k++)

					<tr id="fila_{{$linea}}" data-codigo_matricula="{{ $vec_estudiantes[$k]['codigo_matricula'] }}"  data-id_estudiante="{{ $vec_estudiantes[$k]['id_estudiante'] }}"  data-id_calificacion="{{ $vec_estudiantes[$k]['id_calificacion'] }}"  data-calificacion="{{ $vec_estudiantes[$k]['calificacion'] }}"  data-id_calificacion_aux="{{ $vec_estudiantes[$k]['id_calificacion_aux'] }}">

						<td width="250px" style="font-size:12px">
							<b> {{$linea}} {{ $vec_estudiantes[$k]['nombre'] }}</b>
						</td>				
						
						@for ($c=1; $c < $cantidad_calificaciones; $c++)
							<td class="celda_C{{$c}}">
								<input type="text" name="C{{$c}}[]" id="C{{$c.'_'.$linea}}" style="width: 32px;" class="valores_{{$linea}}" value="{{$vec_estudiantes[$k]['C'.$c]}}" autocomplete="off">
							</td>
						@endfor

						<td>
							<input type="text" name="calificacion_texto[]" id="{{ "calificacion_texto".$linea }}" style="width: 32px;"  value="{{ $vec_estudiantes[$k]['calificacion'] }}" disabled="disabled">
						</td>


						<td width="50px"> 
							<input type="text" name="caja_logros[]" id="logros_{{$linea}}" size="3" class="caja_logros" value="{{ $vec_estudiantes[$k]['logros'] }}">
							<input type="hidden" id="caja_logro">
						</td>
						<td> 
							<a href="#" onclick="ventana({{$datos_asignatura->id}},{{ $linea }},{{$curso->id}});"> <i class="fa fa-btn fa-search"></i> </a>
						</td>
					</tr>
					<?php $linea++; ?>
				@endfor
				
			</tbody>
		</table>
	</div>
@endsection