@extends('layouts.principal')

<?php  
	$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
?>

@section('content')
	
	{{ Form::bsMigaPan($miga_pan) }}
	
	<div class="row">
		<div class="col-md-4">
			<div class="btn-group">
				
				{{ Form::bsBtnCreate( 'web/create' . $variables_url ) }}
				
				@if( $orden_de_trabajo->estado != 'Anulado' )
				    <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-btn fa-close"></i></button>
				@endif
				
			</div>
		</div>

		<div class="col-md-4 text-center">
			<div class="btn-group">
				Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar'],null, [ 'id' =>'formato_impresion_id' ]) }}
				{{ Form::bsBtnPrint( 'nom_ordenes_trabajo_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
			</div>			
		</div>

		<div class="col-md-4">	
			<div class="btn-group pull-right">
				{!! $botones_anterior_siguiente->dibujar( 'nom_ordenes_trabajo/', $variables_url ) !!}
			</div>			
		</div>	

	</div>
	
	<hr>
	<div class="row">
		@yield('cabecera')
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong>¡ADVERTENCIA!</strong>
		La anulación no puede revertirse. Si quieres confirmar, hacer click en: <a class="btn btn-danger btn-sm" href="{{ url( 'nom_ordenes_trabajo_anular/'.$id.$variables_url ) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i> Anular </a>
	</div>

	<div class="container-fluid">
		<div class="marco_formulario">

			<br><br>
			<div class="table-responsive">
				@yield('informacion_antes_encabezado')
				<table class="table table-bordered">
			        <tr>
			            <td width="50%" style="border: solid 1px #ddd; margin-top: -40px;">
			                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
			            </td>
			            <td style="border: solid 1px #ddd; padding-top: -20px;">
			                <div style="vertical-align: center;">
			                    <b style="font-size: 1.6em; text-align: center; display: block;">{{ $orden_de_trabajo->tipo_trasaccion->descripcion }}</b>
			                    <br/>
			                    <b>Documento:</b> {{ $orden_de_trabajo->tipo_documento_app->prefijo . ' ' . $orden_de_trabajo->consecutivo }}
			                    <br/>
			                    <b>Fecha:</b> {{ $orden_de_trabajo->fecha }}
			                    <br/>
			                    <b>Proyecto:</b> {{ $orden_de_trabajo->documento_nomina->descripcion }}

			                    @yield('datos_adicionales_encabezado')
			                    
			                </div>
			                @if($orden_de_trabajo->estado == 'Anulado')
			                    <div class="alert alert-danger" class="center">
			                        <strong>Documento Anulado</strong>
			                    </div>
			                @endif
			            </td>
			        </tr>
			        <tr>
				        <td colspan="2" style="border: solid 1px #ddd;">
				            <b>Tercero:</b> {{ $orden_de_trabajo->tercero->descripcion }}
				             / {{ number_format( $orden_de_trabajo->tercero->numero_identificacion, 0, ',', '.') }}
				            <br/>
				            <b>Ubicación desarollo actividad : &nbsp;&nbsp;</b> {{ $orden_de_trabajo->ubicacion_desarrollo_actividad }}
				        </td>
				    </tr>
				    <tr>        
				        <td colspan="2" style="border: solid 1px #ddd;">
				            <b>Detalle: &nbsp;&nbsp;</b> {{ $orden_de_trabajo->descripcion }}
				        </td>
				    </tr>

			    </table>

			</div>

			{!! $documento_vista !!}
			
			<br>

			<div style="text-align: right;">
			    Creado por: {{ explode('@',$orden_de_trabajo->creado_por)[0] }}, {{ $orden_de_trabajo->created_at }}
			    @if( $orden_de_trabajo->modificado_por != 0)
				    <br>
				    Modificado por: {{ explode('@',$orden_de_trabajo->modificado_por)[0] }}
				@endif
			</div>

		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')

	@yield('otros_scripts')

	<script type="text/javascript">

		$(document).ready(function(){
			$('#btn_print').focus();

			$('.select2').select2();

			$('#btn_print').animate( {  borderSpacing: 45 }, {
			    step: function(now,fx) {
			      $(this).css('-webkit-transform','rotate('+now+'deg)'); 
			      $(this).css('-moz-transform','rotate('+now+'deg)');
			      $(this).css('transform','rotate('+now+'deg)');
			    },
			    duration:'slow'
			},'linear');

			$('#btn_print').animate({  borderSpacing: 0 }, {
			    step: function(now,fx) {
			      $(this).css('-webkit-transform','rotate('+now+'deg)'); 
			      $(this).css('-moz-transform','rotate('+now+'deg)');
			      $(this).css('transform','rotate('+now+'deg)');
			    },
			    duration:'slow'
			},'linear');

			$('#btn_anular').on('click',function(e){
				e.preventDefault();
				$('.alert.alert-warning').show(1000);
			});

			$('#close').on('click',function(e){
				e.preventDefault();
				$('.alert.alert-warning').hide(1000);
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

			$(".btn_editar_registro").click(function(event){

		        $("#myModal").modal({backdrop: "static"});
		        $("#div_spin").show();
		        $(".btn_edit_modal").hide();

		        var url = '../nom_ordenes_trabajo_get_formulario_edit_registro';

				$.get( url, { 
								linea_registro_id: $(this).attr('data-linea_registro_id'), 
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


			$('#btn_guardar_documento_inventario').click(function(event){
				event.preventDefault();

				if ( $('#inv_bodega_id').val() == '' )
	        	{
	        		alert('Debe seleccionar una bodega');
	        		$('#inv_bodega_id').focus();
	        		return false;
	        	}

	        	// Estas lineas se eliminan en InventarioController
	        	$('#ingreso_productos').find('tbody:first').prepend('<tr id="0"> <td>0</td> <td class="nom_prod">0</td> <td><span style="color:white;">0</span><input type="hidden" class="movimiento" value="0"></td>0<td class="cantidad">0</td>0<td></tr>');
	        	$('#ingreso_productos').find('tbody:last').append('<tr id="0"> <td>0</td> <td class="nom_prod">0</td> <td><span style="color:white;">0</span><input type="hidden" class="movimiento" value="0"></td>0<td class="cantidad">0</td>0<td></tr>');

				var table = $('#ingreso_productos').tableToJSON();
				$('#movimiento').val(JSON.stringify(table));

				// Desactivar el click del botón
				$( this ).off( event );
				$('#form_create').submit();
			});

		});
	</script>

	<script src="{{ asset( 'assets/js/modificar_con_doble_click.js' ) }}"></script>
@endsection