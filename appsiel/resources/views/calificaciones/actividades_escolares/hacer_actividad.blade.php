@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>{{$actividad->descripcion}}</h4>
		    <hr>
				<h5><b>Asignatura: </b> {{ $asignatura->descripcion }}</h5>
				<h5><b>Temática: </b> {{ $actividad->tematica }}</h5>
				
				<div style="border: solid 1px; border-bottom: solid 2px; border-right: solid 2px; border-radius: 5px; padding: 10px; margin: 10px;">
					<h4><b>Instrucciones: </b> </h4>
					<hr>
					{!! $actividad->instrucciones !!}
				</div>

				<br>
				<p><b>Fecha de entrega: </b> {{$actividad->fecha_entrega}}</p>
				<?php
					$mostrar = '';
					switch ($actividad->tipo_recurso) {
						case 'Imagen':
							$mostrar = '<p><b>Recurso: </b> Imágen</p>
										<div class="row">
											<div class="col-md-10 col-md-offset-1">
												<img width="100%" height="550px" src="'.$actividad->url_recurso.'">
											</div>
										</div>';
							break;
						
						case 'Video':
							$mostrar = '<p><b>Recurso: </b> Video</p>
										<div class="row">
											<div class="col-md-10 col-md-offset-1">
												<iframe width="100%" height="380" src="'.str_replace('watch?v=','embed/',explode('&', $actividad->url_recurso)[0]).'" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
											</div>
										</div>';
							break;
						
						case 'Adjunto':
							$mostrar = '<p><b>Recurso: </b> Archivo adjunto</p>
										<div class="row">
											<div class="col-md-10 col-md-offset-1">
												<a href="'.config('configuracion.url_instancia_cliente')."/storage/app/".$modelo->ruta_storage_archivo_adjunto.$actividad->archivo_adjunto.'" class="btn btn-warning btn-sm" target="_blank"> <i class="fa fa-file"></i> '.$actividad->archivo_adjunto.' </a>
											</div>
										</div>';
							break;
						
						default:
							# code...
							break;
					}

					echo $mostrar;

					
				?>
				
				<br>

				@if( isset($cuestionario->id) )
					<h4><b>Cuestionario</b></h4>
					<hr>
					<p> {!! $cuestionario->detalle !!} </p>
					<div style="border: solid 1px; border-bottom: solid 2px; border-right: solid 2px; border-radius: 5px; padding: 10px;">
						@if( $cuestionario->activar_resultados )
							<br>
							<!-- Mostrar resultados, ya no se puede modificar preguntas -->
							@include('calificaciones.actividades_escolares.resultados_cuestionario')
						@else
							<br>
							<!-- Mostrar lista de preguntas y respuestas enviadas -->
							@include('calificaciones.actividades_escolares.preguntas_respuestas')
						@endif
					</div>
				@else

					<div class="container-fluid" id="div_ingresar_respuesta">
						<div class="row">
							<div class="col-md-12">
								{{ Form::open( [ 'url' => 'sin_cuestionario_guardar_respuesta?id='.Input::get('id'), 'id' => 'form_create', 'files' => true]) }}

									<h4> A continuación Ingrese sus respuestas ó anotaciones: </h4>

									<textarea class="form-control" rows="4" name="respuesta_enviada" id="respuesta_enviada" cols="250" required="required">{{ $respuesta->respuesta_enviada }}</textarea>

									{{ Form::hidden('estudiante_id', $estudiante->id ) }}
									{{ Form::hidden('actividad_id', $actividad->id ) }}
									{{ Form::hidden('respuesta_id', $respuesta->id, ['id' => 'respuesta_id'] ) }}
									<br>

									<div class="form-group">
										<a href="#" class="btn btn-primary btn-xs" id="btn_guardar"> <i class="fa fa-save"></i>&nbsp;Guardar</a>
									</div>

								{{ Form::close() }}
							</div>
						</div>
					</div>						
						
				@endif

		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')
	
	<script type="text/javascript">
		$(document).ready(function(){


			$('.btn_agregar_respuesta').on('click',function(){
                let respuesta, respuesta_txt;
                let pregunta_id = $(this).attr('data-pregunta_id');
                let numero_pregunta = $(this).attr('data-numero_pregunta');
                
                // Solo hay dos tipos de controles para las preguntas
                if( $(this).attr('data-tipo_pregunta') == 'Abierta' )
                {
                	respuesta = $("[name='pregunta_"+pregunta_id+"']").val().replace(/\r?\n/g, " ");
                	respuesta_txt = respuesta;
                }else{
                	// El valor de la respuesta es distinto al valor que ve el usuario
                	respuesta = $("[name='pregunta_"+pregunta_id+"']:checked").val();
                	respuesta_txt = $("[name='pregunta_"+pregunta_id+"']:checked").next('div').text();
                }

                if( respuesta != '' && typeof respuesta !== "undefined" )
                {
                	$('#pregunta_id').val( pregunta_id );
                	$('#respuesta_enviada').val( respuesta );

                	let btn_borrar = "<button type='button' class='btn btn-warning btn-xs btn_eliminar' title='Cambiar respuesta'><i class='glyphicon glyphicon-edit'></i></button>";
					$('#ingreso_registros').find('tbody').append('<tr> <td style="display:none;">' + numero_pregunta + '</td> <td style="display:none;">' + pregunta_id + '</td> <td style="display:none;">' + respuesta + '</td> <td>' + $(this).parent('div').find('.pregunta_descripcion').html() +'</td> <td>' + respuesta_txt +'</td><td>'+btn_borrar+'</td></tr>');
					
					$('#div_'+numero_pregunta).hide();

					asignar_opciones();

                }else{
                	alert('No puede enviar una respuesta vacía.');
                }

           });

			/*
			  * Botón Eliminar
			*/
			$(document).on('click', '.btn_eliminar', function() {
				let fila = $(this).closest("tr");
				
				let numero_pregunta = fila.find('td:first').html();

				$('#div_'+numero_pregunta).show();

				fila.remove();

				asignar_opciones();
			});

			/*
			  * Se va crear una cadena en formato JSON con las respuestas
			*/
			function asignar_opciones() 
			{
				
				let text = '{';

				let primero = true;
				$('#ingreso_registros').find('tbody>tr').each( function(){

					let pregunta_id = $(this).find('td:nth-child(2)').html();
					let respuesta = $(this).find('td:nth-child(3)').html();

					if ( primero ) {
						text = text + '"' + pregunta_id + '":"' + respuesta + '"';
						primero = false;
					}else{
						text = text + ',"' + pregunta_id + '":"' + respuesta + '"';
					}
				});

				text = text + '}';

				$('#respuesta_enviada').val( text );

				if( text == '{ }')
				{
					$('#respuesta_enviada').val( '' );
				}
			}



			$('#btn_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}



				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();

				/*
				$('#div_cargando').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_create');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#respuesta_id').val( respuesta );
					$('#mensaje_ok').show();
				});

				*/



			});

		});

		CKEDITOR.replace('respuesta_enviada', {
		    height: 200,
		      // By default, some basic text styles buttons are removed in the Standard preset.
		      // The code below resets the default config.removeButtons setting.
		      removeButtons: ''
	    });
	</script>
@endsection