<?php
	
	$user = \Auth::user();

	if ( $user->hasRole('SuperAdmin') || $user->hasRole('Administrador') || $user->hasRole('Jefe de almacén') ) 
    {
    	$pdvs = App\VentasPos\Pdv::where([['estado','<>', 'Inactivo']])->get();
    }else{
    	$pdvs = App\VentasPos\Pdv::where( [['cajero_default_id','=', $user->id],['estado','<>', 'Inactivo']] )->get();
    }
?>


@extends('layouts.principal')

@section('estilos_2')
	<style>
		.tienda{

		}

		.tienda div.caja{
			border: 2px solid gray;
		    margin: -40px 10% 0px;
		    height: 220px;
		}

		.datos_pdv{
			padding: 10px;
		}

		table tr td {
			padding: 5px;
		}

	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<input type="hidden" id="ids_facturas" name="ids_faturas">
			
			<?php
				$cant_cols = 3;
				$i = $cant_cols;
		    ?>

			@foreach( $pdvs as $pdv )

				@if($i % $cant_cols == 0)
		            <div class="row">
		        @endif

					@include('ventas_pos.index_una_casita')

				<?php
					$i++;
				?>

				@if($i % $cant_cols == 0)
				</div>
				<br/><br/>
				@endif

			@endforeach

		</div>
	</div>

	@include('components.design.ventana_modal',['titulo'=>'','texto_mensaje'=>''])

	@include( 'components.design.ventana_modal2',[ 'titulo2' => '', 'texto_mensaje2' => '', 'clase_tamanio' => 'modal-lg' ] )

	<input type="hidden" name="convertir_facturas_pos_a_electronicas_en_acumulacion" id="convertir_facturas_pos_a_electronicas_en_acumulacion" value="{{ (int)config('ventas_pos.convertir_facturas_pos_a_electronicas_en_acumulacion') }}">

@endsection

@section('scripts')
	
<script type="text/javascript">
		
	var pdv_id;
	var continuar = true;
	var arr_ids_facturas;
	var restantes;
	var facturas_con_error = [];
	var facturas_para_reintentar_envio = [];
	var facturas_acumuladas = 0;

	$(document).ready(function(){

		var btn_acumular;

		$(".btn_acumular").click(function(event){

			// Desactivar el click del botón
			$( this ).attr( 'disabled', 'disabled');
			$( this ).off( event );

			$("#myModal").modal({backdrop: "static"});
			$("#div_spin").show();
			$("#myModal .close").hide();
			$(".btn_close_modal").hide();
			$(".btn_edit_modal").hide();
			$(".btn_save_modal").hide();

			btn_acumular = $(this);
			facturas_con_error = [];
			facturas_para_reintentar_envio = [];
			facturas_acumuladas = 0;

			$("#ids_facturas").val($(this).attr('data-ids_facturas'));
			
			$('#contenido_modal').html( '<h1 style="text-align:center;"> <small>Por favor espere</small> <br> Validando Existencias... </h1>' );

			validar_existencias();
		});	

		function validar_existencias()
		{
			pdv_id = btn_acumular.attr('data-pdv_id');

			$.ajax({
				type: "GET",
				url: "{{url('pos_factura_validar_existencias')}}" + "/" + pdv_id,
				async: true,
				success : function(data) {
					if ( data != 1 ) // Cuando falla la validacion. data = vista_html
					{
						$(".btn_close_modal").show();
						$("#ids_facturas").val('[]');
						$('#contenido_modal').html( '<h1 style="text-align:center;"> <small>Por favor espere</small>  <br> Validación de existencias: <i class="fa fa-remove"></i> </h1>' + data );
					}else{
						
						arr_ids_facturas = JSON.parse($("#ids_facturas").val());

						restantes = arr_ids_facturas.length;

						$('#contenido_modal').html( '<h1 style="text-align:center;"> <small>Por favor espere</small>  <br> Validación de existencias completada exitosamente: <i class="fa fa-check"></i> <br> Acumulando facturas POS... <span id="contador_facturas" style="color:#9c27b0">' + restantes + '</span> facturas restantes.</h1>' );

						// fires off the first call 
						getShelfRecursive();
					}
				},
				error : function( data, textStatus, xhr ) {
					$("#ids_facturas").val('[]');
					$(".btn_close_modal").show();
					$('#contenido_modal').html( '<h1 style="text-align:center;">  <small style="color:red;"> <i class="fa fa-times-circle"></i> Error en Validacion de existencias. </small> <br> Code: ' + data.status + '  <br> Status: ' + textStatus + " - " + xhr + ' </h1>' );
				}
			});

			return continuar;
		}
		
		function escapar_html(texto)
		{
			return $('<div>').text(texto == null ? '' : texto).html();
		}

		function mensaje_error_ajax(respuesta)
		{
			if (respuesta.responseJSON && respuesta.responseJSON.message) {
				return respuesta.responseJSON.message;
			}

			if (respuesta.responseText) {
				try {
					var contenido = JSON.parse(respuesta.responseText);
					if (contenido.message) {
						return contenido.message;
					}
				} catch (e) {
					// Las respuestas HTML no se muestran completas en el resumen.
				}
			}

			return 'Error HTTP ' + respuesta.status + '. Consulte el log para conocer el detalle.';
		}

		function registrar_error_factura(factura_id, etapa, respuesta)
		{
			facturas_con_error.push({
				id: factura_id,
				etapa: etapa,
				mensaje: mensaje_error_ajax(respuesta)
			});
		}

		function continuar_acumulacion()
		{
			restantes--;
			document.getElementById('contador_facturas').innerHTML = restantes;
			getShelfRecursive();
		}

		function procesar_conversion_electronica(factura_id, es_reintento)
		{
			$.ajax({
				type: 'GET',
				url: "{{url('pos_acumulacion_convertir_en_factura_electronica')}}" + "/" + factura_id,
				success: function() {
					if (es_reintento) {
						reintentar_envio_electronico_recursivo();
						return;
					}

					continuar_acumulacion();
				},
				error: function(respuesta) {
					var reintentable = respuesta.responseJSON && respuesta.responseJSON.reintentable;
					if (!es_reintento && reintentable) {
						facturas_para_reintentar_envio.push(factura_id);
						continuar_acumulacion();
						return;
					}

					registrar_error_factura(factura_id, 'conversión/envío electrónico', respuesta);
					if (es_reintento) {
						reintentar_envio_electronico_recursivo();
						return;
					}

					continuar_acumulacion();
				}
			});
		}

		function reintentar_envio_electronico_recursivo()
		{
			if (facturas_para_reintentar_envio.length === 0) {
				finalizar_acumulacion();
				return;
			}

			document.getElementById('contador_facturas').innerHTML = facturas_para_reintentar_envio.length;
			var factura_id = facturas_para_reintentar_envio.shift();
			procesar_conversion_electronica(factura_id, true);
		}

		function finalizar_acumulacion()
		{
			$('#div_spin').hide();

			if (facturas_con_error.length === 0) {
				location.reload();
				return;
			}

			var errores_acumulacion = facturas_con_error.filter(function(error) {
				return error.etapa === 'acumulación';
			});
			var errores_conversion = facturas_con_error.filter(function(error) {
				return error.etapa === 'conversión/envío electrónico';
			});

			var listado_errores = '<ul style="text-align:left; max-height:260px; overflow:auto;">';
			facturas_con_error.forEach(function(error) {
				listado_errores += '<li><strong>Factura ID ' + escapar_html(error.id) + '</strong> (' +
					escapar_html(error.etapa) + '): ' + escapar_html(error.mensaje) + '</li>';
			});
			listado_errores += '</ul>';

			var resumen_errores = '';
			if (errores_acumulacion.length > 0) {
				resumen_errores += '<br><strong style="color:#d32f2f;">' + errores_acumulacion.length +
					'</strong> facturas no se acumularon y permanecen pendientes.';
			}
			if (errores_conversion.length > 0) {
				resumen_errores += '<br><strong style="color:#d32f2f;">' + errores_conversion.length +
					'</strong> facturas se convirtieron o enviaron con error y quedaron identificadas para reintento.';
			}

			$('#contenido_modal').html(
				'<h2 style="text-align:center;">Acumulación finalizada</h2>' +
				'<p style="text-align:center;"><strong>' + facturas_acumuladas + '</strong> facturas acumuladas correctamente.' +
				resumen_errores + '</p>' +
				listado_errores +
				'<p style="text-align:center;"><button type="button" class="btn btn-primary" onclick="location.reload();">Actualizar pantalla</button></p>'
			);
			$('.btn_close_modal').show();
			$('#myModal .close').show();
		}

		// Procesa una factura a la vez para no sobrecargar el servidor. Un error
		// individual se registra y no detiene las facturas restantes.
		function getShelfRecursive() {
			
			// terminate if array exhausted 
			if (arr_ids_facturas.length === 0) 
			{
				if (facturas_para_reintentar_envio.length > 0) {
					$('#contenido_modal h1').html('<small>Por favor espere</small><br>Reintentando envíos electrónicos pendientes... <span id="contador_facturas" style="color:#9c27b0">' + facturas_para_reintentar_envio.length + '</span> facturas restantes.');
					reintentar_envio_electronico_recursivo();
					return;
				}

				finalizar_acumulacion();
				return; 
			}

			// pop top value 
			var factura_id = arr_ids_facturas[0]; 
			arr_ids_facturas.shift(); 
			
			$.ajax({
				type: 'GET',
				url: "{{url('pos_acumular_una_factura')}}" + "/" + factura_id,
				success: function() {
					facturas_acumuladas++;

					if ($('#convertir_facturas_pos_a_electronicas_en_acumulacion').val() == 0) {
						continuar_acumulacion();
						return;
					}

					procesar_conversion_electronica(factura_id, false);
				},
				error: function(respuesta) {
					registrar_error_factura(factura_id, 'acumulación', respuesta);
					continuar_acumulacion();
				}
			});
		}

		$(document).on('click',".btn_consultar_facturas",function(event){
			event.preventDefault();

			$('#contenido_modal2').html('');
			$('#div_spin2').fadeIn();

			$("#myModal2").modal(
				{backdrop: "static"}
			);

			$("#myModal2 .modal-title").text('Consulta de ' + $(this).attr('data-lbl_ventana'));

			$("#myModal2 .btn_edit_modal").hide();
			$("#myModal2 .btn_save_modal").hide();
			
			var url = "{{ url('pos_consultar_documentos_pendientes') }}" + "/" + $(this).attr('data-pdv_id') + "/" + $(this).attr('data-fecha_primera_factura') + "/" + $(this).attr('data-fecha_hoy') + "?view=" + $(this).attr('data-view') + "&id=20";

			$.get( url, function( respuesta ){
				$('#div_spin2').hide();
				$('#contenido_modal2').html( respuesta );
			});
		});

	});
</script>
@endsection
