@extends('calificaciones.calificaciones_form')

<!--

	YA NO SE USA, SE UNIFICÓ CON calificar2.blade

	-->

@section('tabla')
							
	<table class="table table-responsive" id="tabla">
		<thead>
			<tr>
				<th>Estudiante</th>
				<th colspan="16">Calificaciones</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td></td>
				@for($k=1; $k < 16; $k++)
					<td>
						<button class="btn btn-default btn-xs encabezado_calificacion" value="C{{$k}}">C{{$k}}</button>
					</td>
				@endfor
				<td><input type="text" style="border: none;border-color: transparent;width: 32px; text-align: center;" value="Def.">
				</td>
			</tr>						
			<?php
				$linea=1;

				dd($vec_estudiantes);

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
					
			<?php
			for ($c=1; $c < 16; $c++) {

				$name = "C".$c."[]";
				$id = "C".$c."_".$linea;
				$key = "C".$c;

				echo '<td><input type="text" name="'.$name.'" id="'.$id .'" style="width: 32px" class="'.$valores.'" value="'.$vec_estudiantes[$k][$key].'" autocomplete="off"></td>';
			}
			$linea++;
			
			?>
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