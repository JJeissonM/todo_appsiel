@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<style>
		.academico-actividad {
			font-family: 'Poppins', 'Segoe UI', 'Lato', sans-serif;
			background: #f4f6fb;
			min-height: 100vh;
			padding: 40px 0 80px;
		}

		.academico-actividad .marco_formulario {
			background: #fff;
			border-radius: 1.7rem;
			padding: 36px;
			box-shadow: 0 30px 70px rgba(15, 32, 92, 0.15);
			border: none;
		}

		.academico-actividad .panel {
			border-radius: 1.1rem;
			box-shadow: 0 20px 50px rgba(15, 32, 92, 0.08);
			border: none;
			margin-bottom: 1.8rem;
		}

		.academico-actividad .panel-heading {
			border: none;
			border-radius: 1rem 1rem 0 0;
			padding: 1.2rem 1.6rem;
		}

		.academico-actividad .panel-primary .panel-heading {
			background: linear-gradient(135deg, #1f3b88, #5e60ff);
			color: #fff;
			font-weight: 600;
			letter-spacing: 0.05em;
		}

		.academico-actividad .panel-info .panel-heading {
			background: linear-gradient(135deg, #7c3aed, #a855f7);
			color: #fff;
			font-weight: 600;
			letter-spacing: 0.05em;
		}

		.academico-actividad .panel-default .panel-heading {
			background: #f8f9ff;
			border-bottom: 1px solid #e1e5f0;
			color: #2f3345;
		}

		.academico-actividad .panel-body {
			background: #fff;
			color: #2f3345;
			padding: 1.6rem 2rem;
		}

		.academico-actividad .panel .panel-body {
			padding: 1.4rem 1.8rem;
		}

		.academico-actividad h4,
		.academico-actividad h5 {
			font-weight: 600;
			color: #1f2b44;
		}

		.academico-actividad .text-muted {
			color: #7f8ca9 !important;
		}

		.academico-actividad .label {
			font-weight: 600;
			letter-spacing: 0.08em;
		}

		.academico-actividad .panel-body .btn {
			padding: 0.6rem 1.4rem;
			border-radius: 0.6rem;
			font-weight: 600;
		}

		.academico-actividad .panel-default .panel-body {
			border-radius: 0 0 1rem 1rem;
		}

		#ingreso_registros th,
		#ingreso_registros td {
			border-color: #edf2f7;
		}

		#ingreso_registros thead tr {
			background: #f6f7fb;
			font-size: 0.9rem;
			text-transform: uppercase;
			letter-spacing: 0.08em;
		}

		.question-block {
			border: 1px solid #edf2f7;
			border-radius: 1rem;
			margin-bottom: 1rem;
		}

		.question-block .panel-heading {
			background: #f6f7fb;
			font-weight: 600;
			font-size: 1rem;
			color: #1f2b44;
		}

		.question-block .panel-body {
			background: #fff;
		}

		#div_ingresar_respuesta {
			border-radius: 1.2rem;
			border: 1px solid #e1e5f0;
			padding: 1.6rem;
			background: #fff;
		}

		.exam-details {
			margin-top: 1.5rem;
			display: flex;
			flex-direction: column;
			gap: 1.5rem;
		}

		@media (max-width: 768px) {
			.academico-actividad .panel-body {
				padding: 1.2rem;
			}

			.academico-actividad .marco_formulario {
				padding: 24px;
			}

			.academico-actividad .panel-heading {
				padding: 1rem 1.2rem;
			}
		}
	</style>

	<div class="container-fluid academico-actividad">
		<div class="marco_formulario">

			@if( $actividad->id == 0)
				<?php
					dd( 'La actividad ha sido eliminada o está inactiva. Por favor, consulte con el administrador. ID actividad = ' . $actividad_id );
				?>
			@endif

			@php
				$fecha_entrega_vencida = date('Y-m-d') > $actividad->fecha_entrega;
				$tipoIcfesLabel = $cuestionario->tipo_icfes_label ?? '';
				$color = $actividad->estado === 'Activo' ? '#16a085' : '#d35400';
				$mostrar = '';
				switch ($actividad->tipo_recurso) {
					case 'Imagen':
						$mostrar = <<<HTML
<p><b>Recurso:</b> Imagen</p>
<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<img width="100%" height="400px" src="{$actividad->url_recurso}" alt="Recurso multimedia">
	</div>
</div>
HTML;
						break;
					case 'Video':
						$embedUrl = str_replace('watch?v=','embed/',explode('&', $actividad->url_recurso)[0]);
						$mostrar = <<<HTML
<p><b>Recurso:</b> Video</p>
<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<iframe width="100%" height="350" src="{$embedUrl}" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
	</div>
</div>
HTML;
						break;
					case 'Adjunto':
						$archivoUrl = config('configuracion.url_instancia_cliente') . "/storage/app/{$modelo->ruta_storage_archivo_adjunto}{$actividad->archivo_adjunto}";
						$mostrar = <<<HTML
<p><b>Recurso:</b> Archivo adjunto</p>
<div class="row">
	<div class="col-md-10 col-md-offset-1">
		<a href="{$archivoUrl}" class="btn btn-outline-primary btn-md" target="_blank"><i class="fa fa-file"></i> Ver archivo</a>
	</div>
</div>
HTML;
						break;
				}
			@endphp

			@php $user = Auth::user(); @endphp

			<article class="exam-hero">
				<div>
					<p class="hero-label">Actividad académica</p>
					<h1 class="hero-title">{{ $actividad->descripcion }}</h1>
					<p class="hero-subtitle">
						Asignatura: {{ $asignatura->descripcion }} · Temática: {{ $actividad->tematica }}
					</p>
				</div>
				<div class="hero-meta">
					<p class="hero-date">Fecha de entrega: {{ $actividad->fecha_entrega }}</p>
					<div class="hero-badges">
						<span class="exam-badge {{ $fecha_entrega_vencida ? 'exam-badge--danger' : 'exam-badge--success' }}">
							{{ $fecha_entrega_vencida ? 'Plazo vencido' : 'A tiempo' }}
						</span>
						<span class="exam-badge exam-badge--secondary">
							{{ $tipoIcfesLabel ?: 'Cuestionario general' }}
						</span>
					</div>
					@if( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
						<div class="hero-actions">
							<a href="{{ route('cuestionarios.revision', ['id' => Input::get('id'), 'id_modelo' => Input::get('id_modelo')]) }}" class="btn btn-outline-light btn-sm font-weight-bold">
								Ver todos los cuestionarios
							</a>
						</div>
					@endif
				</div>
			</article>

			<div class="exam-details">
				<section class="exam-card">
					<div class="exam-card-header">
						<h4>Instrucciones</h4>
					</div>
					<div class="exam-card-body">
						{!! $actividad->instrucciones !!}
					</div>
				</section>

				@if( $mostrar != '' )
					<section class="exam-card">
						<div class="exam-card-header">
							<h4>Recurso complementario</h4>
						</div>
						<div class="exam-card-body">
							{!! $mostrar !!}
						</div>
					</section>
				@endif

				<section class="exam-card exam-card--summary">
					<div class="exam-card-header">
						<h4>Resumen rápido</h4>
					</div>
					<div class="exam-card-body">
						<p><strong>Estado:</strong> <i class="fa fa-circle" style="color: {{ $color }}"></i> {{ $actividad->estado }}</p>
						<p><strong>Fecha límite:</strong> {{ $actividad->fecha_entrega }}</p>
						<p><strong>Asignatura:</strong> {{ $asignatura->descripcion }}</p>
						<p><strong>Temática:</strong> {{ $actividad->tematica }}</p>
						<p><strong>Recurso:</strong> {{ $actividad->tipo_recurso ?: 'N/A' }}</p>
					</div>
				</section>

				@if( isset($cuestionario->id) )
					<section class="exam-card exam-card--interactive">
						<div class="exam-card-header">
							<div>
								<h4 class="mb-0">Cuestionario</h4>
								@if( $cuestionario->detalle )
									<p class="text-muted mb-0">{!! $cuestionario->detalle !!}</p>
								@endif
							</div>
							<span class="exam-badge exam-badge--info">
								{{ $cuestionario->activar_resultados || $fecha_entrega_vencida ? 'Resultados publicados' : 'Responde y guarda para avanzar' }}
							</span>
						</div>
						<div class="exam-card-body">
							@if( $cuestionario->activar_resultados || $fecha_entrega_vencida )
								@include('calificaciones.actividades_escolares.resultados_cuestionario')
							@else
								@include('calificaciones.actividades_escolares.preguntas_respuestas')
							@endif
						</div>
						<input type="hidden" name="cuestionario_id" id="cuestionario_id" value="{{ $cuestionario->id }}">
					</section>
				@else
					<section class="exam-card">
						<div class="exam-card-header">
							<h4 class="mb-0">Actividad sin cuestionario</h4>
						</div>
						<div class="exam-card-body">
							<div class="container-fluid" id="div_ingresar_respuesta">
								<div class="row" style="font-size: 15px;">
									<div class="col-md-12">
										{{ Form::open( [ 'url' => 'sin_cuestionario_guardar_respuesta?id='.Input::get('id'), 'id' => 'form_create', 'files' => true]) }}

											<h4> A continuación ingrese sus respuestas o anotaciones: </h4>

											{{ Form::hidden('estudiante_id', $estudiante->id ) }}
											{{ Form::hidden('actividad_id', $actividad->id ) }}
											{{ Form::hidden('respuesta_id', $respuesta->id, ['id' => 'respuesta_id'] ) }}

											@if( $respuesta->calificacion == '' && !$fecha_entrega_vencida )
												@include('calificaciones.actividades_escolares.hacer_actividad_ingresar_respuesta')
											@else
												@include('calificaciones.actividades_escolares.hacer_actividad_revisar_respuesta')
											@endif

											@if( $respuesta->updated_at != '')
												<br>
												<u>Fecha envío</u>
												<br>
												@php
													$fecha = explode(" ", $respuesta->updated_at);
												@endphp
												Fecha: {{ $fecha[0] }}
												<br>
												Hora: {{ $fecha[1] }}
											@endif

										{{ Form::close() }}
									</div>
								</div>
							</div>
						</div>
					</section>
				@endif
			</div>

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
		                const preguntaDescripcion = $(this).data('descripcion') || $(this).closest('.panel-info').find('.pregunta_descripcion').text().trim();
	                if ( !numero_pregunta ) {
                    const heading = $(this).closest('.panel-info').find('.panel-heading strong').first().text();
                    numero_pregunta = heading.indexOf('.') > -1 ? heading.split('.')[0].trim() : heading.trim();
                }

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
				$('#ingreso_registros').find('tbody').append('<tr> <td style="display:none;">' + numero_pregunta + '</td> <td style="display:none;">' + pregunta_id + '</td> <td style="display:none;">' + respuesta + '</td> <td>' + preguntaDescripcion +'</td> <td>' + respuesta_txt +'</td><td>'+btn_borrar+'</td></tr>');
				
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


			CKEDITOR.replace('respuesta_enviada_2', {
			    height: 200,
			      // By default, some basic text styles buttons are removed in the Standard preset.
			      // The code below resets the default config.removeButtons setting.
			      removeButtons: ''
		    });


		    // Validar tamaño archivos
			$(document).on('change', '#adjunto', function(e) {
				var file = e.target.files[0];
				if ( file.size < 21060000 ) {
					//addImage(e);
				}else{
					alert('El tamaño (peso) del archivo supera el maximo permitido.');
					$(this).val('');
					$(this).focus();
				}
			});

		     function addImage(e){

		      var file = e.target.files[0];
		  
		      var reader = new FileReader();
		      reader.onload = fileOnload;
		      reader.readAsDataURL(file);
		     }
		  
		     function fileOnload(e) {
		      var result=e.target.result;
		      $('#imgSalida_'+cant_imagenes).attr("src",result);
		     }

		});

	</script>
@endsection



