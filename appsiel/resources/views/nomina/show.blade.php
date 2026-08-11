@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		.dropdown-submenu{position:relative;}
		.dropdown-submenu>.dropdown-menu{top:0;left:100%;margin-top:-6px;margin-left:-1px;-webkit-border-radius:0 6px 6px 6px;-moz-border-radius:0 6px 6px 6px;border-radius:0 6px 6px 6px;}
		.dropdown-submenu:hover>.dropdown-menu{display:block;}
		.dropdown-submenu>a:after{display:block;content:" ";float:right;width:0;height:0;border-color:transparent;border-style:solid;border-width:5px 0 5px 5px;border-left-color:#cccccc;margin-top:5px;margin-right:-10px;}
		.dropdown-submenu:hover>a:after{border-left-color:#ffffff;}
		.dropdown-submenu.pull-left{float:none;}.dropdown-submenu.pull-left>.dropdown-menu{left:-100%;margin-left:10px;-webkit-border-radius:6px 0 6px 6px;-moz-border-radius:6px 0 6px 6px;border-radius:6px 0 6px 6px;}

		table{
			margin-top: 0 !important;
		}
		td > table > tbody{
			background-color: unset;
		}

		#espera_liquidacion_automatica {
			display: none;
			position: fixed;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			z-index: 20000;
			background: rgba(0, 0, 0, 0.55);
			align-items: center;
			justify-content: center;
		}

		.espera-liquidacion-contenido {
			width: 420px;
			max-width: calc(100% - 30px);
			padding: 32px 24px;
			border-radius: 5px;
			background: #fff;
			box-shadow: 0 5px 20px rgba(0, 0, 0, 0.35);
			text-align: center;
		}

		.espera-liquidacion-contenido .fa-spinner {
			margin-bottom: 15px;
			color: #2196f3;
			font-size: 48px;
		}
	</style>
@endsection

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-5">
			&nbsp;&nbsp;&nbsp; {{ Form::bsBtnCreate( 'web/create?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
			

			@if ( $encabezado_doc->estado == 'Activo' )
				{{ Form::bsBtnEdit2('web/'.$encabezado_doc_id.'/edit?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
				{{ Form::bsBtnEliminar('web_eliminar/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
				&nbsp;&nbsp;&nbsp; {{ Form::bsBtnDropdown( 'Liquidar', 'primary', 'cogs', 
						[ 
							['link' => 'nomina/liquidacion/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
							'etiqueta' => 'Registros automáticos (todo)'],
							['link' => 'nomina/liquidacion_sp/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
							'etiqueta' => 'Solo salud y pensión'],
							['link' => 'nom_liquidar_prima_antiguedad/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
							'etiqueta' => 'Primas de antigüedad']
						] ) }}
				&nbsp;&nbsp;&nbsp;
				<div class="dropdown" style="display:inline-block;">
					<button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
						<i class="fa fa-history"></i> Retirar <span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						<li><a href="{{ url('nomina/retirar_liquidacion/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion')) }}">Registros automáticos (todo)</a></li>
						<li><a href="{{ url('nom_retirar_prima_antiguedad/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion')) }}">Primas de antigüedad</a></li>
						<li role="separator" class="divider"></li>
						<li><a href="#" id="btn_abrir_retiro_personalizado"><i class="fa fa-filter"></i> Retiro personalizado</a></li>
					</ul>
				</div>
			@else
				<small>(Documento está <b>{{ $encabezado_doc->estado }}</b>)</small>
			@endif
		</div>
		<div class="col-md-6">

			Formato: {{ Form::select('formato_impresion_id',['1'=>'Estándar','2'=>'Estándar v2'], null, [ 'id' =>'formato_impresion_id' ] ) }}
			{{ Form::bsBtnPrint( 'nomina_print/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion').'&formato_impresion_id=1' ) }}
			<a class="btn-gmail" id="btn_export_registros_xlsx" href="{{ url('nomina_export_registros_xlsx/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion')) }}" title="Exportar XLSX"><i class="fa fa-file-excel-o"></i></a>
			
		</div>
		<div class="col-md-1">
			<div class="pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev( 'nomina/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext( 'nomina/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
				@endif
			</div>
		</div>
	</div>
	

	<!-- @ include('nomina.incluir.btn_liquidacion') -->

	
	<hr>

	@include('layouts.mensajes')

	<div id="espera_liquidacion_automatica" role="alert" aria-live="assertive" aria-busy="true">
		<div class="espera-liquidacion-contenido">
			<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
			<h3 style="margin-top:0;">Liquidando registros automáticos</h3>
			<p style="margin-bottom:0;">Espere por favor. Este proceso puede tardar varios minutos.</p>
			<small>No cierre ni recargue esta página.</small>
		</div>
	</div>

	<div class="container-fluid">
		<div class="marco_formulario">

			@include('nomina.incluir.encabezado_transaccion')

			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#tab1"> Registros de liquidación </a></li>
				<li><a data-toggle="tab" href="#tab2"> Empleados del documento </a></li>
				<li><a data-toggle="tab" href="#tab3"> Contabilización </a></li>
		    </ul>

		    <div class="tab-content">
		    	<div id="tab1" class="tab-pane fade in active">
			        @include( 'nomina.incluir.tabla_registros_documento' )
			    </div>
			    <div id="tab2" class="tab-pane fade">
			        @include( 'nomina.incluir.tabla_empleados_documento' )
		    	</div>
			    <div id="tab3" class="tab-pane fade">
			    	<br><br>
			        @include('transaccion.registros_contables_con_terceros')
		    	</div>
		    </div><!---->
			
			@include('transaccion.auditoria', [ 'doc_encabezado' => $encabezado_doc ])

		</div>
	</div>
	<br/><br/>	

	@include('nomina.incluir.modal_retiro_personalizado')

	<div class="modal fade" id="modal_confirmar_eliminar_empleado" tabindex="-1" role="dialog" aria-labelledby="modal_confirmar_eliminar_empleado_label" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modal_confirmar_eliminar_empleado_label">Confirmar retiro</h4>
				</div>
				<div class="modal-body">
					<p id="modal_confirmar_eliminar_empleado_texto">¿Realmente quiere retirar a este empleado del documento?</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					<button type="button" class="btn btn-danger" id="btn_confirmar_eliminar_empleado">
						<span class="btn-text">Retirar</span>
						<i class="fa fa-spinner fa-spin btn-spinner" style="display: none; margin-left: 6px;"></i>
					</button>
				</div>
			</div>
		</div>
	</div>

@endsection
@section('scripts9')
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
    //código a ejecutar cuando el DOM está listo para recibir acciones
	//console.log(event)
	document.getElementsByClassName("buttons-excel")[0].classList.add("btn-gmail","btn-excel");
	document.getElementsByClassName("buttons-excel")[0].innerHTML = '<i class="fa fa-file-excel-o"></i>';
	document.getElementsByClassName("buttons-pdf")[0].classList.add("btn-gmail","btn-pdf");
	document.getElementsByClassName("buttons-pdf")[0].innerHTML = '<i class="fa fa-file-pdf-o"></i>';
	document.getElementsByClassName("dt-buttons")[0].classList.add("d-inline");
	document.getElementById('myTable_filter').children[0].children[0].classList.add('form-control');
	document.getElementById('myTable_filter').children[0].children[0].placeholder = 'Escriba aquí para buscar...';	
	
	
});
			$('#formato_impresion_id').on('change',function(){
				var btn_print = $('#btn_print').attr('href');

				n = btn_print.search('formato_impresion_id');
				var url_aux = btn_print.substr(0,n);
				var new_url = url_aux + 'formato_impresion_id=' + $(this).val();
				
				$('#btn_print').attr('href', new_url);



				var btn_email = $('#btn_email').attr('href');

				n = btn_email.search('formato_impresion_id');
				var url_aux = btn_email.substr(0,n);
				var new_url = url_aux + 'formato_impresion_id=' + $(this).val();
				
				$('#btn_email').attr('href', new_url);
				
			});

			function actualizarTablaEmpleados(respuesta) {
				if (!respuesta || !respuesta.ok) {
					return;
				}

				$('#tabla-empleados-documento').html(respuesta.tabla);

				var $select = $('#registro_modelo_hijo_id');
				$select.empty();
				$.each(respuesta.opciones || {}, function(valor, texto){
					$select.append($('<option></option>').attr('value', valor).text(texto));
				});

				if (respuesta.mensaje) {
					$('#empleados-alerta').html('<div class="alert alert-success">' + respuesta.mensaje + '</div>');
				}
			}

			$(document).on('submit', '#form-asignar-empleado', function(event){
				event.preventDefault();
				var $form = $(this);
				var $btnGuardar = $('#btn_guardar_empleado');
				var $opcionSeleccionada = $('#registro_modelo_hijo_id option:selected');
				var textoEmpleado = $opcionSeleccionada.text().trim();
				var partesEmpleado = textoEmpleado.split(' ');
				var empleadoSeleccionado = {
					id: $opcionSeleccionada.val(),
					identificacion: partesEmpleado.shift() || '',
					nombre: partesEmpleado.join(' ').trim()
				};
				$btnGuardar.prop('disabled', true);
				$btnGuardar.find('.btn-text').text('Guardando...');
				$btnGuardar.find('.btn-spinner').show();

				$.ajax({
					url: $form.attr('action'),
					type: 'POST',
					data: $form.serialize(),
					success: function(respuesta){
						actualizarTablaEmpleados(respuesta);
						agregarEmpleadoATablaRegistros(empleadoSeleccionado);
						$('#form-asignar-empleado').find('input[type="text"]').val('');
						$('#form-asignar-empleado').find('.custom-combobox-input').val('');
						$btnGuardar.prop('disabled', false);
						$btnGuardar.find('.btn-text').text('Guardar');
						$btnGuardar.find('.btn-spinner').hide();
					},
					error: function(xhr){
						var mensaje = 'No se pudo agregar el empleado.';
						if (xhr.responseJSON && (xhr.responseJSON.mensaje_error || xhr.responseJSON.message)) {
							mensaje = xhr.responseJSON.mensaje_error || xhr.responseJSON.message;
						}
						$('#empleados-alerta').html('<div class="alert alert-danger">' + mensaje + '</div>');
						$btnGuardar.prop('disabled', false);
						$btnGuardar.find('.btn-text').text('Guardar');
						$btnGuardar.find('.btn-spinner').hide();
					}
				});
			});

			function cambiarEstadoBoton($boton, procesando, textoNormal, textoProcesando) {
				$boton.prop('disabled', procesando);
				$boton.find('.btn-text').text(procesando ? textoProcesando : textoNormal);
				$boton.find('.btn-spinner').toggle(procesando);
			}

			var liquidacionAutomaticaEnProceso = false;
			var urlLiquidacionAutomatica = '{{ url('nomina/liquidacion/'.$encabezado_doc_id) }}'.split('?')[0];

			$(document).on('click', 'a.enlace_dropdown', function(event){
				var enlace = this.href.split('?')[0];
				if (enlace !== urlLiquidacionAutomatica) {
					return;
				}

				event.preventDefault();
				if (liquidacionAutomaticaEnProceso) {
					return;
				}

				liquidacionAutomaticaEnProceso = true;
				var destino = this.href;
				var $botonLiquidar = $(this).closest('.dropdown').find('.dropdown-toggle');
				$botonLiquidar.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Liquidando...');
				$('#espera_liquidacion_automatica').css('display', 'flex');

				setTimeout(function(){
					window.location.href = destino;
				}, 100);
			});

			window.addEventListener('pageshow', function(){
				liquidacionAutomaticaEnProceso = false;
				$('#espera_liquidacion_automatica').hide();
			});

			function datosGestionEmpleados(accion) {
				return {
					_token: '{{ csrf_token() }}',
					accion_masiva: accion,
					nom_doc_encabezado_id: '{{ $encabezado_doc_id }}',
					url_id: '{{ Input::get('id') }}',
					url_id_modelo: '{{ Input::get('id_modelo') }}',
					url_id_transaccion: '{{ Input::get('id_transaccion') }}'
				};
			}

			$('#btn_confirmar_agregar_empleados').on('click', function(){
				var $boton = $(this);
				cambiarEstadoBoton($boton, true, 'Agregar empleados', 'Agregando...');

				$.ajax({
					url: $boton.data('url'),
					type: 'POST',
					data: datosGestionEmpleados('agregar_empleados'),
					success: function(respuesta){
						actualizarTablaEmpleados(respuesta);
						$.each(respuesta.contratos_agregados || [], function(indice, empleado){
							agregarEmpleadoATablaRegistros(empleado);
						});
						$('#modal_agregar_empleados_documento').modal('hide');
						cambiarEstadoBoton($boton, false, 'Agregar empleados', 'Agregando...');
					},
					error: function(xhr){
						var mensaje = xhr.responseJSON && xhr.responseJSON.mensaje_error
							? xhr.responseJSON.mensaje_error
							: 'No se pudieron agregar los empleados.';
						$('#empleados-alerta').html('<div class="alert alert-danger">' + mensaje + '</div>');
						$('#modal_agregar_empleados_documento').modal('hide');
						cambiarEstadoBoton($boton, false, 'Agregar empleados', 'Agregando...');
					}
				});
			});

			$('#btn_confirmar_retirar_empleados').on('click', function(){
				var $boton = $(this);
				cambiarEstadoBoton($boton, true, 'Retirar empleados', 'Retirando...');

				$.ajax({
					url: $boton.data('url'),
					type: 'POST',
					data: datosGestionEmpleados('retirar_empleados'),
					success: function(respuesta){
						actualizarTablaEmpleados(respuesta);
						retirarContratosDeTablaRegistros(respuesta.contratos_retirados || []);
						var tipoMensaje = respuesta.tipo_mensaje || 'success';
						$('#empleados-alerta').html('<div class="alert alert-' + tipoMensaje + '">' + respuesta.mensaje + '</div>');
						$('#modal_retirar_empleados_documento').modal('hide');
						cambiarEstadoBoton($boton, false, 'Retirar empleados', 'Retirando...');
					},
					error: function(xhr){
						var mensaje = xhr.responseJSON && xhr.responseJSON.mensaje_error
							? xhr.responseJSON.mensaje_error
							: 'No se pudieron retirar los empleados.';
						$('#empleados-alerta').html('<div class="alert alert-danger">' + mensaje + '</div>');
						$('#modal_retirar_empleados_documento').modal('hide');
						cambiarEstadoBoton($boton, false, 'Retirar empleados', 'Retirando...');
					}
				});
			});

			var retiroPersonalizadoFilas = {!! json_encode($opciones_retiro_personalizado, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

			function agregarOpcionesUnicas($select, filas, campoId, construirTexto) {
				var usados = {};
				$.each(filas, function(indice, fila){
					var id = String(fila[campoId]);
					if (usados[id]) {
						return true;
					}
					usados[id] = true;
					$select.append($('<option></option>').attr('value', id).text(construirTexto(fila)));
				});
			}

			function cargarOpcionesRetiroPersonalizado() {
				var $grupo = $('#retiro_grupo_empleado_id');
				$grupo.empty().append($('<option></option>').attr('value', '').text('Todos los grupos'));
				agregarOpcionesUnicas($grupo, retiroPersonalizadoFilas, 'grupo_id', function(fila){
					return fila.grupo;
				});

				var $empleado = $('#retiro_nom_contrato_id');
				$empleado.empty().append($('<option></option>').attr('value', '').text('Todos los empleados'));
				agregarOpcionesUnicas($empleado, retiroPersonalizadoFilas, 'contrato_id', function(fila){
					return fila.identificacion + ' - ' + fila.empleado;
				});

				var $concepto = $('#retiro_nom_concepto_id');
				$concepto.empty().append($('<option></option>').attr('value', '').text('Todos los conceptos'));
				agregarOpcionesUnicas($concepto, retiroPersonalizadoFilas, 'concepto_id', function(fila){
					return fila.concepto;
				});
			}

			function inicializarBuscadoresRetiroPersonalizado() {
				if (!$.fn.select2) {
					return;
				}

				$('.retiro-personalizado-select').each(function(){
					var $select = $(this);
					$select.select2({
						width: '100%',
						dropdownParent: $('#modal_retiro_personalizado'),
						placeholder: $select.find('option:first').text(),
						allowClear: true,
						minimumResultsForSearch: 0
					});
				});
			}

			function valorFiltroRetiroPersonalizado(selector) {
				var valor = $(selector).val();
				return valor === null || typeof valor === 'undefined' ? '' : String(valor);
			}

			function actualizarCantidadRetiroPersonalizado() {
				var grupoId = valorFiltroRetiroPersonalizado('#retiro_grupo_empleado_id');
				var contratoId = valorFiltroRetiroPersonalizado('#retiro_nom_contrato_id');
				var conceptoId = valorFiltroRetiroPersonalizado('#retiro_nom_concepto_id');
				var cantidad = 0;

				var hayFiltro = grupoId !== '' || contratoId !== '' || conceptoId !== '';

				if (hayFiltro) {
					$.each(retiroPersonalizadoFilas, function(indice, fila){
						if (grupoId !== '' && String(fila.grupo_id) !== String(grupoId)) {
							return true;
						}
						if (contratoId !== '' && String(fila.contrato_id) !== String(contratoId)) {
							return true;
						}
						if (conceptoId !== '' && String(fila.concepto_id) !== String(conceptoId)) {
							return true;
						}
						cantidad += Number(fila.cantidad || 0);
					});
				}

				$('#retiro_personalizado_cantidad').text(cantidad);
				$('#btn_ejecutar_retiro_personalizado').prop('disabled', !hayFiltro || cantidad < 1);
				return cantidad;
			}

			function descripcionFiltrosRetiroPersonalizado() {
				var filtros = [];
				if (valorFiltroRetiroPersonalizado('#retiro_grupo_empleado_id') !== '') {
					filtros.push('Grupo: ' + $('#retiro_grupo_empleado_id option:selected').text());
				}
				if (valorFiltroRetiroPersonalizado('#retiro_nom_contrato_id') !== '') {
					filtros.push('Empleado: ' + $('#retiro_nom_contrato_id option:selected').text());
				}
				if (valorFiltroRetiroPersonalizado('#retiro_nom_concepto_id') !== '') {
					filtros.push('Concepto: ' + $('#retiro_nom_concepto_id option:selected').text());
				}

				return filtros.join('; ');
			}

			function mostrarErrorRetiroPersonalizado(mensaje) {
				$('#retiro-personalizado-alerta').text(mensaje).show();
			}

			$('#btn_abrir_retiro_personalizado').on('click', function(event){
				event.preventDefault();
				$('#retiro-personalizado-alerta').hide().text('');
				if (!$('#retiro_grupo_empleado_id').data('select2')) {
					cargarOpcionesRetiroPersonalizado();
					inicializarBuscadoresRetiroPersonalizado();
				}
				$('.retiro-personalizado-select').val('').trigger('change');
				$('#retiro_personalizado_cantidad').text('0');
				$('#btn_ejecutar_retiro_personalizado').prop('disabled', true);

				if (retiroPersonalizadoFilas.length === 0) {
					mostrarErrorRetiroPersonalizado('El documento no tiene registros disponibles para retirar.');
				}

				$('#modal_retiro_personalizado').modal('show');
			});

			$('.retiro-personalizado-select').on('change', function(){
				$('#retiro-personalizado-alerta').hide();
				actualizarCantidadRetiroPersonalizado();
			});

			function ejecutarRetiroPersonalizado() {
				var $boton = $('#btn_ejecutar_retiro_personalizado');
				cambiarEstadoBoton($boton, true, 'Retirar', 'Retirando...');
				$('#modal_retiro_personalizado select, #modal_retiro_personalizado .close, #modal_retiro_personalizado .btn-default').prop('disabled', true);
				$('.retiro-personalizado-select').trigger('change.select2');

				$.ajax({
					url: {!! json_encode(url('nomina/retirar_personalizado/'.$encabezado_doc_id).'?'.http_build_query([
						'id' => Input::get('id'),
						'id_modelo' => Input::get('id_modelo'),
						'id_transaccion' => Input::get('id_transaccion'),
					])) !!},
					type: 'POST',
					dataType: 'json',
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
						'Accept': 'application/json'
					},
					data: {
						_token: '{{ csrf_token() }}',
						grupo_empleado_id: valorFiltroRetiroPersonalizado('#retiro_grupo_empleado_id'),
						nom_contrato_id: valorFiltroRetiroPersonalizado('#retiro_nom_contrato_id'),
						nom_concepto_id: valorFiltroRetiroPersonalizado('#retiro_nom_concepto_id'),
						cantidad_esperada: actualizarCantidadRetiroPersonalizado(),
						confirmar_retiro: 1
					},
					success: function(respuesta){
						$('#modal_retiro_personalizado').modal('hide');
						var mensaje = respuesta.mensaje || 'Retiro realizado correctamente.';
						if (typeof Swal !== 'undefined' && Swal.fire) {
							Swal.fire({ title: 'Retiro completado', text: mensaje, type: 'success', icon: 'success' })
								.then(function(){ window.location.reload(); });
						} else {
							window.location.reload();
						}
					},
						error: function(xhr){
						var mensaje = xhr.responseJSON && (xhr.responseJSON.mensaje_error || xhr.responseJSON.message)
							? (xhr.responseJSON.mensaje_error || xhr.responseJSON.message)
							: 'No fue posible realizar el retiro.';
						mostrarErrorRetiroPersonalizado(mensaje);
						cambiarEstadoBoton($boton, false, 'Retirar', 'Retirando...');
						$('#modal_retiro_personalizado select, #modal_retiro_personalizado .close, #modal_retiro_personalizado .btn-default').prop('disabled', false);
						$('.retiro-personalizado-select').trigger('change.select2');
						actualizarCantidadRetiroPersonalizado();
						if (typeof Swal !== 'undefined' && Swal.fire) {
							Swal.fire({ title: 'No se realizó el retiro', text: mensaje, type: 'error', icon: 'error' });
						}
					}
				});
			}

			$('#btn_ejecutar_retiro_personalizado').on('click', function(){
				var cantidad = actualizarCantidadRetiroPersonalizado();
				if (descripcionFiltrosRetiroPersonalizado() === '') {
					mostrarErrorRetiroPersonalizado('Debe seleccionar al menos un grupo, empleado o concepto.');
					return;
				}
				if (cantidad < 1) {
					mostrarErrorRetiroPersonalizado('No hay registros que coincidan con los filtros seleccionados.');
					return;
				}

				var texto = 'Se retirarán ' + cantidad + ' registro(s). ' + descripcionFiltrosRetiroPersonalizado() + '. Esta operación revertirá sus movimientos asociados.';

				if (typeof Swal !== 'undefined' && Swal.fire) {
					Swal.fire({
						title: '¿Realmente quiere ejecutar el retiro?',
						text: texto,
						type: 'warning',
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#d33',
						confirmButtonText: 'Sí, retirar',
						cancelButtonText: 'Cancelar'
					}).then(function(resultado){
						if (resultado.isConfirmed || resultado.value === true) {
							ejecutarRetiroPersonalizado();
						}
					});
					return;
				}

				if (window.confirm('¿Realmente quiere ejecutar el retiro?\n\n' + texto)) {
					ejecutarRetiroPersonalizado();
				}
			});

			var eliminarEmpleadoData = { url: null, contratoId: null };

			$(document).on('click', '.js-eliminar-empleado', function(event){
				event.preventDefault();
				var $btn = $(this);
				var url = $btn.attr('href');
				var contratoId = $btn.data('contrato-id');
				var nombreEmpleado = $btn.data('empleado-nombre') || $btn.closest('tr').find('td').eq(2).text().trim();

				eliminarEmpleadoData.url = url;
				eliminarEmpleadoData.contratoId = contratoId;

				$('#modal_confirmar_eliminar_empleado_texto').text('¿Realmente quiere retirar a ' + nombreEmpleado + ' del documento?');
				$('#modal_confirmar_eliminar_empleado').modal('show');
			});

			$('#btn_confirmar_eliminar_empleado').on('click', function(){
				if (!eliminarEmpleadoData.url) {
					return;
				}

				var $btnConfirmar = $(this);
				$btnConfirmar.prop('disabled', true);
				$btnConfirmar.find('.btn-text').text('Retirando...');
				$btnConfirmar.find('.btn-spinner').show();

				var url = eliminarEmpleadoData.url;
				var contratoId = eliminarEmpleadoData.contratoId;
				var $filaEmpleados = $('.js-eliminar-empleado[data-contrato-id="' + contratoId + '"]').closest('tr');

				$.ajax({
					url: url,
					type: 'GET',
					success: function(respuesta){
						$('#modal_confirmar_eliminar_empleado').modal('hide');
						$btnConfirmar.prop('disabled', false);
						$btnConfirmar.find('.btn-text').text('Retirar');
						$btnConfirmar.find('.btn-spinner').hide();
						if (respuesta && respuesta.tabla) {
							actualizarTablaEmpleados(respuesta);
						} else {
							$filaEmpleados.remove();
						}

						if (contratoId) {
							$('#tabla_registros_documento > tbody > tr[data-contrato-id="' + contratoId + '"]').remove();
						}
					},
					error: function(xhr){
						$('#modal_confirmar_eliminar_empleado').modal('hide');
						$btnConfirmar.prop('disabled', false);
						$btnConfirmar.find('.btn-text').text('Retirar');
						$btnConfirmar.find('.btn-spinner').hide();
						var mensaje = 'No se pudo retirar el empleado.';
						if (xhr.responseJSON && xhr.responseJSON.mensaje_error) {
							mensaje = xhr.responseJSON.mensaje_error;
						}
						$('#empleados-alerta').html('<div class="alert alert-danger">' + mensaje + '</div>');
					}
				});
			});

			$(document).on('input', '#buscar_registros_liquidacion', function(){
				var valor = $(this).val().toLowerCase();
				$('#tabla_registros_documento > tbody > tr').each(function(){
					var $fila = $(this);
					if ($fila.hasClass('fila-totales')) {
						$fila.removeClass('fila-oculta');
						return true;
					}
					var texto = ($fila.data('search') || '').toString();
					var match = texto.indexOf(valor) !== -1;
					$fila.toggleClass('fila-oculta', !match);
				});
			});

			function agregarEmpleadoATablaRegistros(empleado) {
				empleado = empleado || {};
				var contratoId = empleado.id;
				if (!contratoId) {
					return;
				}

				if ($('#tabla_registros_documento > tbody > tr[data-contrato-id="' + contratoId + '"]').length) {
					return;
				}

				var cc = empleado.identificacion || '';
				var nombre = empleado.nombre || '';

				var $tabla = $('#tabla_registros_documento');
				var conceptosCount = parseInt($tabla.data('conceptos-count') || '0', 10);

				var $tbody = $tabla.find('> tbody');
				var $filaTotales = $tbody.find('> tr.fila-totales');
				var $filas = $tbody.find('> tr').not('.fila-totales');
				var nro = $filas.length + 1;

				var $fila = $('<tr></tr>')
					.attr('data-contrato-id', contratoId)
					.attr('data-search', (nombre + ' ' + cc).toLowerCase());

				$fila.append('<td class="text-center sticky-col-1">' + nro + '</td>');
				$fila.append('<td class="text-left celda_nombre_empleado sticky-col-2">' + nombre + '</td>');
				$fila.append('<td class="text-center">' + cc + '</td>');

				for (var i = 0; i < conceptosCount; i++) {
					$fila.append('<td>' + monedaHtml(0) + '</td>');
				}

				$fila.append('<td>' + monedaHtml(0) + '</td>');
				$fila.append('<td>' + monedaHtml(0) + '</td>');
				$fila.append('<td>' + monedaHtml(0) + '</td>');
				$fila.append('<td class="celda_firma">&nbsp;</td>');

				if ($filaTotales.length) {
					$filaTotales.before($fila);
				} else {
					$tbody.append($fila);
				}
			}

			function retirarContratosDeTablaRegistros(contratosIds) {
				$.each(contratosIds, function(indice, contratoId){
					$('#tabla_registros_documento > tbody > tr[data-contrato-id="' + contratoId + '"]').remove();
				});
			}

			function monedaHtml(valor) {
				var formatted = Number(valor || 0).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
				return '<table style="width: 100%; margin: 0px;" class="texto_moneda"><tr><td width="5px" style="border: 0px !important;">$</td><td style="text-align: right; border: 0px !important; background-color: transparent !important;">' + formatted + '</td></tr></table>';
			}
</script>	
@endsection
