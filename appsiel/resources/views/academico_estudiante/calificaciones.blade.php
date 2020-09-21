@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-12">
			
			<h3>{{ $estudiante->nombre_completo }}</h3>
			<h4>
				Matrícula actual: {{ $codigo_matricula }}
				<br>
				Curso actual: {{ $curso->descripcion }}
			</h4>


			<div class="well">
				
				<h3>Consultar preinformes</h3>
				<hr>
				<?php 
					$modelo_padre_id = App\Sistema\Modelo::where('modelo', 'periodos')->value('id');
					$core_campo_id = 749; // Visualizar preinforme

					$opciones = App\Core\ModeloEavValor::where(
	                                                    [ 
	                                                        "modelo_padre_id" => $modelo_padre_id,
	                                                        "core_campo_id" => $core_campo_id,
	                                                        "valor" => 1
	                                                    ]
	                                                )
	                                            ->get();
	                $vec[0]='';
			        foreach ($opciones as $opcion)
			        {
			        	$el_periodo = App\Calificaciones\Periodo::find( $opcion->registro_modelo_padre_id );
			        	$periodo_lectivo = App\Matriculas\PeriodoLectivo::find( $el_periodo->periodo_lectivo_id );

			            $vec[$el_periodo->id] = $periodo_lectivo->descripcion . ' > ' . $el_periodo->descripcion;
			        }

			        $periodos_visualizar = $vec;

				?>

				<div class="row">
						<div class="col-sm-4">
							{{ Form::label('periodo_visualizar_id','Seleccionar periodo') }}
							{{ Form::select('periodo_visualizar_id',$periodos_visualizar,null,['class'=>'form-control','id'=>'periodo_visualizar_id']) }}
						</div>
						<div class="col-sm-">
							<br>
							<button class="btn btn-primary btn-sm" id="btn_consultar_preinforme" data-periodo_visualizar_id="0" data-curso_id="{{$curso->id}}" data-estudiante_id="{{ $estudiante->id }}">Consultar</button>
						</div>
						@include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
				</div>
			</div>



			<h3>Calificaciones por periodo</h3>
			<hr>
			{{ Form::open(['url'=>'academico_estudiante/ajax_calificaciones','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-2">
						{{ Form::label('periodo_id','Seleccionar periodo') }}
						{{ Form::select('periodo_id',$periodos,null,['class'=>'form-control','id'=>'periodo_id']) }}
					</div>
					<div class="col-sm-2">
					</div>
					<div class="col-sm-3">
					</div>
					<div class="col-sm-3">
					</div>
					<div class="col-sm-2">
						{{ Form::label(' ','.') }}
						<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Consultar</a>
					</div>
				</div>

				{{ Form::hidden('curso_id',$curso->id) }}
				
			{{ Form::close() }}
			<!--	<button id="btn_ir">ir</button>	-->


			{{ Form::open( [ 'url' => 'calificaciones/boletines/generarPDF', 'files' => true, 'id' => 'formulario_boletin'] ) }}

				<input type="hidden" name="estudiante_id" value="{{ $estudiante->id }}">
				<input type="hidden" name="periodo_id" value="0" id="boletin_periodo_id">
				<input type="hidden" name="curso_id" value="{{ $curso->id }}">
				<input type="hidden" name="formato" value="{{ config('calificaciones.formato_boletin_default') }}">
				<input type="hidden" name="mostrar_areas" value="Si">
				<input type="hidden" name="mostrar_nombre_docentes" value="Si">
				<input type="hidden" name="mostrar_etiqueta_final" value="No">
				<input type="hidden" name="mostrar_escala_valoracion" value="Si">
				<input type="hidden" name="tam_hoja" value="folio">
				<input type="hidden" name="tam_letra" value="4">
				<input type="hidden" name="convetir_logros_mayusculas" value="No">
				<input type="hidden" name="mostrar_usuarios_estudiantes" value="No">

			{{Form::close()}}
			
			<input type="hidden" name="fecha_hoy" id="fecha_hoy" value="{{ date('Y-m-d') }}">

		</div>
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('calificaciones') }}
			<a class="btn btn-primary btn-sm" id="btn_imprimir" target="_blank" style="display: none;">
				<i class="fa fa-btn fa-print"></i> Imprimir
			</a>

			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('[data-toggle="popover"]').popover();

			$('[data-toggle="tooltip"]').tooltip();
			
			$('#periodo_id').focus();

			$('#periodo_id').change(function(){
				$('#btn_generar').focus();
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos())
				{
					alert('Debe ingresar el periodo.');
					$('#periodo_id').focus();
					return false;
				}

				$('#boletin_periodo_id').val( $('#periodo_id').val() ); // En el formulario boletín

				$('#resultado_consulta').html('');
				$('#btn_imprimir').hide();
				$('#div_cargando').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();

				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();
					$('#resultado_consulta').html(respuesta);
					$('#btn_excel').show(500);

					if ( document.getElementById('fecha_termina_periodo').value <= document.getElementById('fecha_hoy').value )
					{
						$('#btn_imprimir').show(500);
					}
				});
			});

			function valida_campos()
			{
				var valida = true;
				if( $('#periodo_id').val() == '' ){
					valida = false;
				}
				return valida;
			}

			$('#periodo_visualizar_id').on('change',function(){
				$('#btn_consultar_preinforme').attr('data-periodo_visualizar_id', $(this).val() );
			});

			$('#btn_consultar_preinforme').on('click',function(){

				if( $(this).attr('data-periodo_visualizar_id') != 0 )
				{
					var url = "{{url('/consultar_preinforme')}}" + "/" + $(this).attr('data-periodo_visualizar_id') + "/" + $(this).attr('data-curso_id') + "/" + $(this).attr('data-estudiante_id');

					$("#myModal").modal({backdrop: "static"});
			        $("#div_spin").show();
			        $(".btn_edit_modal").hide();
				    $('.btn_save_modal').hide();
				    $('.modal-title').html('');				    

					$.get( url, function( datos ) {
				        $('#div_spin').hide();

				        $('#contenido_modal').html( datos );

					});
				}else{
					$('#periodo_visualizar_id').focus();
					$('#contenido_modal').html( '' );
					alert('Debe Seleccionar un periodo.');
				}
			});


			$("#btn_imprimir").on('click',function(){
				$('#formulario_boletin').submit();
			});

		});

		
	</script>
@endsection