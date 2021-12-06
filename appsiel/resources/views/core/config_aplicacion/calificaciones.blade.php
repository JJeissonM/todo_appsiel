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
		    <h4>{{$parametros['titulo']}}</h4>
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

				{{ Form::hidden('titulo', $parametros['titulo']) }}

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$permitir_calificaciones_sin_logros = 'Si';
								if( isset($parametros['permitir_calificaciones_sin_logros'] ) )
								{
									$permitir_calificaciones_sin_logros = $parametros['permitir_calificaciones_sin_logros'];
								}
							?>
							{{ Form::bsSelect('permitir_calificaciones_sin_logros', $permitir_calificaciones_sin_logros, 'Permitir ingreso de calificaciones sin haber ingresado logros', ['Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_preinformes_academicos = 'No';
								if( isset($parametros['manejar_preinformes_academicos'] ) )
								{
									$manejar_preinformes_academicos = $parametros['manejar_preinformes_academicos'];
								}
							?>
							{{ Form::bsSelect('manejar_preinformes_academicos', $manejar_preinformes_academicos, 'Manejar pre-informes académicos', ['No'=>'No','Si'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$colegio_maneja_metas = 'No';
								if( isset($parametros['colegio_maneja_metas'] ) )
								{
									$colegio_maneja_metas = $parametros['colegio_maneja_metas'];
								}
							?>
							{{ Form::bsSelect('colegio_maneja_metas', $colegio_maneja_metas, 'Manejar metas en boletines', ['Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$etiqueta_calificacion_boletines = 'numero_y_letras';
								if( isset($parametros['etiqueta_calificacion_boletines'] ) )
								{
									$etiqueta_calificacion_boletines = $parametros['etiqueta_calificacion_boletines'];
								}
							?>
							{{ Form::bsSelect('etiqueta_calificacion_boletines', $etiqueta_calificacion_boletines, 'Calificación a mostrar en boletines', ['numero_y_letras'=>'Número y letras','solo_numeros'=>'Solo números','solo_letras'=>'Solo letras'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_peso_asignaturas_boletines = 0;
								if( isset($parametros['mostrar_peso_asignaturas_boletines'] ) )
								{
									$mostrar_peso_asignaturas_boletines = $parametros['mostrar_peso_asignaturas_boletines'];
								}
							?>
							{{ Form::bsSelect('mostrar_peso_asignaturas_boletines', $mostrar_peso_asignaturas_boletines, 'Mostrar peso de asignaturas en boletines', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cantidad_decimales_mostrar_calificaciones = 2;
								if( isset($parametros['cantidad_decimales_mostrar_calificaciones'] ) )
								{
									$cantidad_decimales_mostrar_calificaciones = $parametros['cantidad_decimales_mostrar_calificaciones'];
								}
							?>
							{{ Form::bsSelect('cantidad_decimales_mostrar_calificaciones', $cantidad_decimales_mostrar_calificaciones, 'Cantidad de decimales para mostrar calificaciones', [0,1,2,3], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$permitir_imprimir_boletin_a_estudiantes = 'Si';
								if( isset($parametros['permitir_imprimir_boletin_a_estudiantes'] ) )
								{
									$permitir_imprimir_boletin_a_estudiantes = $parametros['permitir_imprimir_boletin_a_estudiantes'];
								}
							?>
							{{ Form::bsSelect('permitir_imprimir_boletin_a_estudiantes', $permitir_imprimir_boletin_a_estudiantes, 'Permitir imprimir boletin a estudiantes', ['Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$formato_boletin_default = 'pdf_boletines_3';
								if( isset($parametros['formato_boletin_default'] ) )
								{
									$formato_boletin_default = $parametros['formato_boletin_default'];
								}

								$formatos_boletines = [
                        						'pdf_boletines_1' => 'Formato # 1 (estándar)',
						                        'pdf_boletines_2' => 'Formato # 2 (moderno)',
						                        'pdf_boletines_3' => 'Formato # 3 (visual)',
						                        'pdf_boletines_4' => 'Formato # 4 (metas)'
						                    ];

							?>
							{{ Form::bsSelect('formato_boletin_default', $formato_boletin_default, 'Formato de boletín por defecto', $formatos_boletines, ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$estudiante_revisar_guia_academicas = 'No';
								if( isset($parametros['estudiante_revisar_guia_academicas'] ) )
								{
									$estudiante_revisar_guia_academicas = $parametros['estudiante_revisar_guia_academicas'];
								}
							?>
							{{ Form::bsSelect('estudiante_revisar_guia_academicas', $estudiante_revisar_guia_academicas, 'Estudiante puede revisar guías académicas', ['No'=>'No','Si'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$estudiante_activar_foros_discucion = 'No';
								if( isset($parametros['estudiante_activar_foros_discucion'] ) )
								{
									$estudiante_activar_foros_discucion = $parametros['estudiante_activar_foros_discucion'];
								}
							?>
							{{ Form::bsSelect('estudiante_activar_foros_discucion', $estudiante_activar_foros_discucion, 'Estudiante puede participar en foros', ['No'=>'No','Si'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<h4> Académico Estudiantes  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$activar_aula_virtual = 1;
								if( isset($parametros['activar_aula_virtual'] ) )
								{
									$activar_aula_virtual = $parametros['activar_aula_virtual'];
								}
							?>
							{{ Form::bsSelect('activar_aula_virtual', $activar_aula_virtual, 'Activar Aula Virtual', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$url_correo_institucional = 'https://gmail.com'; 
								if( isset($parametros['url_correo_institucional'] ) )
								{
									$url_correo_institucional = $parametros['url_correo_institucional'];
								}
							?>
							{{ Form::bsText('url_correo_institucional', $url_correo_institucional, 'URL Correo institucional', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$periodos_activos_visualizar_calificaciones = '';
								if( isset($parametros['periodos_activos_visualizar_calificaciones'] ) )
								{
									$periodos_activos_visualizar_calificaciones = $parametros['periodos_activos_visualizar_calificaciones'];
								}

								$valores = '';
								if ( is_array($periodos_activos_visualizar_calificaciones) ) 
								{
									foreach( $periodos_activos_visualizar_calificaciones AS $key => $value )
									{
										global $valores;

									    $valores .= $value . ',';
									}
								}

								$opciones = App\Calificaciones\Periodo::get_activos_periodo_lectivo();

						        $vec[''] = '';
						        foreach ($opciones as $opcion)
						        {
						            $vec[$opcion->id] = $opcion->periodo_lectivo_descripcion . ' > ' . $opcion->descripcion;
						        }

						        $periodos = $vec;

							?>
							{{ Form::bsCheckBox('periodos_activos_visualizar_calificaciones', $valores, 'Periodos activos para visualizar calificaciones', $periodos, ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<br><br>
				<h4> Certificado de Notas  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$detallar_curso_grado = 'grado';
								if( isset($parametros['detallar_curso_grado'] ) )
								{
									$detallar_curso_grado = $parametros['detallar_curso_grado'];
								}
							?>
							{{ Form::bsSelect('detallar_curso_grado', $detallar_curso_grado, 'Detallar etiqueta del...', [ 'grado' => 'Grado', 'curso' => 'Curso'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
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