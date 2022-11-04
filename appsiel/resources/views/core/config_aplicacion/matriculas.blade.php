@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		#div_cargando{
			display: none;/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom:0px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index:999;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>{!!$parametros['titulo']!!}</h4>
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

				{{ Form::hidden('titulo', $parametros['titulo']) }}

				<br>
				<h4> Parámetros para el observador  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$formato_impresion_observador = 1;
								if( isset($parametros['formato_impresion_observador'] ) )
								{
									$formato_impresion_observador = $parametros['formato_impresion_observador'];
								}
							?>
							{{ Form::bsSelect('formato_impresion_observador', $formato_impresion_observador, 'Formato impresión del Observador', [ 1 => 'Estándar', 2 => 'Tipo Historial' ], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$convenciones_valoraciones_aspectos = 'S= Siempre      CS= Casi siempre      AV= Algunas veces      N= Nunca';
								if( isset($parametros['convenciones_valoraciones_aspectos'] ) )
								{
									$convenciones_valoraciones_aspectos = $parametros['convenciones_valoraciones_aspectos'];
								}
							?>
							{{ Form::bsSelect('convenciones_valoraciones_aspectos', $convenciones_valoraciones_aspectos, 'Convenciones valoraciones de aspectos', [ 1 => 'S= Siempre      CS= Casi siempre      AV= Algunas veces      N= Nunca', 2 => 'Db= Desempeño bajo      DB= Desempeño Básico      DA= Desempeño Alto      DS= Desempeño Superior' ], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_control_disciplinario = 0;
								if( isset($parametros['manejar_control_disciplinario'] ) )
								{
									$manejar_control_disciplinario = $parametros['manejar_control_disciplinario'];
								}
							?>
							{{ Form::bsSelect('manejar_control_disciplinario', $manejar_control_disciplinario, 'Manejar control disciplinario', [ 'No', 'Si' ], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_matriz_dofa = 1;
								if( isset($parametros['manejar_matriz_dofa'] ) )
								{
									$manejar_matriz_dofa = $parametros['manejar_matriz_dofa'];
								}
							?>
							{{ Form::bsSelect('manejar_matriz_dofa', $manejar_matriz_dofa, 'Manejar matriz DOFA', [ 'No', 'Si' ], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Parámetros de facturas de estudiantes  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$inv_producto_id_default_matricula = 24;
								if( isset($parametros['inv_producto_id_default_matricula'] ) )
								{
									$inv_producto_id_default_matricula = $parametros['inv_producto_id_default_matricula'];
								}
							?>
							{{ Form::bsSelect('inv_producto_id_default_matricula', $inv_producto_id_default_matricula, 'Concepto por defecto para Matrícula', App\Inventarios\Servicio::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$inv_producto_id_default_pension = 25; 
								if( isset($parametros['inv_producto_id_default_pension'] ) )
								{
									$inv_producto_id_default_pension = $parametros['inv_producto_id_default_pension'];
								}
							?>
							{{ Form::bsSelect('inv_producto_id_default_pension', $inv_producto_id_default_pension, 'Concepto por defecto para Pensión', App\Inventarios\Servicio::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$transaccion_id_factura_estudiante = 23;
								if( isset($parametros['transaccion_id_factura_estudiante'] ) )
								{
									$transaccion_id_factura_estudiante = $parametros['transaccion_id_factura_estudiante'];
								}
							?>
							{{ Form::bsSelect('transaccion_id_factura_estudiante', $transaccion_id_factura_estudiante, 'Transacción de facturación por defecto', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$modelo_id_factura_estudiante = 234; 
								if( isset($parametros['modelo_id_factura_estudiante'] ) )
								{
									$modelo_id_factura_estudiante = $parametros['modelo_id_factura_estudiante'];
								}
							?>
							{{ Form::bsSelect('modelo_id_factura_estudiante', $modelo_id_factura_estudiante, 'Modelo de factura por defecto', App\Sistema\Modelo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<br>
				<h4> Parámetros de tesorería  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cantidad_facturas_vencidas_permitidas = 2;
								if( isset($parametros['cantidad_facturas_vencidas_permitidas'] ) )
								{
									$cantidad_facturas_vencidas_permitidas = $parametros['cantidad_facturas_vencidas_permitidas'];
								}
							?>
							{{ Form::bsText('cantidad_facturas_vencidas_permitidas', $cantidad_facturas_vencidas_permitidas, 'Facturas vencidas permitidas para permitir acceso a estudiantes', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<br>
				<h4> Miscelanea  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_password_en_ficha_matricula = 0;
								if( isset($parametros['mostrar_password_en_ficha_matricula'] ) )
								{
									$mostrar_password_en_ficha_matricula = $parametros['mostrar_password_en_ficha_matricula'];
								}
							?>
							{{ Form::bsSelect('mostrar_password_en_ficha_matricula', $mostrar_password_en_ficha_matricula, 'Mostrar contraseña del estudiante en la Ficha de matrícula', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$largo_consecutivo_codigo_matricula = 3;
								if( isset($parametros['largo_consecutivo_codigo_matricula'] ) )
								{
									$largo_consecutivo_codigo_matricula = $parametros['largo_consecutivo_codigo_matricula'];
								}
							?>
							{{ Form::bsText('largo_consecutivo_codigo_matricula', $largo_consecutivo_codigo_matricula, 'Longitud del consecutivo para códigos de mátriculas', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$banner_reportes = 'renderizado';
								if( isset($parametros['banner_reportes'] ) )
								{
									$banner_reportes = $parametros['banner_reportes'];
								}
							?>
							{{ Form::bsSelect('banner_reportes', $banner_reportes, 'Tipo de Banner para reportes', ['renderizado'=>'Renderizado (View)','imagen' => 'Imágen fija'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$url_imagen_banner = '';
								if( isset($parametros['url_imagen_banner'] ) )
								{
									$url_imagen_banner = $parametros['url_imagen_banner'];
								}
							?>
							{{ Form::bsText('url_imagen_banner', $url_imagen_banner, 'URL de la imágen para el banner de reportes', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>
				
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$etiqueta_datos_aspirante = 'DATOS DEL ASPIRANTE';
								if( isset($parametros['etiqueta_datos_aspirante'] ) )
								{
									$etiqueta_datos_aspirante = $parametros['etiqueta_datos_aspirante'];
								}
							?>
							{{ Form::bsText('etiqueta_datos_aspirante', $etiqueta_datos_aspirante, 'Etiqueta datos aspirantes en inscripciones en línea', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mensaje_inscripcion_creada = 'GRACIAS. Su inscripción ha sido registrada correctamente. Pronto no comunicaremos con usted';
								if( isset($parametros['mensaje_inscripcion_creada'] ) )
								{
									$mensaje_inscripcion_creada = $parametros['mensaje_inscripcion_creada'];
								}
							?>
							{{ Form::bsTextArea('mensaje_inscripcion_creada', $mensaje_inscripcion_creada, 'Mensaje registro existoso de inscripciones en línea', ['class'=>'form-control']) }}

							
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