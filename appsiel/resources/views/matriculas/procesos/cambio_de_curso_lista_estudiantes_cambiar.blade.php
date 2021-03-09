<h2 style="width: 100%; text-align: center;">Listado de estudiantes</h2>
<hr>

<div class="well">
	Selecciones los estudiantes y elija un curso; luego haga click en el botón Continuar para trasladar la información de los estudiantes de un curso a otro.
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

			<h3 style="width: 100%; text-align: center;">Parámetros para el nuevo curso</h3>
			<hr>
			<br><br>

			<div class="row">
				<div class="col-md-4">
					&nbsp;
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