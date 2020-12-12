{{ Form::open( [ 'url' => 'sga_notas_nivelaciones_actualizar', 'id' => 'form_actualizar' ] ) }}

	<div class="row" style="padding:5px;">
		<div class="alert alert-{{$clase_mensaje}} alert-dismissible" style="display: none;" id="div_mensaje">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<b> {{ $mensaje }} </b>
		</div>
	</div>

	<div class="row" style="padding:5px;">
		<b>Estudiante: </b> {{ $nota_nivelacion->estudiante->tercero->descripcion }}
	</div>

	<div class="row" style="padding:5px;">
		<b>Periodo: </b> {{ $nota_nivelacion->periodo->descripcion }}
	</div>

	<div class="row" style="padding:5px;">
		<b>Curso: </b> {{ $nota_nivelacion->curso->descripcion }}
	</div>

	<div class="row" style="padding:5px;">
		<b>Asignatura: </b> {{ $nota_nivelacion->asignatura->descripcion }}
	</div>

	<div class="row" style="padding:5px;">
		{{ Form::bsText( 'calificacion', $nota_nivelacion->calificacion, 'Calificación de nivelación',[] ) }}
	</div>

	<div class="form-group">
		<label class="control-label col-sm-3 col-md-3" for="calificacion"></label>
		<div class="col-sm-9 col-md-9">
			(Ingresar cero para eliminar la calificación.)
		</div>
	</div>

	<div class="row" style="padding:5px;">
		{{ Form::bsText( 'observacion', $nota_nivelacion->observacion, 'Observación',[] ) }}
	</div>

	{{ Form::hidden( 'estudiante_id', $nota_nivelacion->estudiante->id) }}
	{{ Form::hidden( 'periodo_id', $nota_nivelacion->periodo->id) }}
	{{ Form::hidden( 'curso_id', $nota_nivelacion->curso->id) }}
	{{ Form::hidden( 'asignatura_id', $nota_nivelacion->asignatura->id) }}
	{{ Form::hidden( 'nota_nivelacion_id', $nota_nivelacion->id ) }}
	{{ Form::hidden( 'escala_valoracion_maxima', $escala_valoracion_maxima, ['id'=>'escala_valoracion_maxima'] ) }}
	
{{ Form::close() }}

<div class="row" style="padding:5px; text-align: center;">
	<button class="btn btn-success btn-sm" id="btn_guardar"><i class="fa fa-save"></i> Guardar</button>
</div>