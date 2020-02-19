@extends('calificaciones.calificaciones_form')

@section('tabla')
	
	<?php 
		$cantidad_calificaciones = 16;
	?>	
	<div class="table-responsive">
		<table class="table table-striped" id="tabla_registros">
			<thead>
				<tr>
					<th>Estudiantes</th>
					@for($k=1; $k < $cantidad_calificaciones; $k++)
						<th class="celda_C{{$k}}">
							<button class="btn btn-default btn-xs encabezado_calificacion" value="C{{$k}}">C{{$k}}</button>
						</th>
					@endfor
					<th>
						Def.
					</th>
				</tr>
			</thead>
			<tbody>
				<?php $linea=1; ?>

				@for($k=0;$k<$cantidad_estudiantes;$k++)		

					<tr valign="{{$linea}}" data-codigo_matricula="{{ $vec_estudiantes[$k]['codigo_matricula'] }}"  data-id_estudiante="{{ $vec_estudiantes[$k]['id_estudiante'] }}"  data-id_calificacion="{{ $vec_estudiantes[$k]['id_calificacion'] }}"  data-calificacion="{{ $vec_estudiantes[$k]['calificacion'] }}"  data-id_calificacion_aux="{{ $vec_estudiantes[$k]['id_calificacion_aux'] }}">

						<td width="250px" style="font-size:12px">
							<b>{{ $vec_estudiantes[$k]['nombre'] }}</b>
						</td>				
						
						@for ($c=1; $c < $cantidad_calificaciones; $c++)
							<td class="celda_C{{$c}}"><input type="text" name="C{{$c}}[]" id="C{{$c.'_'.$linea}}" style="width: 32px;" class="valores_{{$linea}}" value="{{$vec_estudiantes[$k]['C'.$c]}}" autocomplete="off"></td>
						@endfor

						<td>
							<input type="text" name="calificacion_texto[]" id="{{ "calificacion_texto".$linea }}" style="width: 32px;"  value="{{ $vec_estudiantes[$k]['calificacion'] }}" disabled="disabled">
						</td>
					</tr>
					<?php $linea++; ?>
				@endfor
				
			</tbody>
		</table>
	</div>
@endsection