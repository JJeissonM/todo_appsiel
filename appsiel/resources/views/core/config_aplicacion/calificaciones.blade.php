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

				
				<h4> Informes y calificaciones  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$permitir_calificaciones_sin_logros = '';
								if( isset($parametros['permitir_calificaciones_sin_logros'] ) )
								{
									$permitir_calificaciones_sin_logros = $parametros['permitir_calificaciones_sin_logros'];
								}
							?>
							{{ Form::bsSelect('permitir_calificaciones_sin_logros', $permitir_calificaciones_sin_logros, 'Permitir ingreso de calificaciones sin haber ingresado logros', [''=>'','Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_preinformes_academicos = '';
								if( isset($parametros['manejar_preinformes_academicos'] ) )
								{
									$manejar_preinformes_academicos = $parametros['manejar_preinformes_academicos'];
								}
							?>
							{{ Form::bsSelect('manejar_preinformes_academicos', $manejar_preinformes_academicos, 'Manejar pre-informes académicos', [''=>'','No'=>'No','Si'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$colegio_maneja_metas = '';
								if( isset($parametros['colegio_maneja_metas'] ) )
								{
									$colegio_maneja_metas = $parametros['colegio_maneja_metas'];
								}
							?>
							{{ Form::bsSelect('colegio_maneja_metas', $colegio_maneja_metas, 'Manejar metas en boletines', [''=>'','Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_peso_estandar_encabezados_calificaciones = '0';
								if( isset($parametros['manejar_peso_estandar_encabezados_calificaciones'] ) )
								{
									$manejar_peso_estandar_encabezados_calificaciones = $parametros['manejar_peso_estandar_encabezados_calificaciones'];
								}
							?>
							{{ Form::bsSelect('manejar_peso_estandar_encabezados_calificaciones', $manejar_peso_estandar_encabezados_calificaciones, 'Manejar PESO estándar en los encabezados de calificaciones (Todas las asignaturas del curso deben tener el mismo PESO en los mismos enabezados)', ['0'=>'No','1'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_encabezados_fijos_en_calificaciones = 'No';
								if( isset($parametros['manejar_encabezados_fijos_en_calificaciones'] ) )
								{
									$manejar_encabezados_fijos_en_calificaciones = $parametros['manejar_encabezados_fijos_en_calificaciones'];
								}
							?>
							{{ Form::bsSelect('manejar_encabezados_fijos_en_calificaciones', $manejar_encabezados_fijos_en_calificaciones, 'Manejar encabezados fijos en calificaicones', [''=>'','Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$manejar_calificaciones_por_niveles_de_desempenios = 'No';
								if( isset($parametros['manejar_calificaciones_por_niveles_de_desempenios'] ) )
								{
									$manejar_calificaciones_por_niveles_de_desempenios = $parametros['manejar_calificaciones_por_niveles_de_desempenios'];
								}
							?>
							{{ Form::bsSelect('manejar_calificaciones_por_niveles_de_desempenios', $manejar_calificaciones_por_niveles_de_desempenios, 'Manejar calificaciones por niveles desempenios (Usar Logros adicionales y Escala de Valoración)', [''=>'','Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$ocultar_logros_si_hay_logros_adicionales = '0';
								if( isset($parametros['ocultar_logros_si_hay_logros_adicionales'] ) )
								{
									$ocultar_logros_si_hay_logros_adicionales = $parametros['ocultar_logros_si_hay_logros_adicionales'];
								}
							?>
							{{ Form::bsSelect('ocultar_logros_si_hay_logros_adicionales', $ocultar_logros_si_hay_logros_adicionales, 'Ocultar logros normales si hay logros adicionales', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Diseño y formato  </h4>
				<hr>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$etiqueta_calificacion_boletines = '';
								if( isset($parametros['etiqueta_calificacion_boletines'] ) )
								{
									$etiqueta_calificacion_boletines = $parametros['etiqueta_calificacion_boletines'];
								}
							?>
							{{ Form::bsSelect('etiqueta_calificacion_boletines', $etiqueta_calificacion_boletines, 'Calificación a mostrar en boletines', [''=>'','numero_y_letras'=>'Número y letras','solo_numeros'=>'Solo números','solo_letras'=>'Solo letras'], ['class'=>'form-control']) }}
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
								$mostrar_peso_asignaturas_boletines = '';
								if( isset($parametros['mostrar_peso_asignaturas_boletines'] ) )
								{
									$mostrar_peso_asignaturas_boletines = $parametros['mostrar_peso_asignaturas_boletines'];
								}
							?>
							{{ Form::bsSelect('mostrar_peso_asignaturas_boletines', $mostrar_peso_asignaturas_boletines, 'Mostrar peso de asignaturas en boletines', [''=>'','0'=>'No','1'=>'Si'], ['class'=>'form-control']) }}
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
								$permitir_imprimir_boletin_a_estudiantes = '';
								if( isset($parametros['permitir_imprimir_boletin_a_estudiantes'] ) )
								{
									$permitir_imprimir_boletin_a_estudiantes = $parametros['permitir_imprimir_boletin_a_estudiantes'];
								}
							?>
							{{ Form::bsSelect('permitir_imprimir_boletin_a_estudiantes', $permitir_imprimir_boletin_a_estudiantes, 'Permitir imprimir boletin a estudiantes', [''=>'','Si'=>'Si','No'=>'No'], ['class'=>'form-control']) }}
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
												'pdf_boletines_2' => 'Formato # 2 (preescolar)',
												'pdf_boletines_3' => 'Formato # 3 (moderno)',
												'pdf_boletines_4' => 'Formato # 4 (resúmen)',
												'pdf_boletines_6' => 'Formato # 5 (marca de agua)',
												'pdf_boletines_7' => 'Formato # 6 (Calificaciones Aux.)',
                        						'pdf_boletines_8_moderno_foto' => 'Formato # 7 (moderno con foto)',
                        						'pdf_boletines_9_desempenios' => 'Formato # 8 (Por Desempeños)'
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
								$modo_impresion_boletines = 'post';
								if( isset($parametros['modo_impresion_boletines'] ) )
								{
									$modo_impresion_boletines = $parametros['modo_impresion_boletines'];
								}
							?>
							{{ Form::bsSelect('modo_impresion_boletines', $modo_impresion_boletines, 'Modo de imprimir boletin', [ 'post'=> 'POST','ajax'=>'Ajax'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$forma_generar_pdfs = '0';
								if( isset($parametros['forma_generar_pdfs'] ) )
								{
									$forma_generar_pdfs = $parametros['forma_generar_pdfs'];
								}
							?>
							{{ Form::bsSelect('forma_generar_pdfs', $forma_generar_pdfs, 'Forma de generar los PDFs', [ 'Un solo PDF con todos los estudiantes.','Un PDF individual por cada estudiante (Archivo comprimido)' ], ['class'=>'form-control']) }}							
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$asignatura_id_para_asistencias = '';
								if( isset($parametros['asignatura_id_para_asistencias'] ) )
								{
									$asignatura_id_para_asistencias = $parametros['asignatura_id_para_asistencias'];
								}
							?>
							{{ Form::bsSelect('asignatura_id_para_asistencias', $asignatura_id_para_asistencias, 'ID Asignatura para registro de asistencias', App\Calificaciones\Asignatura::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$color_fuente_boletin = 'black'; 
								if( isset($parametros['color_fuente_boletin'] ) )
								{
									$color_fuente_boletin = $parametros['color_fuente_boletin'];
								}
							?>
							{{ Form::bsText('color_fuente_boletin', $color_fuente_boletin, 'Color fuente Informes', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$etiqueta_curso = 'CURSO'; 
								if( isset($parametros['etiqueta_curso'] ) )
								{
									$etiqueta_curso = $parametros['etiqueta_curso'];
								}
							?>
							{{ Form::bsText('etiqueta_curso', $etiqueta_curso, 'Etiqueta Curso', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<?php
									$etiqueta_estudiante = 'ESTUDIANTE'; 
									if( isset($parametros['etiqueta_estudiante'] ) )
									{
										$etiqueta_estudiante = $parametros['etiqueta_estudiante'];
									}
								?>
								{{ Form::bsText('etiqueta_estudiante', $etiqueta_estudiante, 'Etiqueta Estudiante', ['class'=>'form-control']) }}
							</div>
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$etiqueta_observaciones = 'Observaciones'; 
								if( isset($parametros['etiqueta_observaciones'] ) )
								{
									$etiqueta_observaciones = $parametros['etiqueta_observaciones'];
								}
							?>
							{{ Form::bsText('etiqueta_observaciones', $etiqueta_observaciones, 'Etiqueta Observaciones', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<?php
									$etiqueta_logros = 'Logros'; 
									if( isset($parametros['etiqueta_logros'] ) )
									{
										$etiqueta_logros = $parametros['etiqueta_logros'];
									}
								?>
								{{ Form::bsText('etiqueta_logros', $etiqueta_logros, 'Etiqueta Logros', ['class'=>'form-control']) }}
							</div>
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$etiqueta_asignatura = 'Asignatura'; 
								if( isset($parametros['etiqueta_asignatura'] ) )
								{
									$etiqueta_asignatura = $parametros['etiqueta_asignatura'];
								}
							?>
							{{ Form::bsText('etiqueta_asignatura', $etiqueta_asignatura, 'Etiqueta Asignatura', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<?php
									$etiqueta_valoracion = 'Desempeño'; 
									if( isset($parametros['etiqueta_valoracion'] ) )
									{
										$etiqueta_valoracion = $parametros['etiqueta_valoracion'];
									}
								?>
								{{ Form::bsText('etiqueta_valoracion', $etiqueta_valoracion, 'Etiqueta Desempeño/Valoracion', ['class'=>'form-control']) }}
							</div>
						</div>
					</div>

				</div>

				<h4> Parámetros por defecto para imprimir boletines  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_areas = 'No';
								if( isset($parametros['mostrar_areas'] ) )
								{
									$mostrar_areas = $parametros['mostrar_areas'];
								}
							?>
							{{ Form::bsSelect('mostrar_areas', $mostrar_areas,'Mostrar áreas',['No'=>'No','Si'=>'Si'],[]) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_calificacion_media_areas = '0';
								if( isset($parametros['mostrar_calificacion_media_areas'] ) )
								{
									$mostrar_calificacion_media_areas = $parametros['mostrar_calificacion_media_areas'];
								}
							?>
							{{ Form::bsSelect('mostrar_calificacion_media_areas', $mostrar_calificacion_media_areas,'Mostrar calificación media del área',['No','Si'],[]) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_nombre_docentes = 'No';
								if( isset($parametros['mostrar_nombre_docentes'] ) )
								{
									$mostrar_nombre_docentes = $parametros['mostrar_nombre_docentes'];
								}
							?>
							{{ Form::bsSelect('mostrar_nombre_docentes', $mostrar_nombre_docentes,'Mostrar nombre de docentes',['No'=>'No','Si'=>'Si'],[]) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_etiqueta_final = '0';
								if( isset($parametros['mostrar_etiqueta_final'] ) )
								{
									$mostrar_etiqueta_final = $parametros['mostrar_etiqueta_final'];
								}
							?>
							{{ Form::bsSelect('mostrar_etiqueta_final', $mostrar_etiqueta_final,'Mostrar etiqueta al final',['No'=>'No','aprobo_reprobo'=>'Aprobó() Reprobó() Aplazó()'],[]) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_logros = '1';
								if( isset($parametros['mostrar_logros'] ) )
								{
									$mostrar_logros = $parametros['mostrar_logros'];
								}
							?>
							{{ Form::bsSelect('mostrar_logros', $mostrar_logros, 'Mostrar logros',['1'=>'Si','0'=>'No'],[]) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$convetir_logros_mayusculas = 'No';
								if( isset($parametros['convetir_logros_mayusculas'] ) )
								{
									$convetir_logros_mayusculas = $parametros['convetir_logros_mayusculas'];
								}
							?>
							{{ Form::bsSelect('convetir_logros_mayusculas', $convetir_logros_mayusculas, 'Convertir logros a mayúsculas',['No'=>'No','Si'=>'Si'],[]) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_escala_valoracion = 'No';
								if( isset($parametros['mostrar_escala_valoracion'] ) )
								{
									$mostrar_escala_valoracion = $parametros['mostrar_escala_valoracion'];
								}
							?>
							{{ Form::bsSelect('mostrar_escala_valoracion', $mostrar_escala_valoracion,'Mostrar Escala de valoración',['No'=>'No','Si'=>'Si'],[]) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_fallas = '0';
								if( isset($parametros['mostrar_fallas'] ) )
								{
									$mostrar_fallas = $parametros['mostrar_fallas'];
								}
							?>
							{{ Form::bsSelect('mostrar_fallas', $mostrar_fallas,'Mostrar fallas del estudiante (inasistencia)',['No','Si'],[]) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_usuarios_estudiantes = 'No';
								if( isset($parametros['mostrar_usuarios_estudiantes'] ) )
								{
									$mostrar_usuarios_estudiantes = $parametros['mostrar_usuarios_estudiantes'];
								}
							?>
							{{ Form::bsSelect('mostrar_usuarios_estudiantes', $mostrar_usuarios_estudiantes,'Mostrar usuario de estudiantes',['No'=>'No','Si'=>'Si'],[]) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$mostrar_nota_nivelacion = '0';
								if( isset($parametros['mostrar_nota_nivelacion'] ) )
								{
									$mostrar_nota_nivelacion = $parametros['mostrar_nota_nivelacion'];
								}
							?>
							{{ Form::bsSelect('mostrar_nota_nivelacion', $mostrar_nota_nivelacion,'Mostrar nota nivelación',[ '0' => 'No', 'solo_nota_nivelacion_con_etiqueta'=>'Solo nota nivelación (con etiqueta)', 'solo_nota_nivelacion_sin_etiqueta'=>'Solo nota nivelación (sin etiqueta)','ambas_notas'=>'Ambas notas'],[]) }}
						</div>
					</div>

				</div>

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
							{{ Form::bsSelect('mostrar_intensidad_horaria', $mostrar_intensidad_horaria, 'Mostrar intensidad horaria',['1'=>'Si','0'=>'No'],[]) }}
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
								$tam_hoja = 'letter';
								if( isset($parametros['tam_hoja'] ) )
								{
									$tam_hoja = $parametros['tam_hoja'];
								}
							?>
							{{ Form::bsSelect('tam_hoja', $tam_hoja, 'Tamaño hoja',['letter'=>'Carta','folio'=>'Oficio'],[]) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tam_letra = '0';
								if( isset($parametros['tam_letra'] ) )
								{
									$tam_letra = $parametros['tam_letra'];
								}

								$arr_tam_letra = [ 
												'2.5'=>'10',
												'2.75'=>'10.5',
												'3'=>'11',
												'3.25'=>'11.5',
												'3.5'=>'12',
												'3.75'=>'12.5',
												'4'=>'13',
												'4.25'=>'13.5',
												'4.5'=>'14',
												'4.75'=>'14.5',
												'5'=>'15',
												'5.25'=>'15.5',
												'5.5'=>'16'
											];
							?>
							{{ Form::bsSelect( 'tam_letra',  $tam_letra, 'Tamaño Letra', $arr_tam_letra, []) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cantidad_caracteres_para_proxima_pagina = 2500;
								if( isset($parametros['cantidad_caracteres_para_proxima_pagina'] ) )
								{
									$cantidad_caracteres_para_proxima_pagina = $parametros['cantidad_caracteres_para_proxima_pagina'];
								}
							?>
							{{ Form::bsText('cantidad_caracteres_para_proxima_pagina', $cantidad_caracteres_para_proxima_pagina, 'Cant. caracteres para pasar a la siguiente página', [], []) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$ancho_columna_asignatura = 130;
								if( isset($parametros['ancho_columna_asignatura'] ) )
								{
									$ancho_columna_asignatura = $parametros['ancho_columna_asignatura'];
								}
							?>
							{{ Form::bsText('ancho_columna_asignatura', $ancho_columna_asignatura, 'Ancho columna asignaturas (px)',[],[]) }}
						</div>
					</div>

				</div>

				<h4> Académico Estudiantes  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$estudiante_revisar_guia_academicas = '';
								if( isset($parametros['estudiante_revisar_guia_academicas'] ) )
								{
									$estudiante_revisar_guia_academicas = $parametros['estudiante_revisar_guia_academicas'];
								}
							?>
							{{ Form::bsSelect('estudiante_revisar_guia_academicas', $estudiante_revisar_guia_academicas, 'Estudiante puede revisar guías académicas', [''=>'','No'=>'No','Si'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$estudiante_activar_foros_discucion = '';
								if( isset($parametros['estudiante_activar_foros_discucion'] ) )
								{
									$estudiante_activar_foros_discucion = $parametros['estudiante_activar_foros_discucion'];
								}
							?>
							{{ Form::bsSelect('estudiante_activar_foros_discucion', $estudiante_activar_foros_discucion, 'Estudiante puede participar en foros', [''=>'','No'=>'No','Si'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$activar_aula_virtual = '';
								if( isset($parametros['activar_aula_virtual'] ) )
								{
									$activar_aula_virtual = $parametros['activar_aula_virtual'];
								}
							?>
							{{ Form::bsSelect('activar_aula_virtual', $activar_aula_virtual, 'Activar Aula Virtual', [''=>'','0'=>'No','1'=>'Si'], ['class'=>'form-control']) }}
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
								$activar_horario = '0';
								if( isset($parametros['activar_horario'] ) )
								{
									$activar_horario = $parametros['activar_horario'];
								}
							?>
							{{ Form::bsSelect('activar_horario', $activar_horario, 'Activar Horario', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$activar_libreta_pagos = '0';
								if( isset($parametros['activar_libreta_pagos'] ) )
								{
									$activar_libreta_pagos = $parametros['activar_libreta_pagos'];
								}
							?>
							{{ Form::bsSelect('activar_libreta_pagos', $activar_libreta_pagos, 'Activar Libreta de Pagos', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$activar_reconocimientos = '0';
								if( isset($parametros['activar_reconocimientos'] ) )
								{
									$activar_reconocimientos = $parametros['activar_reconocimientos'];
								}
							?>
							{{ Form::bsSelect('activar_reconocimientos', $activar_reconocimientos, 'Activar Reconocimientos', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						&nbsp;
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

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$modulo_activo_solo_para_descargar_informes = '0';
								if( isset($parametros['modulo_activo_solo_para_descargar_informes'] ) )
								{
									$modulo_activo_solo_para_descargar_informes = $parametros['modulo_activo_solo_para_descargar_informes'];
								}
							?>
							{{ Form::bsSelect('modulo_activo_solo_para_descargar_informes', $modulo_activo_solo_para_descargar_informes, 'Modulo activo solo para descargar informes', ['No','Si'], ['class'=>'form-control']) }}
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
								$detallar_curso_grado = '';
								if( isset($parametros['detallar_curso_grado'] ) )
								{
									$detallar_curso_grado = $parametros['detallar_curso_grado'];
								}
							?>
							{{ Form::bsSelect('detallar_curso_grado', $detallar_curso_grado, 'Detallar etiqueta del...', [ ''=>'', 'grado' => 'Grado', 'curso' => 'Curso'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$texto_titulo_inicial = 'EL SUSCRITO RECTOR Y SECRETARIA DE: ';
								if( isset($parametros['texto_titulo_inicial'] ) )
								{
									$texto_titulo_inicial = $parametros['texto_titulo_inicial'];
								}
							?>
							{{ Form::bsText('texto_titulo_inicial', $texto_titulo_inicial, 'Texto título inicial', ['class'=>'form-control']) }}
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