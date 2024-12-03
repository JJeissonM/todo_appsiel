@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    {!! $parametros['titulo'] !!}
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

		    	<!--
					// NOTA: La variable que no sea enviada en el request (a través de un input) será borrada del archivo de configuración
        			// Si se quiere agregar una nueva variable al archivo de configuración, hay que agregar también un campo nuevo a este formulario
		    	-->

				{{ Form::hidden('titulo', $parametros['titulo'] ) }}

				
				<h4> Parámetros generales </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_intensidad_horaria = '1';
								if( isset($parametros['mostrar_intensidad_horaria'] ) )
								{
									$mostrar_intensidad_horaria = $parametros['mostrar_intensidad_horaria'];
								}
							?>
							{{ Form::bsSelect('mostrar_intensidad_horaria', $mostrar_intensidad_horaria, 'Mostrar columna Intensidad Horaria', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$mostrar_numero_identificacion_estudiante = '1';
								if( isset($parametros['mostrar_numero_identificacion_estudiante'] ) )
								{
									$mostrar_numero_identificacion_estudiante = $parametros['mostrar_numero_identificacion_estudiante'];
								}
							?>
							{{ Form::bsSelect('mostrar_numero_identificacion_estudiante', $mostrar_numero_identificacion_estudiante, 'Mostrar Número de indetificación del estudiante', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_imagen_firma_autorizada_1 = '1';
								if( isset($parametros['mostrar_imagen_firma_autorizada_1'] ) )
								{
									$mostrar_imagen_firma_autorizada_1 = $parametros['mostrar_imagen_firma_autorizada_1'];
								}
							?>
							{{ Form::bsSelect('mostrar_imagen_firma_autorizada_1', $mostrar_imagen_firma_autorizada_1, 'Mostrar Firma Autorizada #1', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_imagen_firma_autorizada_2 = '1';
								if( isset($parametros['mostrar_imagen_firma_autorizada_2'] ) )
								{
									$mostrar_imagen_firma_autorizada_2 = $parametros['mostrar_imagen_firma_autorizada_2'];
								}
							?>
							{{ Form::bsSelect('mostrar_imagen_firma_autorizada_2', $mostrar_imagen_firma_autorizada_2, 'Mostrar Firma Autorizada #2', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tabla_desing = '1';
								if( isset($parametros['tabla_desing'] ) )
								{
									$tabla_desing = $parametros['tabla_desing'];
								}
							?>
							{{ Form::bsSelect('tabla_desing', $tabla_desing, 'Diseño de la Tabla a Mostrar', [
								'tabla_asignaturas_calificacion_2' => 'Normal con Asignaturas',
								'tabla_areas_calificacion' => 'Por Áreas',
								],
								['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>
				
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$columnas_encabezado = '1';
								if( isset($parametros['columnas_encabezado'] ) )
								{
									$columnas_encabezado = $parametros['columnas_encabezado'];
								}
							?>
							{{ Form::bsSelect('columnas_encabezado', $columnas_encabezado, 'Cantidad de columnas encabezado', [
								'1' => '1',
								'2' => '2',
								'3' => '3',
								],
								['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$url_imagen_izquierda_encabezado = '';
								if( isset($parametros['url_imagen_izquierda_encabezado'] ) )
								{
									$url_imagen_izquierda_encabezado = $parametros['url_imagen_izquierda_encabezado'];
								}
							?>
							{{ Form::bsText('url_imagen_izquierda_encabezado', $url_imagen_izquierda_encabezado, 'Url imágen Izquierda Encabezado', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$url_imagen_derecha_encabezado = '';
								if( isset($parametros['url_imagen_derecha_encabezado'] ) )
								{
									$url_imagen_derecha_encabezado = $parametros['url_imagen_derecha_encabezado'];
								}
							?>
							{{ Form::bsText('url_imagen_derecha_encabezado', $url_imagen_derecha_encabezado, 'Url imágen Derecha Encabezado', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<br>

				<h4> Parámetros para el formato Marca de agua  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$ma_porcentaje_ancho_escudo = 80;
								if( isset($parametros['ma_porcentaje_ancho_escudo'] ) )
								{
									$ma_porcentaje_ancho_escudo = $parametros['ma_porcentaje_ancho_escudo'];
								}
							?>
							{{ Form::bsText('ma_porcentaje_ancho_escudo', $ma_porcentaje_ancho_escudo, '% Ancho escudo de fondo', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$ma_opacidad_escudo = 40;
								if( isset($parametros['ma_opacidad_escudo'] ) )
								{
									$ma_opacidad_escudo = $parametros['ma_opacidad_escudo'];
								}
							?>
							{{ Form::bsText('ma_opacidad_escudo', $ma_opacidad_escudo, '% Opacidad escudo (0 = Invisible)', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="row" style="padding:5px;">
							<?php 
								$ma_encabezado = '';
								if( isset($parametros['ma_encabezado'] ) )
								{
									$ma_encabezado = $parametros['ma_encabezado'];
								}
							?>
							<div class="form-group" style="padding-left: 10px;">
								<label class="control-label" for="ma_encabezado" style="padding-left: 5px;"> Diseño Encabezado: </label>
								<div class="col-sm-12">
									<textarea id="ma_encabezado" rows="20" name="ma_encabezado" cols="150" style="width: 400px; height: 100px;">
										{!! $ma_encabezado !!}
									</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="row" style="padding:5px;">
							<?php 
								$ma_encabezado_2 = '';
								if( isset($parametros['ma_encabezado_2'] ) )
								{
									$ma_encabezado_2 = $parametros['ma_encabezado_2'];
								}
							?>
							<div class="form-group" style="padding-left: 10px;">
								<label class="control-label" for="ma_encabezado_2" style="padding-left: 5px;"> Diseño Encabezado Linea #2: </label>
								<div class="col-sm-12">
									<textarea id="ma_encabezado_2" rows="20" name="ma_encabezado_2" cols="150" style="width: 400px; height: 100px;">
										{!! $ma_encabezado_2 !!}
									</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="row" style="padding:5px;">
							<?php 
								$ma_preambulo = '';
								if( isset($parametros['ma_preambulo'] ) )
								{
									$ma_preambulo = $parametros['ma_preambulo'];
								}
							?>
							<div class="form-group" style="padding-left: 10px;">
								<label class="control-label" for="ma_preambulo" style="padding-left: 5px;"> Texto Preambulo: </label>
								<div class="col-sm-12">
									<textarea id="ma_preambulo" rows="20" name="ma_preambulo" cols="150" style="width: 400px; height: 100px;">
										{!! $ma_preambulo !!}
									</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="row" style="padding:5px;">
							<?php 
								$ma_introduccion = '';
								if( isset($parametros['ma_introduccion'] ) )
								{
									$ma_introduccion = $parametros['ma_introduccion'];
								}
							?>
							<div class="form-group" style="padding-left: 10px;">
								<label class="control-label" for="ma_introduccion" style="padding-left: 5px;"> Texto introducción: </label>
								<div class="col-sm-12">
									<textarea id="ma_introduccion" rows="20" name="ma_introduccion" cols="150" style="width: 400px; height: 100px;">
										{!! $ma_introduccion !!}
									</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="row" style="padding:5px;">
							<?php 
								$ma_contenido_inicial = '';
								if( isset($parametros['ma_contenido_inicial'] ) )
								{
									$ma_contenido_inicial = $parametros['ma_contenido_inicial'];
								}
							?>
							<div class="form-group" style="padding-left: 10px;">
								<label class="control-label" for="ma_contenido_inicial" style="padding-left: 5px;"> Contenido Inicial: </label>
								<div class="col-sm-12">
									<textarea id="ma_contenido_inicial" rows="20" name="ma_contenido_inicial" cols="150" style="width: 400px; height: 100px;">
										{!! $ma_contenido_inicial !!}
									</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="row" style="padding:5px;">
							<?php 
								$ma_contenido_pie_pagina = '';
								if( isset($parametros['ma_contenido_pie_pagina'] ) )
								{
									$ma_contenido_pie_pagina = $parametros['ma_contenido_pie_pagina'];
								}
							?>
							<div class="form-group" style="padding-left: 10px;">
								<label class="control-label" for="ma_contenido_pie_pagina" style="padding-left: 5px;">Contenido para el pie de página: </label>
								<div class="col-sm-12">
									<textarea id="ma_contenido_pie_pagina" rows="20" name="ma_contenido_pie_pagina" cols="150" style="width: 400px; height: 100px;">
										{!! $ma_contenido_pie_pagina !!}
									</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>

				<br><br>

				<div style="width: 100%; text-align: center;">
					<div class="row" style="margin: 5px;"> {{ Form::bsButtonsForm( url()->previous() ) }} </div>

					{{ Form::hidden('url_id',Input::get('id')) }}
					{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
				</div>

			{{ Form::close() }}
		</div>
	</div>
	<br/><br/>

	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){

			CKEDITOR.replace('ma_encabezado', {
				height: 200,
				// By default, some basic text styles buttons are removed in the Standard preset.
				// The code below resets the default config.removeButtons setting.
				removeButtons: ''
			});

			/*CKEDITOR.replace('ma_encabezado_2', {
				height: 200,
				// By default, some basic text styles buttons are removed in the Standard preset.
				// The code below resets the default config.removeButtons setting.
				removeButtons: ''
			});

			CKEDITOR.replace('ma_preambulo', {
				height: 200,
				// By default, some basic text styles buttons are removed in the Standard preset.
				// The code below resets the default config.removeButtons setting.
				removeButtons: ''
			});
			CKEDITOR.replace('ma_introduccion', {
				height: 200,
				// By default, some basic text styles buttons are removed in the Standard preset.
				// The code below resets the default config.removeButtons setting.
				removeButtons: ''
			});
*/

			CKEDITOR.replace('ma_contenido_inicial', {
				height: 200,
				// By default, some basic text styles buttons are removed in the Standard preset.
				// The code below resets the default config.removeButtons setting.
				removeButtons: ''
			});

			CKEDITOR.replace('ma_contenido_pie_pagina', {
				height: 200,
				// By default, some basic text styles buttons are removed in the Standard preset.
				// The code below resets the default config.removeButtons setting.
				removeButtons: ''
			});

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
	
	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection