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
			{!! $parametros['titulo'] !!}
			<hr>

			{!! Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true])  !!}

		<!--
					// NOTA: La variable que no sea enviada en el request (a través de un input) será borrada del archivo de configuración
        			// Si se quiere agregar una nueva variable al archivo de configuración, hay que agregar también un campo nuevo a este formulario
		    	-->

			{{ Form::hidden('titulo', $parametros['titulo'] ) }}

			<h4> Parámetros por defecto Tienda Online  </h4>
			<hr>
			<div class="row">

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						<?php
						if( isset($parametros['lista_precios_id'] ) )
						{
							$lista_precios_id = $parametros['lista_precios_id'];
						}else{
							$lista_precios_id = 1;
						}
						?>
						{{ Form::bsSelect('lista_precios_id', $lista_precios_id, 'Lista de precios', App\Ventas\ListaPrecioEncabezado::opciones_campo_select(), ['class'=>'form-control']) }}
					</div>
				</div>

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						<?php
						if( isset($parametros['lista_descuentos_id'] ) )
						{
							$lista_descuentos_id = $parametros['lista_descuentos_id'];
						}else{
							$lista_descuentos_id = 24;
						}
						?>
						{{ Form::bsSelect('lista_descuentos_id', $lista_descuentos_id, 'Lista de descuentos', App\Ventas\ListaDctoEncabezado::opciones_campo_select(), ['class'=>'form-control']) }}
					</div>
				</div>

			</div>

			<div class="row">

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						<?php
						if( isset($parametros['clase_cliente_id'] ) )
						{
							$clase_cliente_id = $parametros['clase_cliente_id'];
						}else{
							$clase_cliente_id = 1;
						}
						?>
						{{ Form::bsSelect('clase_cliente_id', $clase_cliente_id, 'Clase de cliente', App\Ventas\ClaseCliente::opciones_campo_select(), ['class'=>'form-control']) }}
					</div>
				</div>

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						<?php
						if( isset($parametros['vendedor_id'] ) )
						{
							$vendedor_id = $parametros['vendedor_id'];
						}else{
							$vendedor_id = 1;
						}
						?>
						{{ Form::bsSelect('vendedor_id', $vendedor_id, 'Vendedor', App\Ventas\Vendedor::opciones_campo_select(), ['class'=>'form-control']) }}
					</div>
				</div>

			</div>

			<div class="row">

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						<?php
						if( isset($parametros['inv_bodega_id'] ) )
						{
							$inv_bodega_id = $parametros['inv_bodega_id'];
						}else{
							$inv_bodega_id = 1;
						}
						?>
						{{ Form::bsSelect('inv_bodega_id', $inv_bodega_id, 'Bodega', App\Inventarios\InvBodega::opciones_campo_select(), ['class'=>'form-control']) }}
					</div>
				</div>

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						<?php
						if( isset($parametros['zona_id'] ) )
						{
							$zona_id = $parametros['zona_id'];
						}else{
							$zona_id = 1;
						}
						?>
						{{ Form::bsSelect('zona_id', $zona_id, 'Zona', App\Ventas\Zona::opciones_campo_select(), ['class'=>'form-control']) }}
					</div>
				</div>

			</div>

			<div class="row">

				<div class="col-md-6">
					<div class="row" style="padding:5px;">
						<?php
						if( isset($parametros['condicion_pago_id'] ) )
						{
							$condicion_pago_id = $parametros['condicion_pago_id'];
						}else{
							$condicion_pago_id = 1;
						}
						?>
						{{ Form::bsSelect('condicion_pago_id', $condicion_pago_id, 'Condicion de pago', App\Ventas\CondicionPago::opciones_campo_select(), ['class'=>'form-control']) }}
					</div>
				</div>


					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['pedidos_inv_motivo_id'] ) )
								{
									$pedidos_inv_motivo_id = $parametros['pedidos_inv_motivo_id'];
								}else{
									$pedidos_inv_motivo_id = 1;
								}
							?>
							{{ Form::bsSelect('pedidos_inv_motivo_id', $pedidos_inv_motivo_id, 'Motivo por defecto pedidos ventas', App\Inventarios\InvMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>
				</div>

			</div>

			<div class="row" style="margin: 5px;"> {{ Form::bsButtonsForm( url()->previous() ) }} </div>

			{{ Form::hidden('url_id',Input::get('id')) }}
			{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}

			{{ Form::close() }}
		</div>
	</div>
	<br/><br/>
	<div id="div_cargando">Cargando...</div>
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
	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection