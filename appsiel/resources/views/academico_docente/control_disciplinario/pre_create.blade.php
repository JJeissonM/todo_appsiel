@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Ingreso de control disciplinario</h4>
		    <hr>
			{{ Form::open(array('url'=>'matriculas/control_disciplinario/crear?id='.Input::get('id'))) }}

				<div class="form-group">
					<label class="control-label col-sm-3" > <b> Periodo Lectivo: </b> </label>

					<div class="col-sm-9">
						{{ App\Matriculas\PeriodoLectivo::get_actual()->descripcion }}
					</div>
				</div>

				<br><br>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('semana_id','','Ingresar semana',$semanas,['required'=>'required','class' => 'combobox']) }}
				</div>

				@yield('campos_selects')
								
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-6">
						<br/>
						<button type="submit" class="btn btn-primary" id="btn_continuar">
							<i class="fa fa-btn fa-arrow-right"></i> Continuar
						</button>
						<br/><br/>
					</div>
				</div>
				
				<br/><br/>	
				{{ Form::hidden('id_app',Input::get('id')) }}

			{{ Form::close() }}

		</div>
	</div>
	<br/><br/>	
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){

			$("#semana_id").focus();

			$("#semana_id").on('change',function(){
				$("#btn_continuar").focus();
			});

		});
	</script>
@endsection