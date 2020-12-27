<div class="row">
	<div class="col-md-6">
		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('periodo_lectivo_id','','Año lectivo',$periodos_lectivos,['required' => 'required']) }}
		</div>

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('periodo_id','','Periodo',[],['required' => 'required']) }}
		</div>

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('curso_id','','Curso',$cursos,['required' => 'required']) }}
		</div>
	</div>

	<div class="col-md-6">

		<div class="row" style="padding:5px;">
			{{ Form::bsSelect('formato','','Formato', $formatos ,['required' => 'required']) }}
		</div>
	</div>
</div>