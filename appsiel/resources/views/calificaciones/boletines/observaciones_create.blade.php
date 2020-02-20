@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

<div class="row">
	<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
	    <h4 style="color: gray;">Ingreso de observaciones del boletines</h4>
	    <hr>

	    {{ Form::open( [ 'url'=>'calificaciones/observaciones_boletin/observaciones_create2?id='.Input::get('id') ] ) }}

			<div class="row" style="padding:5px;">
				{{ Form::bsSelect('id_periodo','','Seleccionar periodo',$periodos,['required'=>'required']) }}
			</div>

			<div class="row" style="padding:5px;">
				{{ Form::bsSelect('curso_id','','Seleccionar curso',$cursos,['required'=>'required']) }}
			</div>

			<div class="form-group">
				<div class="col-sm-offset-3 col-sm-6">
					<br/>
					<button type="submit" class="btn btn-primary" id="btn_continuar">
						<i class="fa fa-btn fa-arrow-right"></i>Continuar
					</button>
					<br/><br/>
				</div>
			</div>

			<br/><br/>	
		{{Form::close()}}
		
	</div>
</div>

@endsection

@section('scripts')
	<script>
		$(document).ready(function(){

			$("#id_periodo").focus();

			$("#id_periodo").on('change',function(){
				$("#curso_id").focus();
			});

			$("#curso_id").on('change',function(){
				$("#btn_continuar").focus();
			});
			
		});
	</script>
@endsection