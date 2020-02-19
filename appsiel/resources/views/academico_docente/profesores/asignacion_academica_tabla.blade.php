<div class="table-responsive">

	<table class="table table-striped table-bordered" id="lista_asignaciones">
		<thead>
			<tr>
				<th colspan="4"><h4 style="color: gray;">Profesor <b style="color: red;">{{ $profesor->name }}</b></h4></th>
			</tr>
			<tr>
				<th>Curso</th>
				<th>Asignatura</th>
				<th>Intensidad horaria</th>
				<th>Acción</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				$total_ih = 0;
				$i = 1;
			?>
			@foreach($listado_asignaciones as $fila)

				<?php 
					$intensidad_horaria = App\Calificaciones\CursoTieneAsignatura::intensidad_horaria_asignatura_curso( $periodo_lectivo->id, $fila->curso_id, $fila->id_asignatura );
				?>

				@include( 'academico_docente.profesores.asignacion_academica_tabla_fila', [
	            												'curso_descripcion' => $fila->Curso,
	            												'asignatura_descripcion' => $fila->Asignatura,
	            												'intensidad_horaria' => $intensidad_horaria, 
	            												'asignacion_id' => $fila->id
	            											] )

				<?php 
					$total_ih += $intensidad_horaria;
					$i++;
				?>
			@endforeach
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2"></td>
				<td>
					<div id="ih_total">
						{{ $total_ih }}
					</div>
				</td>
				<td></td>
			</tr>
			<tr>
				<td colspan="4">
					<?php 
						$i--;
					?>
					<b> Total asignaturas: </b>
					<input type="hidden" id="total_asignaturas" name="total_asignaturas" value="{{ $i }}">
					<div id="lbl_total_asignaturas" style="display: inline;">
						{{ $i }}
					</div>
				</td>
			</tr>
		</tfoot>
	</table>
</div>