@extends('calificaciones.calificaciones_form_v2', ['titulo'=>'calificaciones'])

@section('tabla')
	
	<?php 
		$cantidad_calificaciones = 16;

		if($hay_pesos)
		{
			$display = 'block';
			$color_btn = '#50B794';
		}else{
			$display = 'none';
			$color_btn = 'white';
		}
	?>	
	<div class="table-responsive">
		<p style="color: #ff4d4d; display: {{$display}}" id="nota_hay_pesos">
			<span style="background-color: #50B794; color: #454b44;">Nota:</span> Algunos encabezados tienen un Peso asignado. La definitiva será calculada con base en la ponderación de estos pesos; si algún encabezado no tiene un peso, esa calificación NO se tendrá en cuenta para la definitiva.
			<p style="color: #50B794">
				<b>Suma de porcentajes:</b> <span id="lbl_suma_pesos">{{$suma_porcentajes}}%</span> 
				<span id="warning_pesos">
					@if($suma_porcentajes > 100) 
						<i class="fa fa-warning" style="color: red;" title="La suma de porcentajes es mayor a la permitida. Por favor, comunicar al administrador del sistema."></i>
					@endif
				</span>
			</p>
		</p>
		<table class="table table-striped" id="tabla_registros">
			<thead>
				<tr>
					<th>Estudiantes</th>
					@for($k=1; $k < $cantidad_calificaciones; $k++)
						<?php
							if( $array_pesos[$k] == 0 )
							{
								$color_btn = 'white';
							}else{
								$color_btn = '#50B794';
							}
						?>	
						<th class="celda_C{{$k}}">
							<button class="btn btn-default btn-xs encabezado_calificacion" value="C{{$k}}" data-peso="{{$array_pesos[$k]}}" title="Peso= {{$array_pesos[$k]}}%" id="btn_C{{$k}}" style="background-color: {{$color_btn}}">C{{$k}}</button>
						</th>
					@endfor
					<th>
						Def.
					</th>
					<th colspan="2">
						Logros Adicionales
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$linea = 1;
					$matriculas_ids_list = '';
					$is_the_first = true;
				?>

				@for( $k = 0; $k < $cantidad_estudiantes; $k++)

					<tr id="fila_{{$linea}}" data-codigo_matricula="{{ $vec_estudiantes[$k]['codigo_matricula'] }}" data-matricula_id="{{ $vec_estudiantes[$k]['matricula_id'] }}" data-id_estudiante="{{ $vec_estudiantes[$k]['id_estudiante'] }}"  data-id_calificacion="{{ $vec_estudiantes[$k]['id_calificacion'] }}"  data-calificacion="{{ $vec_estudiantes[$k]['calificacion'] }}"  data-id_calificacion_aux="{{ $vec_estudiantes[$k]['id_calificacion_aux'] }}" class="fila_calificaciones_estudiante">

						<td width="250px" style="font-size:12px">
							<b> {{$linea}} {{ $vec_estudiantes[$k]['nombre'] }}</b>
						</td>				
						
						@for ($c=1; $c < $cantidad_calificaciones; $c++)
							<td class="celda_C{{$c}}"><input type="text" name="C{{$c}}[]" id="C{{$c.'_'.$linea}}" style="width: 32px;" class="valores_{{$linea}}" value="{{$vec_estudiantes[$k]['C'.$c]}}" autocomplete="off"></td>
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
					<?php
						$linea++;
						if ($is_the_first) {
							$matriculas_ids_list = $vec_estudiantes[$k]['matricula_id'];
							$is_the_first = false;
						}else{
							$matriculas_ids_list .= ',' . $vec_estudiantes[$k]['matricula_id'];
						}						
					?>
				@endfor
				
			</tbody>
		</table>
		
		<input type="hidden" class="form-control" id="matriculas_ids_list" name="matriculas_ids_list" value="{{$matriculas_ids_list}}">
	</div>
@endsection