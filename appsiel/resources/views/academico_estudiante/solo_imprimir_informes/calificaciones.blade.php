@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-12" style="padding: 40px;">
			
			<h5>{{ $estudiante->tercero->descripcion }}</h5>
			<h6>
				Matrícula actual: {{ $codigo_matricula }}
				<br>
				Curso actual: {{ $curso->descripcion }}
			</h6>
				
			<div class="row">
				<div style="background-color: #d2d2d2; padding: 10px; border-radius: 5px;">
					<h3>Imprimir informes Académicos</h3>
					<hr>

					@if( $mensaje_facturas_vencidas->mensaje == '' )
						{{ Form::open(['url'=>'academico_estudiante/ajax_calificaciones','id'=>'form_consulta']) }}
							<div class="row">
								<div class="col-sm-3">
									{{ Form::label('periodo_id','Seleccionar periodo') }}
									{{ Form::select('periodo_id',$periodos,null,['class'=>'form-control','id'=>'periodo_id']) }}
								</div>
								<div class="col-sm-3">
									{{ Form::label(' ','.') }}
									<a href="#" class="btn btn-primary bt-detail form-control" id="btn_imprimir"><i class="fa fa-print"></i> Imprimir </a>
								</div>
								<div class="col-sm-6">
								</div>
							</div>

							{{ Form::hidden('curso_id',$curso->id) }}
							
						{{ Form::close() }}

						{{ Form::open( [ 'url' => 'calificaciones/boletines/generarPDF', 'files' => true, 'id' => 'formulario_boletin'] ) }}

                        <?php 
								$tam_letra = '3.5';
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
                            <br><br>
							<div class="row">
								<div class="col-sm-3">
							        {{ Form::bsSelect( 'tam_letra',  $tam_letra, 'Tamaño Letra', $arr_tam_letra, []) }}
                                </div>
                            </div>

							<input type="hidden" name="estudiante_id" value="{{ $estudiante->id }}">
							<input type="hidden" name="periodo_id" value="0" id="boletin_periodo_id">
							<input type="hidden" name="curso_id" value="{{ $curso->id }}">
							<input type="hidden" name="formato" value="{{ $parametros['formato_boletin_default'] }}">
														
							<input type="hidden" name="mostrar_areas" value="{{ $parametros['mostrar_areas'] }}">
							<input type="hidden" name="mostrar_calificacion_media_areas" value="{{ $parametros['mostrar_calificacion_media_areas'] }}">
							<input type="hidden" name="mostrar_nombre_docentes" value="{{ $parametros['mostrar_nombre_docentes'] }}">
							<input type="hidden" name="mostrar_etiqueta_final" value="{{ $parametros['mostrar_etiqueta_final'] }}">

							<input type="hidden" name="mostrar_logros" value="{{ $parametros['mostrar_logros'] }}">
							<input type="hidden" name="mostrar_escala_valoracion" value="{{ $parametros['mostrar_escala_valoracion'] }}">
							<input type="hidden" name="mostrar_fallas" value="{{ $parametros['mostrar_fallas'] }}">
							<input type="hidden" name="mostrar_usuarios_estudiantes" value="{{ $parametros['mostrar_usuarios_estudiantes'] }}">
							<input type="hidden" name="mostrar_nota_nivelacion" value="{{ $parametros['mostrar_nota_nivelacion'] }}">
							<input type="hidden" name="mostrar_intensidad_horaria" value="{{ $parametros['mostrar_intensidad_horaria'] }}">
							<input type="hidden" name="tam_hoja" value="{{ $parametros['tam_hoja'] }}">
							<input type="hidden" name="convetir_logros_mayusculas" value="{{ $parametros['convetir_logros_mayusculas'] }}">
							
							<input type="hidden" name="cantidad_caracteres_para_proxima_pagina" value="{{ $parametros['cantidad_caracteres_para_proxima_pagina'] }}">
							<input type="hidden" name="ancho_columna_asignatura" value="{{ $parametros['ancho_columna_asignatura'] }}">

							<input type="hidden" name="margen_izquierdo" value="5">
							<input type="hidden" name="margen_superior" value="5">
							<input type="hidden" name="margen_derecho" value="5">
							<input type="hidden" name="margen_inferior" value="5">

						{{Form::close()}}
						
						<input type="hidden" name="fecha_hoy" id="fecha_hoy" value="{{ date('Y-m-d') }}">

						<hr>
						@include('layouts.mensajes')

						<br>

						</div>
					@else
						<div class="alert alert-danger">
						  <strong>¡Notificación!</strong>
						  <br>
						  {{ $mensaje_facturas_vencidas->mensaje }}
						</div>
						@if( $mensaje_facturas_vencidas->enlace_libreta != '')
							<div class="alert alert-info">
							Consulte su libreta de pagos <a href="{{ url( $mensaje_facturas_vencidas->enlace_libreta ) }}">AQUÍ</a>.
							</div>
						@endif
					@endif
				</div>

			</div>

			<br/><br/>
		</div>
	</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#periodo_id').focus();

			$('#periodo_id').change(function(){
				$('#btn_imprimir').focus();
			});

			function valida_campos()
			{
				var valida = true;
				if( $('#periodo_id').val() == '' ){
					valida = false;
				}
				return valida;
			}

			$("#btn_imprimir").on('click',function(){
				
				$(this).children('.fa-print').attr('class','fa fa-spinner fa-spin');

                if(!valida_campos())
				{
					alert('Debe ingresar el periodo.');
					$('#periodo_id').focus();
                    
					$('#btn_imprimir').children('.fa-spinner').attr('class','fa fa-print');

					return false;
				}

				$('#boletin_periodo_id').val( $('#periodo_id').val() ); // En el formulario boletín

				$('#formulario_boletin').submit();
			});

		});
        
		$("#btn_imprimir").on('blur',function(){
            $('#btn_imprimir').children('.fa-spinner').attr('class','fa fa-print');
		});

		
	</script>
@endsection