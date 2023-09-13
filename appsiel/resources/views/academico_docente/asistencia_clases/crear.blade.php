@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Tomar asistencia</h4>
		    <hr>

		    {{Form::open(['url'=>'/academico_docente/asistencia_clases/continuar_creacion?id='.Input::get('id') . '&id_modelo=181'],['class'=>'form-horizontal']) }}

				<div class="row" style="padding:5px;">
					{{ Form::bsFecha('fecha',date('Y-m-d'),'Fecha',[],[]) }}
			    </div>
					
				<div class="row" style="padding:5px;">
					{{ Form::bsText( 'curso_id_no', $curso->descripcion, 'Curso', ['disabled' => 'disabled'] ) }}

					{{ Form::hidden('curso_id',$curso->id) }}
				</div>

				<div class="row" style="padding:5px;">
					{{Form::bsText( 'id_asignatura_no', $asignatura->descripcion, 'Asignatura', ['disabled' => 'disabled'] ) }}
					{{ Form::hidden('asignatura_id',$asignatura->id) }}
				</div>

			    <br/>
			    <button class="btn btn-success" id="btn_continuar"> <i class="fa fa-btn fa-forward"></i> Continuar</button>

			{{Form::close()}}
		</div>
	</div>
	<br/><br/>	
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){

			$("#btn_continuar").focus();

		});
	</script>
@endsection