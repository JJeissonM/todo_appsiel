@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Ingreso de calificaciones</h4>
		    <hr>
			{{ Form::open(array('url'=>'calificaciones/calificar2?id='.Input::get('id'))) }}

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('id_periodo','','Seleccionar periodo',$periodos,['required'=>'required']) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsText('curso_id_no',$curso->descripcion,'Curso',['disabled'=>'disabled']) }}

					{{ Form::hidden('curso_id',$curso->id) }}
				</div>

				<div class="row" style="padding:5px;">
					{{Form::bsText('id_asignatura_no', $asignatura->descripcion, 'Asignatura', ['disabled'=>'disabled'])}}
					{{ Form::hidden('id_asignatura',$asignatura->id) }}
				</div>
								
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-6">
						<br/>
						<input type="submit" class="btn btn-primary" id="btn_continuar" value="Continuar" />
							<i class="fa fa-btn fa-arrow-right"></i> 
						
						<br/><br/>
					</div>
				</div>
				
				<br/><br/>	
				{{ Form::hidden('id_app',Input::get('id')) }}

				{{ Form::hidden('ruta','academico_docente/calificar/'.$curso->id.'/'.$asignatura->id.'/'.rand(0,1000).'?id='.Input::get('id') ) }}

			{{ Form::close() }}

		</div>
	</div>
	<br/><br/>	
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){

			$("#id_periodo").focus();

			$("#id_periodo").on('change',function(){
				$("#btn_continuar").focus();
			});

		});
	</script>
@endsection