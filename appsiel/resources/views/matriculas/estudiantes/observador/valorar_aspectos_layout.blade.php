@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Actualización - Curso {{ $matricula_a_mostrar->curso->descripcion }}</h4>
		    <hr>

            @if ( Input::get('id') == 5 ) 
                {{Form::open(['url' => 'academico_docente/guardar_valoracion_aspectos','method'=>'post', 'id' => 'form_create', 'class'=>'form-horizontal']) }}
				<?php 
					$url_cancelar = url('academico_docente/revisar_estudiantes/curso_id/'.Input::get('curso_id').'/id_asignatura/'.Input::get('asignatura_id').'?id='.Input::get('id'));
				?>
            @else
                {{Form::open( ['url' => '/matriculas/estudiantes/observador/valorar_aspectos','method'=>'post', 'class'=>'form-horizontal', 'id' => 'form_create'] ) }}
				<?php				
					$url_cancelar = url( 'matriculas/estudiantes/observador/show/' . $estudiante->id . '?id=1&id_modelo=180&id_transaccion=&matricula_id=' . $matricula_a_mostrar->id );
				?>
            @endif			

				<div class="form-group">
					<div class="alert alert-success">
					  <strong>Convenciones!</strong> <br/>
					  @include('academico_docente.estudiantes.lbl_convenciones_valorar_aspectos_observador')
					  
					</div>
				</div>

				{{ Form::bsTextArea('observacion_general', $observacion_general, 'Observación general', []) }}
				
				{{ Form::hidden('valoraciones_linea_aspecto', null, ['id' => 'valoraciones_linea_aspecto'])}}

				{{ Form::hidden('numero_del_periodo', null, ['id' => 'numero_del_periodo'])}}
				
				{{ Form::hidden('id_estudiante', $estudiante->id, ['id' => 'id_estudiante']) }}
				
				@if ( $matricula_a_mostrar != null )
					{{ Form::hidden('matricula_id', $matricula_a_mostrar->id, ['id' => 'matricula_id']) }}
				@else
					{{ Form::hidden('matricula_id', null, ['id' => 'matricula_id']) }}
				@endif
	
				{{ Form::hidden('fecha_valoracion', date( $anio_matricula . '-' . 'm-d') ) }}

				{{ Form::hidden('url_id', Input::get('id'), ['id' => 'url_id'])}}
				{{ Form::hidden('curso_id', Input::get('curso_id'), ['id' => 'curso_id'])}}
				{{ Form::hidden('asignatura_id', Input::get('asignatura_id'), ['id' => 'asignatura_id'])}}

			{{Form::close()}}

			@include('matriculas.estudiantes.observador.valorar_aspectos_tabla_formulario')
			
			<br/>

			<div class="form-group">
				<a href="#" class="btn btn-primary btn-xs" id="bs_boton_guardar"><i class="fa fa-save"></i> Guardar</a>
				<a href="{{ $url_cancelar }}" class="btn btn-danger btn-xs" id="bs_boton_cancelar">Cancelar</a>
			</div>
			
			<div class="container" style="clear: both; width: auto; display: none;" id="mensaje_ok">
				<div class="alert alert-success alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<em><i class="fa fa-check" aria-hidden="true"> </i> Valoraciones almacenadas correctamente.</em>
				</div>
			</div>

		</div>
	</div>	
@endsection

@section('scripts')
	<script type="text/javascript">

		var url_raiz = '{{url('/')}}';
		$(document).ready(function(){

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				$(this).children('.fa-save').attr('class','fa fa-spinner fa-spin');

				$('#valoraciones_linea_aspecto').val( build_json() );
				
				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
						
				//$(this).children('.fa-spinner').attr('class','fa fa-save');
							
				//$("#mensaje_ok").fadeIn(1000);
			});

			
			function build_json() { 
				
				var string_json = '{';
				var primero = true;
				var contador = 1;
				$('.linea_aspecto').each(function () {

					var fila = $(this).closest("tr");

					if ( primero ) {
						
						string_json = string_json + '"' + contador + '":{"aspecto_estudiante_id":"' + fila.find('.aspecto_estudiante_id').val() + '"';
						
						string_json = string_json + ', ' + '"id_aspecto":"' + fila.find('.id_aspecto').val() + '"';
						
						string_json = string_json + ', ' + '"valoracion_periodo1":"' + fila.find('.valoracion_periodo1').val() + '"';
						
						string_json = string_json + ', ' + '"valoracion_periodo2":"' + fila.find('.valoracion_periodo2').val() + '"';
						
						string_json = string_json + ', ' + '"valoracion_periodo3":"' + fila.find('.valoracion_periodo3').val() + '"';
						
						string_json = string_json + ', ' + '"valoracion_periodo4":"' + fila.find('.valoracion_periodo4').val() + '"}';

						primero = false;
						contador++;
					}else{
						string_json = string_json + ', ' +  '"' + contador + '":{"aspecto_estudiante_id":"' + fila.find('.aspecto_estudiante_id').val() + '"';

						string_json = string_json + ', ' + '"id_aspecto":"' + fila.find('.id_aspecto').val() + '"';

						string_json = string_json + ', ' + '"valoracion_periodo1":"' + fila.find('.valoracion_periodo1').val() + '"';

						string_json = string_json + ', ' + '"valoracion_periodo2":"' + fila.find('.valoracion_periodo2').val() + '"';

						string_json = string_json + ', ' + '"valoracion_periodo3":"' + fila.find('.valoracion_periodo3').val() + '"';

						string_json = string_json + ', ' + '"valoracion_periodo4":"' + fila.find('.valoracion_periodo4').val() + '"}';
						
						contador++;
					}

				});
				
				string_json = string_json + '}';

				return string_json;
			}
			
			$('.valoracion_periodo1').on('keyup',function(event){
				$("#mensaje_ok").hide();
			});
			$('.valoracion_periodo2').on('keyup',function(event){
				$("#mensaje_ok").hide();
			});
			$('.valoracion_periodo3').on('keyup',function(event){
				$("#mensaje_ok").hide();
			});
			$('.valoracion_periodo4').on('keyup',function(event){
				$("#mensaje_ok").hide();
			});
		});
	</script>
@endsection