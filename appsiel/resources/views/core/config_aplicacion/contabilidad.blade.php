@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    {!! $parametros['titulo'] !!}
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

		    	<!--
					// NOTA: La variable que no sea enviada en el request (a través de un input) será borrada del archivo de configuración
        			// Si se quiere agregar una nueva variable al archivo de configuración, hay que agregar también un campo nuevo a este formulario
		    	-->

				{{ Form::hidden('titulo', $parametros['titulo'] ) }}

				<h4> Documento contable por defecto </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$transaction_type_id_default = 43;

								if( isset($parametros['transaction_type_id_default'] ) )
								{
									$transaction_type_id_default = $parametros['transaction_type_id_default'];
								}
							?>
							{{ Form::bsSelect('transaction_type_id_default', $transaction_type_id_default, 'Tipo de transaccion ', \App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$document_type_id_default = 43;

								if( isset($parametros['document_type_id_default'] ) )
								{
									$document_type_id_default = $parametros['document_type_id_default'];
								}
							?>
							{{ Form::bsSelect('document_type_id_default', $document_type_id_default, 'Tipo documento', \App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>


				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$contact_id_default = 1;

								if( isset($parametros['contact_id_default'] ) )
								{
									$contact_id_default = $parametros['contact_id_default'];
								}
							?>
							{{ Form::bsSelect('contact_id_default', $contact_id_default, 'Tercero', \App\Core\Tercero::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$crear_cliente_y_proveedor_al_crear_tercero = 1;

								if( isset($parametros['crear_cliente_y_proveedor_al_crear_tercero'] ) )
								{
									$crear_cliente_y_proveedor_al_crear_tercero = $parametros['crear_cliente_y_proveedor_al_crear_tercero'];
								}
							?>
							{{ Form::bsSelect('crear_cliente_y_proveedor_al_crear_tercero', $crear_cliente_y_proveedor_al_crear_tercero, 'Crear Cliente y Proveedor al crear un Tercero', ['No','Si'], []) }}
						</div>
					</div>

				</div>

				<br>
				<h4> Cuentas contables por defecto </h4>
				<?php
					$array_cuentas = App\Contabilidad\ContabCuenta::opciones_campo_select();
				?>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cta_cartera_default = 27;
								if( isset($parametros['cta_cartera_default'] ) )
								{
									$cta_cartera_default = $parametros['cta_cartera_default'];
								}
							?>
							{{ Form::bsSelect('cta_cartera_default', $cta_cartera_default, 'Cta. Cartera (CxC)', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cta_anticipo_clientes_default = 219;
								if( isset($parametros['cta_anticipo_clientes_default'] ) )
								{
									$cta_anticipo_clientes_default = $parametros['cta_anticipo_clientes_default'];
								}
							?>
							{{ Form::bsSelect('cta_anticipo_clientes_default', $cta_anticipo_clientes_default, 'Cta. Anticipo clientes', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cta_por_pagar_default = 131;
								if( isset($parametros['cta_por_pagar_default'] ) )
								{
									$cta_por_pagar_default = $parametros['cta_por_pagar_default'];
								}
							?>
							{{ Form::bsSelect('cta_por_pagar_default', $cta_por_pagar_default, 'Cta. por pagar (CxP)', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cta_anticipo_proveedores_default = 29;
								if( isset($parametros['cta_anticipo_proveedores_default'] ) )
								{
									$cta_anticipo_proveedores_default = $parametros['cta_anticipo_proveedores_default'];
								}
							?>
							{{ Form::bsSelect('cta_anticipo_proveedores_default', $cta_anticipo_proveedores_default, 'Cta. Anticipo proveedores', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cta_ingresos_default = 229;
								if( isset($parametros['cta_ingresos_default'] ) )
								{
									$cta_ingresos_default = $parametros['cta_ingresos_default'];
								}
							?>
							{{ Form::bsSelect('cta_ingresos_default', $cta_ingresos_default, 'Cta. ingresos (ventas)', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cta_gastos_default = 229;
								if( isset($parametros['cta_gastos_default'] ) )
								{
									$cta_gastos_default = $parametros['cta_gastos_default'];
								}
							?>
							{{ Form::bsSelect('cta_gastos_default', $cta_gastos_default, 'Cta. Gastos', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cta_impuestos_default = 229;
								if( isset($parametros['cta_impuestos_default'] ) )
								{
									$cta_impuestos_default = $parametros['cta_impuestos_default'];
								}
							?>
							{{ Form::bsSelect('cta_impuestos_default', $cta_impuestos_default, 'Cta. Impuestos', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cta_inventarios_default = 229;
								if( isset($parametros['cta_inventarios_default'] ) )
								{
									$cta_inventarios_default = $parametros['cta_inventarios_default'];
								}
							?>
							{{ Form::bsSelect('cta_inventarios_default', $cta_inventarios_default, 'Cta. Inventarios', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

				</div>
				
				<br>
				<h4> Parámetros por defecto cierre del ejercicio </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$transaccion_default_cierre_ejercicio = 43;

								if( isset($parametros['transaccion_default_cierre_ejercicio'] ) )
								{
									$transaccion_default_cierre_ejercicio = $parametros['transaccion_default_cierre_ejercicio'];
								}
							?>
							{{ Form::bsSelect('transaccion_default_cierre_ejercicio', $transaccion_default_cierre_ejercicio, 'Tipo de transaccion para cierre del ejercicio', \App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tipo_documento_cierre_ejercicio = 43;

								if( isset($parametros['tipo_documento_cierre_ejercicio'] ) )
								{
									$tipo_documento_cierre_ejercicio = $parametros['tipo_documento_cierre_ejercicio'];
								}
							?>
							{{ Form::bsSelect('tipo_documento_cierre_ejercicio', $tipo_documento_cierre_ejercicio, 'Tipo documento para cierre del ejercicio', \App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tercero_default_cierre_ejercicio = 0;

								if( isset($parametros['tercero_default_cierre_ejercicio'] ) )
								{
									$tercero_default_cierre_ejercicio = $parametros['tercero_default_cierre_ejercicio'];
								}
							?>
							{{ Form::bsSelect('tercero_default_cierre_ejercicio', $tercero_default_cierre_ejercicio, 'Tercero por defecto para cierre del ejercicio', \App\Core\Tercero::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$cuenta_ganancias_perdidas_ejercicio = 411;

								if( isset($parametros['cuenta_ganancias_perdidas_ejercicio'] ) )
								{
									$cuenta_ganancias_perdidas_ejercicio = $parametros['cuenta_ganancias_perdidas_ejercicio'];
								}
							?>
							{{ Form::bsSelect('cuenta_ganancias_perdidas_ejercicio', $cuenta_ganancias_perdidas_ejercicio, 'Cta. Ganancias o Perdidas del ejercicio', \App\Contabilidad\ContabCuenta::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<br>
				<h5> Liquidación de Retenciones </h5>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tercero_dian_id = 0;
								if( isset($parametros['tercero_dian_id'] ) )
								{
									$tercero_dian_id = $parametros['tercero_dian_id'];
								}
							?>
							{{ Form::bsSelect('tercero_dian_id', $tercero_dian_id, 'Tercero DIAN', App\Core\Tercero::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
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