<?php
	$variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;

	$user = \Illuminate\Support\Facades\Auth::user();
?>

@extends('transaccion.show')

@section('botones_acciones')

	<!-- @ can('vtas_bloquear_vista_index')
		
	@ else
		
	@ endcan
	-->
	@if( !$user->hasRole('SupervisorCajas') )
		{{ Form::bsBtnCreate( 'vtas_pedidos/create'.$variables_url ) }}

		@if( $doc_encabezado->estado == 'Pendiente')
			{{ Form::bsBtnEdit2( 'vtas_pedidos/' . $id . '/edit' . $variables_url . '&action=edit' ,'Editar') }}
		@endif

	@endif
	
	@if( $doc_encabezado->estado != 'Anulado' && $doc_encabezado->estado == 'Pendiente')
		@can('vtas_pedidos_anular')
			<button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-btn fa-close"></i></button>
		@endcan
	@endif

@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['pos'=>'POS','estandar'=>'Estándar','estandar2'=>'Estándar v2'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'vtas_pedidos_imprimir/'.$id.$variables_url.'&formato_impresion_id=pos' ) }}
	{{ Form::bsBtnEmail( 'vtas_pedidos_enviar_por_email/'.$id.$variables_url.'&formato_impresion_id=1' ) }}

	@if( (int)config('ventas_pos.imprimir_pedidos_en_cocina') )
		<div class="col">
			<br><br>
			<button class="btn btn-success btn-sm" id="btn_imprimir_en_cocina"><i class="fa fa-btn fa-print"></i> Imprimir en Cocina </button>
			
			<input type="hidden" id="impresora_cocina_por_defecto" name="impresora_cocina_por_defecto" value="{{ config('ventas_pos.impresora_cocina_por_defecto') }}">

			<input type="hidden" id="tamanio_letra_impresion_items_cocina" name="tamanio_letra_impresion_items_cocina" value="{{ config('ventas_pos.tamanio_letra_impresion_items_cocina') }}">

			<input type="hidden" id="lbl_consecutivo_doc_encabezado" value="{{ $doc_encabezado->consecutivo }}">
			<input type="hidden" id="lbl_fecha" value="{{ $doc_encabezado->fecha }}">
			<input type="hidden" id="lbl_cliente_descripcion" value="{{ $doc_encabezado->tercero_nombre_completo }}">
			<input type="hidden" id="lbl_descripcion_doc_encabezado" value="{{ $doc_encabezado->descripcion }}">
			<input type="hidden" id="lbl_total_factura" value="{{ '$ ' . number_format($doc_encabezado->valor_total,0,',','.') }}">
			<input type="hidden" id="nombre_vendedor" value="{{ $doc_encabezado->vendedor->tercero->descripcion }}">

		</div>
	@endif	
@endsection

@section('botones_anterior_siguiente')

	@if( !$user->hasRole('SupervisorCajas') )
		{!! $botones_anterior_siguiente->dibujar( 'vtas_pedidos/', $variables_url ) !!}
	@endif
	
@endsection

@section('datos_adicionales_encabezado')
	<br />
	<b>Fecha Entrega: </b> {{ explode(' ', $doc_encabezado->fecha_entrega )[0] }} <!-- -->

	@if( !$user->hasRole('SupervisorCajas') )
		
		@if( !is_null( $doc_encabezado->documento_ventas_padre() ) )
			<br>
			<b>{{ $doc_encabezado->documento_ventas_padre()->tipo_transaccion->descripcion }}: &nbsp;&nbsp;</b> {!! $doc_encabezado->documento_ventas_padre()->enlace_show_documento() !!}
		@endif

		<?php  
			//dd( $doc_encabezado, $doc_encabezado->documento_ventas_hijo() );
		?>
		@if( !is_null( $doc_encabezado->documento_ventas_hijo() ) )
			<br>
			<b>{{ $doc_encabezado->documento_ventas_hijo()->tipo_transaccion->descripcion }}: &nbsp;&nbsp;</b> {!! $doc_encabezado->documento_ventas_hijo()->enlace_show_documento() !!}
		@endif

		<br>
		<b>Remisiones: </b> {!! $doc_encabezado->enlaces_remisiones_hijas() !!}
	@endif

	
@endsection

@section('filas_adicionales_encabezado')
	<tr>
		<td style="border: solid 1px #ddd;">
			<b>Cliente: </b> {{ $doc_encabezado->tercero_nombre_completo }}
			<br>
			<b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			
			@if( config("configuracion.tipo_identificador") == 'NIT') 
				{{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	
			@else 
				{{ $doc_encabezado->numero_identificacion}} 
			@endif
            <br/>
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
            <br/>
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
		</td>
		<td style="border: solid 1px #ddd;">
			<b>Vendedor: </b> {{ $doc_encabezado->vendedor->tercero->descripcion }}
			<br>
			<b>Fecha creación: </b> {{ $doc_encabezado->created_at }}
			<br>
			<b>Fecha modificación: </b> {{ $doc_encabezado->updated_at }}
			<br>
			@if( !is_null($doc_encabezado->contacto_cliente) )
				<b>Contacto: </b> {{ $doc_encabezado->contacto_cliente->tercero->descripcion }}
				<br>
				<b>Tel: </b> {{ $doc_encabezado->contacto_cliente->tercero->telefono1 }}
				<br>
				<b>Email: </b> {{ $doc_encabezado->contacto_cliente->tercero->email }}
			@endif
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<b>Detalle: </b> {!! $doc_encabezado->descripcion !!}
		</td>
	</tr>
@endsection

@section('div_advertencia_anulacion')
	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong>¡ADVERTENCIA!</strong>
		La anulación no puede revertirse. Si quieres confirmar, hacer click en: <a class="btn btn-danger btn-sm" href="{{ url( 'vtas_pedidos_anular/'.$id.$variables_url ) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i> Anular </a>
	</div>
@endsection

@section('documento_vista')
	<p style="color: red;">
		Nota: Las cantidades pendientes se van actualizando a medida que se hagan la remisiones.
	</p>
	<div class="table-responsive">
		<table class="table table-bordered table-striped">
			{{ Form::bsTableHeader(['No.','Ítem','Cant.','Cant. Pend.','Vr. unitario','IVA','Total Bruto','Total Dcto.','Total','']) }}
			<tbody>
				<?php
				$i = 1;
				$total_cantidad = 0;
				$subtotal = 0;
				$total_impuestos = 0;
				$total_factura = 0;
                $total_descuentos = 0;
				$array_tasas = [];

				$impuesto_iva = 0;//iva en firma
				?>
				@foreach($doc_registros as $linea )
					<tr class="linea_registro">
						<td class="text-center"> {{ $i }} </td>
						<td width="250px" class="lbl_producto_descripcion"> {{ $linea->item->get_value_to_show() }} </td>
						<td class="text-center">
							{{ number_format( $linea->cantidad, 0, ',', '.') }}
							<span class="cantidad" style="display: none;">{{$linea->cantidad}}</span>
						</td>
						<td class="text-center"> {{ number_format( $linea->cantidad_pendiente, 0, ',', '.') }} </td>
						<td  class="text-right"> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) , 0, ',', '.') }} </td>
						<td class="text-center"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
						<td  class="text-right"> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) * $linea->cantidad, 0, ',', '.') }} </td>
						<td  class="text-right"> {{ '$ '.number_format( $linea->valor_total_descuento, 0, ',', '.') }} </td>
						<td  class="text-right">
							{{ '$ '.number_format( $linea->precio_total, 0, ',', '.') }}
							<span class="precio_total" style="display: none;">{{$linea->precio_total}}</span>
						</td>
	                    <td>
	                        @if( $doc_encabezado->estado == 'Pendiente' && !$user->hasRole('SupervisorCajas') )
	                            <button class="btn btn-warning btn-xs btn-detail btn_editar_registro" type="button" title="Modificar" data-linea_registro_id="{{$linea->id}}"><i class="fa fa-btn fa-edit"></i>&nbsp; </button>

	                            @include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
	                        @endif
	                    </td>
					</tr>
					<?php
					$i++;
					$total_cantidad += $linea->cantidad;
					$total_impuestos += (float) $linea->valor_impuesto * (float) $linea->cantidad;
					$total_factura += $linea->precio_total;
	                $total_descuentos += $linea->valor_total_descuento;

					// Si la tasa no está en el array, se agregan sus valores por primera vez
					if (!isset($array_tasas[$linea->tasa_impuesto])) {
						// Clasificar el impuesto
						$array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA ' . $linea->tasa_impuesto . '%';
						if ($linea->tasa_impuesto == 0) {
							$array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA 0%';
						}
						// Guardar la tasa en el array
						$array_tasas[$linea->tasa_impuesto]['tasa'] = $linea->tasa_impuesto;


						// Guardar el primer valor del impuesto y base en el array
						$array_tasas[$linea->tasa_impuesto]['precio_total'] = (float) $linea->precio_total;
						$array_tasas[$linea->tasa_impuesto]['base_impuesto'] = (float) $linea->base_impuesto * (float) $linea->cantidad;
						$array_tasas[$linea->tasa_impuesto]['valor_impuesto'] = (float) $linea->valor_impuesto * (float) $linea->cantidad;
					} else {
						// Si ya está la tasa creada en el array
						// Acumular los siguientes valores del valor base y valor de impuesto según el tipo
						$precio_total_antes = $array_tasas[$linea->tasa_impuesto]['precio_total'];
						$array_tasas[$linea->tasa_impuesto]['precio_total'] = $precio_total_antes + (float) $linea->precio_total;
						$array_tasas[$linea->tasa_impuesto]['base_impuesto'] += (float) $linea->base_impuesto * (float) $linea->cantidad;
						$array_tasas[$linea->tasa_impuesto]['valor_impuesto'] += (float) $linea->valor_impuesto * (float) $linea->cantidad;
					}

					if($linea->valor_impuesto > 0){
						$impuesto_iva = $linea->tasa_impuesto;
					}
					?>
				@endforeach
				<?php
					$subtotal = $total_factura + $total_descuentos - $total_impuestos;
				?>
			</tbody>
		</table>
	</div>

	@include('ventas.incluir.factura_firma_totales')

	@include('ventas.incluir.registros_anticipos_cliente',['cliente'=>$doc_encabezado->cliente])
@endsection

@section('footer')
	@if( $doc_encabezado->lineas_registros->sum('cantidad_pendiente') > 0 && $doc_encabezado->estado != 'Anulado' && $doc_encabezado->estado != 'Facturado' )

		@if( !$user->hasRole('SupervisorCajas') && !$user->hasRole('Vendedor'))
			@include('ventas.pedidos.formulario_vista_show_pedidos')
		@endif
		
	@endif
@endsection

@section('otros_scripts')

	@if( (int)config('ventas_pos.imprimir_pedidos_en_cocina') )
		<script src="{{ asset( 'assets/js/ventas_pos/external_print/cptable.js' )}}"></script>
		<script src="{{ asset( 'assets/js/ventas_pos/external_print/cputils.js' )}}"></script>
		<script src="{{ asset( 'assets/js/ventas_pos/external_print/JSESCPOSBuilder.js' )}}"></script>
		<script src="{{ asset( 'assets/js/ventas_pos/external_print/JSPrintManager.js' )}}"></script>
		<script src="{{ asset( 'assets/js/ventas/pedidos/script_to_printer.js?aux=' . uniqid() )}}"></script>
    @endif

	<script type="text/javascript">
		var array_registros = [];
		var cliente = <?php echo $cliente; ?>;

		
		$.fn.actualizar_medio_recaudo = function () {
			var abono = 0.0;
			//abono = 0.0;
			$('.valor_total').each(function()
			{
				var cadena = $(this).text();
				abono += parseFloat(cadena.substring(1));
			});

			$("#abono").val( abono );
		};
		
		$(document).ready(function() {

			calcular_saldo_pendiente_documento();

			// Almacenar la factura
			$("#btn_generar").on('click',function(event){
				event.preventDefault();

				if ( $('#forma_pago').val() == 'contado') {
					
					if ( $('#total_valor_total').text()=='$0.00' ) {
						Swal.fire({
							icon: 'error',
							title: 'Alerta!',
							text: 'No ha ingresado ningún registro de Medios de pago para la Factura de Contado.'
						});

						return false;
					}
					
					var valor_total_recaudos = parseFloat( $('#total_valor_total').text().substring(1) );
					if (  valor_total_recaudos != parseFloat( $('#vlr_total_factura').text() ) ) {
						Swal.fire({
							icon: 'error',
							title: 'Alerta!',
							text: 'El Valor total de los registros de Medios de pago es diferente al Valor total del Pedido.'
						});

						return false;
					}
				}


				if ( parseFloat( $('#vlr_total_factura').text() ) < $('#abono').val() )
				{
					Swal.fire({
						icon: 'error',
						title: 'Alerta!',
						text: 'El Valor total de los registros de Medios de pago no puede ser mayor que el Valor total del Pedido.'
					});

					return false;
				}
				
				var tabla_recaudos = $('#ingreso_registros_medios_recaudo').tableToJSON();
				$('#lineas_registros_medios_recaudo').val( JSON.stringify(tabla_recaudos) );

				$('#form_create').submit();
			});

			$(".btn_editar_registro").click(function(event){

		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();

		        var url = '../vtas_pedidos_get_formulario_edit_registro';

				$.get( url, { 
								linea_registro_id: $(this).attr('data-linea_registro_id'),  
								modelo_editar: $(this).attr('data-modelo_editar'),
								id: getParameterByName('id'), 
								id_modelo: getParameterByName('id_modelo'), 
								id_transaccion: getParameterByName('id_transaccion')
							} )
					.done(function( data ) {

						$('#saldo_original').val( $('#saldo_a_la_fecha').val() );
						$('#cantidad_original').val( $('#cantidad').val() );

		                $('#contenido_modal').html(data);

		                $("#div_spin").hide();

		                $('#precio_unitario').select();

					});		        
		    });

		    // Al modificar el precio 
	        $(document).on('keyup','#precio_unitario',function(event){
				
				if( validar_input_numerico( $(this) ) )
				{	

					var x = event.which || event.keyCode;
					if( x==13 )
					{
						$('#cantidad').select();				
					}

					calcular_valor_descuento();

					calcular_precio_total();

				}else{
					$(this).focus();
					return false;
				}

			});

		    // Al modificar la cantidad
	        $(document).on('keyup','#cantidad',function(event){
				
				if( validar_input_numerico( $(this) ) )
				{
					if ( !validar_cantidad_pendiente() )
					{
						return false;
					}

					var x = event.which || event.keyCode;
					if( x==13 )
					{
						$('#tasa_descuento').select();
					}

					calcular_valor_descuento();

					calcular_precio_total();
					
				}else{
					$(this).focus();
					return false;
				}

			});

	        $(document).on('keyup','#tasa_descuento',function(event){
	        	if( validar_input_numerico( $(this) ) )
				{	
					// máximo valor de 100
					if ( $(this).val() > 100 )
					{ 
						$(this).val(100);
					}

					var x = event.which || event.keyCode;
					if( x == 13 )
					{
						$('.btn_save_modal').focus();
						return true;
					}
					
					calcular_valor_descuento();

					calcular_precio_total();

				}else{

					$(this).focus();
					return false;
				}
			});

			$('#generar').on('change',function(){
				
				if ( $(this).val() == 'remision_desde_pedido' )
				{
					$('#form_create').attr('action', url_raiz + '/' + 'vtas_form_crear_remision_desde_doc_venta?id=13&id_modelo=164&id_transaccion=24&crear_remision_desde_pedido=yes&doc_ventas_id=' + $('#doc_encabezado_id').val() );
				}else{
					$('#form_create').attr('action', url_raiz + '/' + 'vtas_crear_remision_y_factura_desde_doc_venta?id=13&id_modelo=164&id_transaccion=24&crear_remision_desde_pedido=yes&doc_ventas_id=' + $('#doc_encabezado_id').val() );
				}
			});

			$('#forma_pago').on('change',function(){		
				
				$('#ingreso_registros_medios_recaudo').find('tbody').html('');
				$('#total_valor_total').text('$0.00');
				
				if ( $(this).val() == 'contado' )
				{
					$('#abono').parent().parent().hide();
					$('#abono').val(0);
				}else{
					$('#abono').parent().parent().show();
					$('#abono').val(0);
				}
			});			

			function calcular_valor_descuento()
			{
				var valor_total_descuento = $('#precio_unitario').val() * $('#tasa_descuento').val() / 100 * $('#cantidad').val();

				$('#valor_total_descuento_no').val( valor_total_descuento );
				$('#valor_total_descuento').val( valor_total_descuento );
			}

			function calcular_precio_total()
			{
				var valor_total_descuento = parseFloat( $('#valor_total_descuento').val() );

				var precio_unitario = parseFloat( $('#precio_unitario').val() );

				var cantidad = parseFloat( $('#cantidad').val() );
				
				var precio_total = precio_unitario * cantidad - valor_total_descuento;

				$('#precio_total').val( precio_total );
			}

	        $('.btn_save_modal').click(function(event){

	        	if ( !validar_cantidad_pendiente() )
				{
					return false;
				}

	        	if ( $.isNumeric( $('#precio_total').val() ) )
	        	{
	                validacion_saldo_movimientos_posteriores();
	        	}else{
	        		alert('El precio total es incorrecto. Verifique lo valores ingresados.');
	        	}
	        });

	        $("#myModal").on('hide.bs.modal', function(){
	            $('#popup_alerta_danger').hide();
	        });

			/*
				validar_existencia_actual
			*/
			function validar_existencia_actual()
			{
				if ( $('#tipo').val() == 'servicio' ) { return true; }

				if ( parseFloat( $('#saldo_a_la_fecha').val() ) < 0 ) 
				{
					alert('Nueva EXISTENCIA negativa.');
					$('#cantidad').val('');
					$('#cantidad').focus();
					return false;
				}
				return true;
			}

			function validar_cantidad_pendiente()
			{
				console.log( $('#doc_encabezado_cotizacion_id').val() );
				if ( $('#doc_encabezado_cotizacion_id').val() != 0 )
				{
					if ( parseFloat( $('#cantidad').val() ) > parseFloat( $('#cantidad_pendiente').val() ) ) 
					{
						alert('Cantidad no puede ser mayor a la cantidad pendiente.');
						$('#cantidad').val('');
						$('#cantidad').focus();
						return false;
					}
				}					

				return true;
			}
            
            function validacion_saldo_movimientos_posteriores()
            {
            	$('.btn_save_modal').off( 'click' );
                $('#popup_alerta_danger').hide();
                $('#form_edit').submit();
            }

			function calcular_saldo_pendiente_documento()
			{
				var total_anticipos = 0;
				$(".col_saldo_pendiente").each(function() {
					total_anticipos += parseFloat( $(this).text().replace(".","") );
				});

				$('#div_total_anticipos').attr('data-vlr_total_anticipos',total_anticipos);

				$('#div_total_anticipos').text( '$ ' + total_anticipos);

				var saldo_pendiente_documento = $('#vlr_total_factura').text() - total_anticipos;

				$('#div_saldo_pendiente_documento').attr('data-vlr_saldo_pendiente_documento',saldo_pendiente_documento);

				$('#div_saldo_pendiente_documento').text( '$ ' + saldo_pendiente_documento);

			}
	            
			array_registros = <?php echo json_encode($doc_registros); ?>;
		});


		function calcular(id) {
			var arraytotal = [];
			var arrayimp = [];
			var arrayc = [];
			var arraytotalbruto = [];
			var nuevoimp = 0;
			var sbtotal = 0;
			var totalc = 0;
			var totalt = 0;
			var vu = $("input:text[name=dpreciounitario_" + id + "]").val();
			var cant = $("input:text[name=dcantidad_" + id + "]").val();
			var bruto = Math.round(parseFloat(vu) * parseFloat(cant));
			$("input:text[name=dprecio_bruto_" + id + "]").val(bruto);
			var iva = $("input:text[name=dimpuesto_" + id + "]").val();
			var total = Math.round(bruto + (bruto * (iva / 100)));
			$("input:text[name=dpreciototal_" + id + "]").val(total);
			$(".cant").each(function() {
				arrayc.push($(this).val());
				totalc = totalc + parseFloat($(this).val());
			});
			$(".total").each(function() {
				arraytotal.push($(this).val());
			});
			$(".imp").each(function() {
				arrayimp.push($(this).val());
			});
			$(".valor_bruto").each(function() {
				arraytotalbruto.push($(this).val());
			});
			arraytotal.forEach(function(value, index) {
				totalt = totalt + parseFloat(value);
				sbtotal = sbtotal + parseFloat(arraytotalbruto[index]);
				nuevoimp = nuevoimp + (arraytotalbruto[index] * (arrayimp[index] / 100));
			});
			$("#tbtotal").html("$ " + Math.round(totalt));
			$("#tbcant").html(totalc);
			$("#tbstotal").html("$ " + Math.round(sbtotal));
			$("#tbimpuesto").html("$ " + Math.round(nuevoimp));
		}


		function enviar() {
			var linea_reg = [];
			$(".total").each(function() {
				var prod = $(this).parent('td').prev().children('input').attr('id');
				linea_reg.push(llenar_objeto(prod));
			});
			$('#lineas_registros').val(JSON.stringify(linea_reg));
			$("#remision").submit();
		}

		function llenar_objeto(id) {
			var o = new Object();
			array_registros.forEach(function(value, index) {
				if (id == value.id) {
					o['inv_motivo_id'] = value.vtas_motivo_id;
					o['inv_bodega_id'] = cliente.inv_bodega_id;
					o['inv_producto_id'] = value.producto_id;
					var precio_unitario = $("input:text[name=dpreciounitario_" + id + "]").val();
					var cantidad = $("input:text[name=dcantidad_" + id + "]").val();
					var costo_unitario = parseFloat(precio_unitario) / (1 + (parseFloat(value.tasa_impuesto) / 100));
					o['costo_unitario'] = costo_unitario;
					o['cantidad'] = cantidad;
					o['costo_total'] = Math.round($("input:text[name=dpreciototal_" + id + "]").val());
				}
			});
			return o;
		}
	</script>

	<script type="text/javascript" src="{{asset( 'assets/js/tesoreria/medios_recaudos.js?aux=' . uniqid())}}"></script>
@endsection