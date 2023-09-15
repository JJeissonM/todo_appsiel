<div class="row">
	<div class="col-md-6">
		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('periodo_lectivo_id','','Año lectivo', $periodos_lectivos,['required' => 'required']) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('periodo_id','','Periodo',[],['required' => 'required']) }}
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('curso_id','','Curso', $cursos,['required' => 'required']) }}
		</div>
	</div>

	<div class="col-md-6">

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('formato','','Formato', $formatos ,['required' => 'required']) }}
		</div>

		<div class="row campo" style="padding:5px;">
			&nbsp;
		</div>

		<div class="row campo" style="padding:5px;">
			{{ Form::bsSelect('estudiante_id','','Estudiante',[],[]) }}
		</div>
	</div>
</div>