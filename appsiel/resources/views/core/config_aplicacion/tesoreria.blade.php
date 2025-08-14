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

				<h4> Parámetros para transacciones </h4>
				<hr>

				<?php
					$motivos_tesoreria = App\Tesoreria\TesoMotivo::opciones_campo_select();
				?>
				
				<div class="row">
					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php
								$motivo_tesoreria_compras_contado = 53;
								if( isset($parametros['motivo_tesoreria_compras_contado'] ) )
								{
									$motivo_tesoreria_compras_contado = $parametros['motivo_tesoreria_compras_contado'];
								}
							?>
							{{ Form::bsSelect('motivo_tesoreria_compras_contado', $motivo_tesoreria_compras_contado, 'Mot. de tesorería para compras de contado', $motivos_tesoreria, ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$motivo_tesoreria_ventas_contado = 54;
								if( isset($parametros['motivo_tesoreria_ventas_contado'] ) )
								{
									$motivo_tesoreria_ventas_contado = $parametros['motivo_tesoreria_ventas_contado'];
								}
							?>
							{{ Form::bsSelect('motivo_tesoreria_ventas_contado', $motivo_tesoreria_ventas_contado, 'Mot. de tesorería para ventas de contado', $motivos_tesoreria, ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$caja_default_id = '';
								if( isset($parametros['caja_default_id'] ) )
								{
									$caja_default_id = $parametros['caja_default_id'];
								}
							?>
							{{ Form::bsSelect('caja_default_id', $caja_default_id, 'Caja por defecto', App\Tesoreria\TesoCaja::opciones_campo_select(), ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						&nbsp;
					</div>

				</div>
				
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$motivo_recibo_caja_id = '';
								if( isset($parametros['motivo_recibo_caja_id'] ) )
								{
									$motivo_recibo_caja_id = $parametros['motivo_recibo_caja_id'];
								}
							?>
							{{ Form::bsSelect('motivo_recibo_caja_id', $motivo_recibo_caja_id, 'Motivo para Recibos de caja', App\Tesoreria\TesoMotivo::opciones_campo_select(), ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$motivo_comprobante_egresos_id = '';
								if( isset($parametros['motivo_comprobante_egresos_id'] ) )
								{
									$motivo_comprobante_egresos_id = $parametros['motivo_comprobante_egresos_id'];
								}
							?>
							{{ Form::bsSelect('motivo_comprobante_egresos_id', $motivo_comprobante_egresos_id, 'Motivo para Comprobantes de egreso', App\Tesoreria\TesoMotivo::opciones_campo_select(), ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$motivo_devolucion_ventas_id = 0;
								if( isset($parametros['motivo_devolucion_ventas_id'] ) )
								{
									$motivo_devolucion_ventas_id = $parametros['motivo_devolucion_ventas_id'];
								}
							?>
							{{ Form::bsSelect('motivo_devolucion_ventas_id', $motivo_devolucion_ventas_id, 'Motivo para devoluciones en ventas', App\Tesoreria\TesoMotivo::opciones_campo_select(), ['class'=>'form-control']) }}
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
							<?php 
								$buscar_por_estudiante_en_inputs = 0;
								if( isset($parametros['buscar_por_estudiante_en_inputs'] ) )
								{
									$buscar_por_estudiante_en_inputs = $parametros['buscar_por_estudiante_en_inputs'];
								}
							?>
							{{ Form::bsSelect('buscar_por_estudiante_en_inputs', $buscar_por_estudiante_en_inputs, 'Buscar por Estudiante en Campos input_suggestions', ['No','Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>
				</div>

				<h4> Parámetros por defecto de Libreta de pagos  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$formato_libreta_pago_defecto = 'pdf_libreta';
								if( isset($parametros['formato_libreta_pago_defecto'] ) )
								{
									$formato_libreta_pago_defecto = $parametros['formato_libreta_pago_defecto'];
								}
							?>
							{{ Form::bsSelect('formato_libreta_pago_defecto', $formato_libreta_pago_defecto, 'Formato de libreta', [ 'pdf_libreta' => 'Libreta dos columnas', 'pdf_libreta_tres_columnas' => 'Libreta tres columnas'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$ancho_columna_1_libretas_pagos = 45;
								if( isset($parametros['ancho_columna_1_libretas_pagos'] ) )
								{
									$ancho_columna_1_libretas_pagos = $parametros['ancho_columna_1_libretas_pagos'];
								}
							?>
							{{ Form::bsText('ancho_columna_1_libretas_pagos', $ancho_columna_1_libretas_pagos, 'Ancho columna #1 PDF Libretas pagos (%)', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$payment_book_font_size = 12;
								if( isset($parametros['payment_book_font_size'] ) )
								{
									$payment_book_font_size = $parametros['payment_book_font_size'];
								}
							?>
							{{ Form::bsText('payment_book_font_size', $payment_book_font_size, 'Tamaño de la letra (px)', ['class'=>'form-control']) }}
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
							<?php 
								$recaudo_tipo_transaccion_id = '';
								if( isset($parametros['recaudo_tipo_transaccion_id'] ) )
								{
									$recaudo_tipo_transaccion_id = $parametros['recaudo_tipo_transaccion_id'];
								}
							?>
							{{ Form::bsSelect('recaudo_tipo_transaccion_id', $recaudo_tipo_transaccion_id, 'Tipo de transacción recaudos', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$recaudo_tipo_doc_app_id = '';
								if( isset($parametros['recaudo_tipo_doc_app_id'] ) )
								{
									$recaudo_tipo_doc_app_id = $parametros['recaudo_tipo_doc_app_id'];
								}
							?>
							{{ Form::bsSelect('recaudo_tipo_doc_app_id', $recaudo_tipo_doc_app_id, 'Tipo de documento de recaudos', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$recaudo_motivo_id = '';
								if( isset($parametros['recaudo_motivo_id'] ) )
								{
									$recaudo_motivo_id = $parametros['recaudo_motivo_id'];
								}
							?>
							{{ Form::bsSelect('recaudo_motivo_id', $recaudo_motivo_id, 'Motivo de recaudos', App\Tesoreria\TesoMotivo::opciones_campo_select(), ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>
				</div>

				

				<h4> Parámetros por defecto para Recaudos de CxC </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$recaudos_cxc_modelo_id = 153;
								if( isset($parametros['recaudos_cxc_modelo_id'] ) )
								{
									$recaudos_cxc_modelo_id = $parametros['recaudos_cxc_modelo_id'];
								}
							?>
							{{ Form::bsSelect('recaudos_cxc_modelo_id', $recaudos_cxc_modelo_id, 'Modelo para Recaudos de CxC', App\Sistema\Modelo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$recaudos_cxc_tipo_transaccion_id = 32;
								if( isset($parametros['recaudos_cxc_tipo_transaccion_id'] ) )
								{
									$recaudos_cxc_tipo_transaccion_id = $parametros['recaudos_cxc_tipo_transaccion_id'];
								}
							?>
							{{ Form::bsSelect('recaudos_cxc_tipo_transaccion_id', $recaudos_cxc_tipo_transaccion_id, 'Tipo de transacción para Recaudos de CxC', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$recaudos_cxc_tipo_doc_app_id = 3;
								if( isset($parametros['recaudos_cxc_tipo_doc_app_id'] ) )
								{
									$recaudos_cxc_tipo_doc_app_id = $parametros['recaudos_cxc_tipo_doc_app_id'];
								}
							?>
							{{ Form::bsSelect('recaudos_cxc_tipo_doc_app_id', $recaudos_cxc_tipo_doc_app_id, 'Documento para Recaudos de CxC', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$tipo_operacion_recaudos_cxc = 'recaudo-cartera';
								if( isset($parametros['tipo_operacion_recaudos_cxc'] ) )
								{
									$tipo_operacion_recaudos_cxc = $parametros['tipo_operacion_recaudos_cxc'];
								}

								$arr_tipo_operacion_recaudos_cxc = json_decode( '{"":"","recaudo-cartera":"Recaudo cartera clientes","anticipo-clientes":"Anticipo/Saldo a favor clientes","otros-recaudos":"Otros recaudos","prestamo-recibido":"Préstamo financiero (CxP)","pago-proveedores":"Pago a proveedores","anticipo-proveedor":"Anticipo/Saldo a favor proveedor","otros-pagos":"Otros pagos","prestamo-entregado":"Préstamo financiero (Cartera CxC)","traslado-efectivo":"Traslado"}', true );
							?>
							{{ Form::bsSelect('tipo_operacion_recaudos_cxc', $tipo_operacion_recaudos_cxc, 'Tipo de Operación para Recaudos de CxC', $arr_tipo_operacion_recaudos_cxc, ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$recaudos_cxc_motivo_id = 0;
								if( isset($parametros['recaudos_cxc_motivo_id'] ) )
								{
									$recaudos_cxc_motivo_id = $parametros['recaudos_cxc_motivo_id'];
								}
							?>
							{{ Form::bsSelect('recaudos_cxc_motivo_id', $recaudos_cxc_motivo_id, 'Motivo para Recaudos de CxC', App\Tesoreria\TesoMotivo::opciones_campo_select(), ['class'=>'form-control', 'required'=>'required']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h4> Parámetros reporte cartera Estudiantes  </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								$campo_visualizar_en_reporte_cartera = 'codigo_matricula';
								if( isset($parametros['campo_visualizar_en_reporte_cartera'] ) )
								{
									$campo_visualizar_en_reporte_cartera = $parametros['campo_visualizar_en_reporte_cartera'];
								}
							?>
							{{ Form::bsSelect('campo_visualizar_en_reporte_cartera', $campo_visualizar_en_reporte_cartera, 'Campo a visualizar en reporte cartera', [ 'anio_lectivo' => 'Año lectivo de la matrícula', 'codigo_matricula' => 'Código de la matrícula'], ['class'=>'form-control']) }}
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