@extends('calificaciones.calificaciones_form')

@section('tabla')
							
	<table class="table table-striped table-responsive" id="tabla">
		<thead>
			<tr>
				<th>Estudiante</th>
				<th colspan="16">Calificaciones</th>
			</tr>
			<tr>
				<th></th>
				@for($k=1; $k < 16; $k++)
					<th class="celda_C{{$k}}">
						<button class="btn btn-default btn-xs encabezado_calificacion" value="C{{$k}}">C{{$k}}</button>
					</th>
				@endfor
				<th><input type="text" style="border: none;border-color: transparent;width: 32px; text-align: center;" value="Def.">
				</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				$linea=1;

			for($k=0;$k<$cantidad_estudiantes;$k++)
			{ 
				$estudiante="estudiante".$linea;
				$calificacion="calificacion".$linea;
				$calificacion_aux="calificacion_aux".$linea;
				$valores="valores_".$linea;
				$nombre_completo = $vec_estudiantes[$k]['nombre'];
				?>
				<tr> 
					<td width="250px" style="font-size:12px">
						<b>{{ $nombre_completo }}</b>
						
						{{ Form::hidden('estudiante[]', $vec_estudiantes[$k]['id_estudiante'], ['id' =>$estudiante]) }}

					</td>

					{{ Form::hidden('id_calificacion[]',$vec_estudiantes[$k]['id_calificacion']) }}
					{{ Form::hidden('id_calificacion_aux[]',$vec_estudiantes[$k]['id_calificacion_aux']) }}
					
					
					@for ($c=1; $c < 16; $c++)
						<td class="celda_C{{$c}}"><input type="text" name="C{{$c}}[]" id="C{{$c.'_'.$linea}}" style="width: 32px" class="valores_{{$linea}}" value="{{$vec_estudiantes[$k]['C'.$c]}}" autocomplete="off"></td>
					@endfor

					<?php $linea++; ?>
					<td>
						<input type="text" name="calificacion_aux[]" id="{{ $calificacion_aux }}" style="width: 32px"  value="{{ $vec_estudiantes[$k]['calificacion'] }}" disabled="disabled">
						<input type="hidden" name="calificacion[]" id="{{ $calificacion }}" style="width: 32px"  value="{{ $vec_estudiantes[$k]['calificacion'] }}">
					</td>
					{{ Form::hidden('codigo_matricula[]',$vec_estudiantes[$k]['codigo_matricula']) }}

			</tr>
			<?php 
			} // Fin for cada estudiante
			?>
		</tbody>
	</table>
@endsection