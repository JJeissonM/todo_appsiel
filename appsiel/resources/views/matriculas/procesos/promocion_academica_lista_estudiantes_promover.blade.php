<h2 style="width: 100%; text-align: center;">Listado de estudiantes</h2>
<hr>

<div class="well">
	Selecciones los estudiantes y elija un año lectivo y curso; luego haga click en el botón Continuar poder matricular los estudiantes en el siguiente curso.
	<br>
	<span class="text-danger">Nota: Este proceso no se puede revertir. Si se quiere devolver, deberá eliminar una por una a las nuevas matrículas creadas.</span>
</div>

<br><br>
<div class="table-responsive">
	<table class="table table-striped table-bordered" id="tabla_lista_estudiantes">
		<thead>
			<tr>
				<th style="display: none;">matricula_id</th>
				<th data-override="checkbox"><input type="checkbox" class="btn-gmail-check" id="checkbox_head"></th>
				<td>No.</td>
				<td>Estudiante</td>
				<td>Grado / Curso Actual</td>
				<td>Perido final</td>
				<td>Nota promedio final</td>
				<td>¿Niveló periodo final?</td>
				<td></td>
			</tr>
		</thead>
		<tbody>
			<?php $linea = 1; ?>
				@foreach( $matriculas As $matricula )
					<tr>
						<td style="display: none;">{{ $matricula->id }}</td>
						<td>
							<input type="checkbox" value="0" class="btn-gmail-check checkbox_fila" name="checkbox_fila[]">
							<span class="checkbox_aux" style="color: transparent;">0</span>
						</td>
						<td> {{ $linea }}</td>
						<td> {{ $matricula->estudiante->tercero->descripcion }}</td>
						<td> {{ $matricula->curso->grado->descripcion }} / {{ $matricula->curso->descripcion }}</td>
						<td> {{ $matricula->periodo_final->descripcion }} </td>
							<?php
								$color_text = 'black';
                                if ( $matricula->promedio_final <= $tope_escala_valoracion_minima )
                                {
                                    $color_text = 'red';
                                }
							?>
						<td> <span style="color: {{$color_text}}"> {{ number_format( $matricula->promedio_final, 2,',','.') }} </span> </td>
						<td>
							<?php
								$hizo_nivelacion = 'No'; 
								if( $matricula->cantidad_nivelaciones > 0 )
								{
									$hizo_nivelacion = 'Si';
								}
							?> 
							{{ $hizo_nivelacion }}
						</td>
						<td> </td>
					</tr>
					<?php $linea++; ?>
				@endforeach
		</tbody>
	</table>
</div>

@if( $linea > 1 )

	<div class="container-fluid">
		<div class="marco_formulario">

			<h3 style="width: 100%; text-align: center;">Parámetros para las nuevas matrículas</h3>
			<hr>
			<br><br>
			<div class="row">
				<div class="col-md-4">
					{{ Form::bsFecha('fecha',date('Y-m-d'),'Fecha matrícula',[],['required'=>'required']) }}
				</div>
				<div class="col-md-4">
					&nbsp;
				</div>
				<div class="col-md-4">
					&nbsp;
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					{{ Form::bsSelect('periodo_lectivo_promover_id',null,'Nuevo año lectivo',\App\Matriculas\PeriodoLectivo::opciones_campo_select(),['required'=>'required']) }}
				</div>
				<div class="col-md-4">
					{{ Form::bsSelect('curso_promover_id',null,'Nuevo curso',$opciones_cursos,['required'=>'required']) }}
				</div>
				<div class="col-md-4">
					<button class="btn btn-primary" id="btn_promover_check"> <i class="fa fa-check"></i> Continuar </button>
				</div>
			</div>
		</div>
	</div>
@endif