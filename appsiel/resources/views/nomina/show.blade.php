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
				&nbsp;&nbsp;&nbsp; {{ Form::bsBtnDropdown( 'Retirar', 'warning', 'history', 
						[ 
							['link' => 'nomina/retirar_liquidacion/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 'etiqueta' => 'Registros automáticos (todo)' ],
							['link' => 'nom_retirar_prima_antiguedad/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
							'etiqueta' => 'Primas de antigüedad']
						] ) }}
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
				$btnGuardar.prop('disabled', true);
				$btnGuardar.find('.btn-text').text('Guardando...');
				$btnGuardar.find('.btn-spinner').show();

				$.ajax({
					url: $form.attr('action'),
					type: 'POST',
					data: $form.serialize(),
					success: function(respuesta){
						actualizarTablaEmpleados(respuesta);
						agregarEmpleadoATablaRegistros();
						$('#registro_modelo_hijo_id option:selected').remove();
						$('#form-asignar-empleado').find('input[type="text"]').val('');
						$('#form-asignar-empleado').find('.custom-combobox-input').val('');
						$btnGuardar.prop('disabled', false);
						$btnGuardar.find('.btn-text').text('Guardar');
						$btnGuardar.find('.btn-spinner').hide();
					},
					error: function(xhr){
						var mensaje = 'No se pudo agregar el empleado.';
						if (xhr.responseJSON && xhr.responseJSON.message) {
							mensaje = xhr.responseJSON.message;
						}
						$('#empleados-alerta').html('<div class="alert alert-danger">' + mensaje + '</div>');
						$btnGuardar.prop('disabled', false);
						$btnGuardar.find('.btn-text').text('Guardar');
						$btnGuardar.find('.btn-spinner').hide();
					}
				});
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

			function agregarEmpleadoATablaRegistros() {
				var $select = $('#registro_modelo_hijo_id');
				var contratoId = $select.val();
				if (!contratoId) {
					return;
				}

				if ($('#tabla_registros_documento > tbody > tr[data-contrato-id="' + contratoId + '"]').length) {
					return;
				}

				var texto = $select.find('option:selected').text().trim();
				var partes = texto.split(' ');
				var cc = partes.shift() || '';
				var nombre = partes.join(' ').trim();

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

			function monedaHtml(valor) {
				var formatted = Number(valor || 0).toLocaleString('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
				return '<table style="width: 100%; margin: 0px;" class="texto_moneda"><tr><td width="5px" style="border: 0px !important;">$</td><td style="text-align: right; border: 0px !important; background-color: transparent !important;">' + formatted + '</td></tr></table>';
			}
</script>	
@endsection
