@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Generación de facturas de ventas</h4>
		    <hr>
			{{ Form::open(['url'=>'facturacion_masiva_estudiantes','id'=>'form_generar_consulta_preliminar_cxc']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				<div class="well" style="font-size: 1.1em;">
					<i class="fa fa-info-circle" style="color: blue;" ></i> El sistema va a generar las facturas con base en el plan de pago de cada estudiante. 
					<br> 
					Se tomarán los registros <u>Pendientes</u> del Plan de pagos con <b>Fecha de vencimiento</b> menor o igual a la fecha seleccionada en el campo <u>Fecha vencimiento plan de pagos</u>
				</div>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="form-group">
								{{ Form::bsFecha( 'fecha_vencimiento', date('Y-m-d'), 'Fecha vencimiento plan de pagos', [], [] ) }}
							</div>
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
							<div class="form-group">
								{{ Form::bsSelect( 'concepto_id', null, 'Concepto a facturar', [ '' => '', config('matriculas.inv_producto_id_default_matricula') => 'Matrícula', config('matriculas.inv_producto_id_default_pension') => 'Pensión' ], [] ) }}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						&nbsp;
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="form-group">
								{{ Form::bsFecha( 'fecha', date('Y-m-d'), '*Fecha facturas', [], ['required' => 'required'] ) }}
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<div class="row" style="padding:5px;">
								<div class="form-group">
									{{ Form::bsFecha( 'fecha_vencimiento_factura', date('Y-m-d'), '*Fecha vencimiento facturas', [], ['required' => 'required'] ) }}
								</div>
							</div>
						</div>
					</div>
				</div>

				@if($modulo_facturacion_electronica_activo)
					<div class="row">
						<div class="col-md-6">
							<div class="row" style="padding:5px;">
								<div class="form-group">
									{{ Form::bsSelect( 'generar_fact_electronica', null, '*Enviar facturas electrónicas', [ '' => '', '1' => 'Si', '0' => 'No' ], [ 'required' => 'required' ] ) }}
								</div>
							</div>
						</div>
						<div class="col-md-6">
							&nbsp;
						</div>
					</div>
				@endif

				<br><br>
				@if(!$modulo_facturacion_electronica_activo)
					<div class="alert alert-info">
						<i class="fa fa-info-circle"></i> El módulo de facturación electrónica no está activo. Se generarán facturas de venta sin envío a la DIAN.
					</div>
				@endif

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

				<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">
				
			{{ Form::close() }}
			<div class="row">
				<div class="col-sm-offset-4 col-sm-6">
					<button class="btn btn-success btn-md" id="btn_generar_consulta_preliminar_cxc">
						<i class="fa fa-btn fa-forward"></i> Generar consulta
					</button>
				</div>
			</div>

			<ul class="nav nav-tabs">
			  <li class="active"><a href="#"><h3>Facturas de Ventas</h4></a></li>
			</ul>
			<br/>

		    <div class="well" id="div_alerta" style="display: none; font-size: 1.3em; padding: 10px;">		    	
			  	<div class="row">
			  		<div class="col-sm-6">
			  			<div class="list-group">
					        <a href="#" class="list-group-item active">
					            Resultados 
					        </a>
					        <a href="#" class="list-group-item">
					            <span class="fa fa-list"></span> Total registros <span class="badge" id="total_cantidad_registros"></span>
					        </a>
					        <a href="#" class="list-group-item">
					            <span class="fa fa-users"></span> Total estudiantes <span class="badge" id="total_cantidad_estudiantes"></span>
					        </a>
					        <a href="#" class="list-group-item">
					            <span class="fa fa-usd"></span> Valor total <span class="badge" id="total_precio_total"></span>
					        </a>
					    </div>
			  		</div>
			  		<div class="col-sm-6">
			  			<div id="div_panel_derecho">
			  				<div class="alert alert-warning">
                                      <strong>¡Advertencia!</strong> Este proceso no se puede reversar.
                                    </div>
				  			<input type="checkbox" name="confirmacion" id="confirmacion"> Confirmar la creación de facturas de ventas
				  			<br/>
				  			<button class="btn btn-primary btn-md" id="btn_guardar_cxc">
								<i class="fa fa-btn fa-save"></i> Crear
							</button>
						</div>
			  		</div>			  	
			  	</div>
			  	<br/>
			</div>

			<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="modal_progreso_titulo" aria-hidden="true">
				<div class="modal-dialog modal-sm" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="modal_progreso_titulo">Procesando</h4>
						</div>
						<div class="modal-body" align="center">
							<img src="{{ asset('assets/img/spinning-wheel.gif') }}" width="40px" height="40px">
							<p id="modal_progreso_texto" style="margin-top: 15px;">Espere por favor...</p>
							<div class="progress" style="display:none;" id="modal_progreso_barra_contenedor">
								<div id="modal_progreso_barra" class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%;">
									0%
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="btn_imprimir_lote" class="pull-right" style="display: none;">
				{{ Form::bsBtnPrint('cxc/imprimir_lote/') }}
			</div>
			<div id="btn_enviar_email_lote" class="pull-right" style="display: none;">
				{{ Form::bsBtnEmail('cxc/enviar_email_lote/') }}
				<br/><br/>
			</div>

			<h3 id="lbl_tabla" align="center"></h3>
			<div class="table-responsive">
			    <table class="table table-striped table-bordered" id="tablas_registros">
			        <thead>
			        </thead>
			        <tbody>
			        </tbody>
			    </table>
			</div>		
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; //January is 0!
			var yyyy = today.getFullYear();

			if(dd<10) {
			    dd = '0'+dd
			} 

			if(mm<10) {
			    mm = '0'+mm
			} 

			today = yyyy + '-' + mm + '-' + dd;

			$('#fecha').val( today );
			$('#fecha').focus();
			$('#core_tercero_id').removeAttr("required");

			$('#btn_generar_consulta_preliminar_cxc').click(function(e){
				e.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				if ( $('#fecha').val() > $('#fecha_vencimiento_factura').val() )
				{
					alert('Fecha de vencimiento de la factura debe ser mayor a la Fecha de la factura.')
					return false;
				}

				mostrar_modal('Consultando', 'Buscando registros pendientes...', 0, false);
				var form = $('#form_generar_consulta_preliminar_cxc');
				var url = form.attr('action').replace("facturacion_masiva_estudiantes", "facturacion_masiva_estudiantes/generar_consulta_preliminar");

				data = form.serialize();
				$.post(url,data,function( resultado ){
					ocultar_modal();
					$('#lbl_tabla').html('Detalles <br> <span class="small" style="color: red;"> Nota: A las filas de color rojo no se les creará factura </span>');
					$('#tablas_registros').find('thead').html( resultado.thead );
					$('#tablas_registros').find('tbody').html( resultado.tbody );
					$('#div_alerta').show();
					$('#total_precio_total').html( '$' + resultado.precio_total );
					$('#total_cantidad_registros').html( resultado.cantidad_registros );
					$('#total_cantidad_estudiantes').html( resultado.cantidad_estudiantes );
					$('#confirmacion').prop('checked', false);
					$('#btn_guardar_cxc').prop('disabled', false);
					$('#btn_imprimir_lote').hide();
					$('#btn_enviar_email_lote').hide();
					calcular_totales();
				}).fail(function(){
					ocultar_modal();
					alert('No fue posible generar la consulta preliminar.');
				});
			});

			$('#btn_guardar_cxc').click(function(e){
				e.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				if ( $('#fecha').val() > $('#fecha_vencimiento_factura').val() )
				{
					alert('Fecha de vencimiento de la factura debe ser mayor a la Fecha de la factura.')
					return false;
				}

				if ($('#confirmacion').is(":checked"))
				{					
					if(!confirm("¿Realmente desea generar todas las facturas consultadas?")){
						return false;
					}else{
						crear_facturas_por_lotes();
					}
					
				}else{
					$('#confirmacion').focus();
					alert('Debe confirmar la creación de todas las facturas.');
				}
			});

			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
				fila.remove();
				$('#btn_nuevo').show();
				calcular_totales();
			});

			function calcular_totales()
			{
				var total = 0;
				var cantidad_registros = 0;
				var estudiantes = {};

				$('#tablas_registros tbody tr').each(function() {
					var fila = $(this);
					var linea_plan_pago_id = parseInt(fila.find('td:eq(0)').text(), 10);
					if (!linea_plan_pago_id) {
						return true;
					}

					total += parseMoney(fila.find('td:eq(1)').text());
					cantidad_registros++;
					estudiantes[$.trim(fila.find('td:eq(2)').text())] = true;
				});

				$('#total_facturas').text( "$" + formatMoney(total) );
				$('#total_precio_total').html( "$" + formatMoney(total) );
				$('#total_cantidad_registros').html( cantidad_registros );
				$('#total_cantidad_estudiantes').html( contar_propiedades(estudiantes) );
			}

			function crear_facturas_por_lotes()
			{
				var lineas = obtener_lineas_validas();
				if (lineas.length == 0) {
					alert('No hay líneas válidas para crear facturas.');
					return false;
				}

				var form = $('#form_generar_consulta_preliminar_cxc');
				var url = form.attr('action');
				var lote = Date.now().toString();
				var tamano_lote = 5;
				var lotes = [];
				var acumulado = {
					tbody: '',
					precio_total: 0,
					cantidad_facturas: 0,
					estudiantes: {},
					empresa_id: 0,
					core_tipo_doc_app_id: 0,
					consec_desde: 0,
					consec_hasta: 0,
					mensaje: ''
				};

				for (var i = 0; i < lineas.length; i += tamano_lote) {
					lotes.push(lineas.slice(i, i + tamano_lote));
				}

				$('#btn_guardar_cxc').prop('disabled', true);
				$('#tablas_registros').find('thead').html('');
				$('#tablas_registros').find('tbody').html('');
				mostrar_modal('Creando facturas', 'Preparando lote 1 de ' + lotes.length + '...', 0, true);

				procesar_lote(0);

				function procesar_lote(indice)
				{
					if (indice >= lotes.length) {
						finalizar_creacion(acumulado);
						return;
					}

					var porcentaje = Math.round((indice / lotes.length) * 100);
					actualizar_modal('Creando facturas', 'Procesando lote ' + (indice + 1) + ' de ' + lotes.length + '...', porcentaje);

					$.post(url, crear_payload_lote(form, lotes[indice], lote), function(resultado) {
						acumulado.tbody += resultado.tbody;
						acumulado.precio_total += parseMoney(resultado.precio_total);
						acumulado.cantidad_facturas += parseInt(resultado.cantidad_facturas, 10) || 0;
						acumulado.mensaje = resultado.mensaje;
						acumulado.empresa_id = resultado.empresa_id || acumulado.empresa_id;
						acumulado.core_tipo_doc_app_id = resultado.core_tipo_doc_app_id || acumulado.core_tipo_doc_app_id;
						if (resultado.consec_desde && (acumulado.consec_desde == 0 || resultado.consec_desde < acumulado.consec_desde)) {
							acumulado.consec_desde = resultado.consec_desde;
						}
						if (resultado.consec_hasta && resultado.consec_hasta > acumulado.consec_hasta) {
							acumulado.consec_hasta = resultado.consec_hasta;
						}

						$.each(lotes[indice], function(key, linea) {
							acumulado.estudiantes[linea.Estudiante] = true;
						});

						$('#tablas_registros').find('thead').html(resultado.thead);
						$('#tablas_registros').find('tbody').append(resultado.tbody);
						$('#total_precio_total').html('$' + formatMoney(acumulado.precio_total));
						$('#total_cantidad_registros').html(acumulado.cantidad_facturas);
						$('#total_cantidad_estudiantes').html(contar_propiedades(acumulado.estudiantes));

						procesar_lote(indice + 1);
					}).fail(function() {
						ocultar_modal();
						$('#btn_guardar_cxc').prop('disabled', false);
						$('#div_panel_derecho').html('<div class="alert alert-danger"><strong>Error.</strong> No fue posible procesar el lote ' + (indice + 1) + ' de ' + lotes.length + '.</div>');
					});
				}
			}

			function finalizar_creacion(acumulado)
			{
				actualizar_modal('Creando facturas', 'Proceso finalizado.', 100);
				setTimeout(function(){
					ocultar_modal();
				}, 400);

				$('#btn_generar_consulta_preliminar_cxc').hide();
				$('#lbl_tabla').html('Facturas generadas');
				$('#total_precio_total').html('$' + formatMoney(acumulado.precio_total));
				$('#total_cantidad_registros').html(acumulado.cantidad_facturas);
				$('#total_cantidad_estudiantes').html(contar_propiedades(acumulado.estudiantes));
				$('#div_panel_derecho').html(acumulado.mensaje);

				if (acumulado.empresa_id && acumulado.core_tipo_doc_app_id && acumulado.consec_desde && acumulado.consec_hasta) {
					$('#btn_imprimir_lote').show(1000);
					$('#btn_imprimir_lote').find('a').attr('href', base_lote_url('cxc/imprimir_lote/') + '/' + acumulado.empresa_id + '/' + acumulado.core_tipo_doc_app_id + '/' + acumulado.consec_desde + '/' + acumulado.consec_hasta);

					$('#btn_enviar_email_lote').show(1000);
					$('#btn_enviar_email_lote').find('a').attr('href', base_lote_url('cxc/enviar_email_lote/') + '/' + acumulado.empresa_id + '/' + acumulado.core_tipo_doc_app_id + '/' + acumulado.consec_desde + '/' + acumulado.consec_hasta);
				}
			}

			function obtener_lineas_validas()
			{
				return $('#tablas_registros').tableToJSON().filter(function(linea) {
					return parseInt(linea.linea_plan_pago_id, 10) > 0;
				});
			}

			function crear_payload_lote(form, lineas, lote)
			{
				var campos = form.serializeArray();
				for (var i = 0; i < campos.length; i++) {
					if (campos[i].name == 'lineas_registros') {
						campos[i].value = JSON.stringify(lineas);
					}
				}
				campos.push({name: 'lote', value: lote});
				return $.param(campos);
			}

			function mostrar_modal(titulo, texto, porcentaje, mostrar_barra)
			{
				$('#modal_progreso_titulo').text(titulo);
				$('#modal_progreso_texto').text(texto);
				$('#modal_progreso_barra_contenedor').toggle(mostrar_barra);
				actualizar_barra(porcentaje);
				$('#myModal').modal({keyboard: false, backdrop: "static"});
			}

			function actualizar_modal(titulo, texto, porcentaje)
			{
				$('#modal_progreso_titulo').text(titulo);
				$('#modal_progreso_texto').text(texto);
				actualizar_barra(porcentaje);
			}

			function actualizar_barra(porcentaje)
			{
				$('#modal_progreso_barra').css('width', porcentaje + '%').text(porcentaje + '%');
			}

			function ocultar_modal()
			{
				$('#myModal').modal('hide');
			}

			function parseMoney(valor)
			{
				valor = $.trim((valor || '').toString());
				valor = valor.replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(',', '.');
				var numero = parseFloat(valor);
				return isNaN(numero) ? 0 : numero;
			}

			function formatMoney(valor)
			{
				return Math.round(valor).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
			}

			function contar_propiedades(objeto)
			{
				var total = 0;
				for (var propiedad in objeto) {
					if (objeto.hasOwnProperty(propiedad)) {
						total++;
					}
				}
				return total;
			}

			function base_lote_url(ruta)
			{
				var base = "{{ url('/') }}";
				return base + '/' + ruta.replace(/^\/|\/$/g, '');
			}

			});
	</script>
@endsection
