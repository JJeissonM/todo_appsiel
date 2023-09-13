@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
		    <h4 style="color: gray;">Modificación del registro</h4>
		    <hr>
			{{Form::model($registro,['route'=>['academico_docente.asistencia_clases.update',$registro->id],'method'=>'PUT', 'id' => 'form_create','class'=>'form-horizontal' ]) }}

					{{ Form::hidden('url_id', Input::get('id')) }}
					{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}

					{{ Form::hidden('asignatura_ori_id', $asignatura->id) }}
					{{ Form::hidden('curso_id',$curso->id) }}

					<div class="row" style="padding:5px;">
						<h4>
							<b>Curso: </b> {{ $curso->descripcion }}
							<br/>
							<b>Asignatura: </b> {{ $asignatura->descripcion }}
						</h4>
				    </div>					

					<div class="row" style="padding:5px;">
						{{ Form::bsFecha('fecha',null,'Fecha',[],[]) }}
				    </div>

				    <div class="row" style="padding:5px;">

				    	{{ Form::label('asistio', '¿Asistió?', ['class' => 'col-md-3']) }}
				    	<div class="col-md-9">
							<div class="radio-inline">
							  <label>
							  	{{ Form::radio('asistio', 'Si') }} Si
							  </label>
							</div>
							<div class="radio-inline">
							  <label>
							  	{{ Form::radio('asistio', 'No') }} No
							  </label>
							</div>
						</div>
					</div>

					<div class="row" style="padding:5px;">
				    	{{ Form::bsText('anotacion',null,'Anotación',[]) }}
				    </div>

				    {{ Form::bsButtonsForm( url()->previous() ) }}

				{{Form::close()}}
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});

		});
	</script>
@endsection