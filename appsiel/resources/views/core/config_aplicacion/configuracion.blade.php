@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>{!! $parametros['titulo'] !!}</h4>
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'form_create','files' => true]) }}

		    	<!--
					// NOTA: La variable que no sea enviada en el request (a través de un input) será borrada del archivo de configuración
        			// Si se quiere agregar una nueva variable al archivo de configuración, hay que agregar también un campo nuevo a este formulario
		    	-->

				{{ Form::hidden('titulo', $parametros['titulo'] ) }}

				<h4> Configuraciones generales </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['url_instancia_cliente'] ) )
								{
									$url_instancia_cliente = $parametros['url_instancia_cliente'];
								}else{
									$url_instancia_cliente = 'http://localhost/appsiel_2020/appsiel/';
								}
							?>
							{{ Form::bsText('url_instancia_cliente', $url_instancia_cliente, 'Url Dominio y Directorio de la aplicacion)', ['class'=>'form-control']) }}
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
								if( isset($parametros['alto_logo_formatos'] ) )
								{
									$alto_logo_formatos = $parametros['alto_logo_formatos'];
								}else{
									$alto_logo_formatos = 110;
								}
							?>
							{{ Form::bsText('alto_logo_formatos', $alto_logo_formatos, 'Alto logo formatos (px)', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['ancho_logo_formatos'] ) )
								{
									$ancho_logo_formatos = $parametros['ancho_logo_formatos'];
								}else{
									$ancho_logo_formatos = 110;
								}
							?>
							{{ Form::bsText('ancho_logo_formatos', $ancho_logo_formatos, 'Ancho logo formatos (px)', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<br>
				<h4> Configuraciones de empresa </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['liquidacion_impuestos'] ) )
								{
									$liquidacion_impuestos = $parametros['liquidacion_impuestos'];
								}else{
									$liquidacion_impuestos = 0;
								}
							?>
							{{ Form::bsSelect('liquidacion_impuestos', $liquidacion_impuestos, 'Liquida impuestos', ['0' => 'No liquida','1' => 'Si liquida'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['tipo_identificador'] ) )
								{
									$tipo_identificador = $parametros['tipo_identificador'];
								}else{
									$tipo_identificador = 0;
								}
							?>
							{{ Form::bsSelect('tipo_identificador', $tipo_identificador, 'Tipo de identificador', ['NIT' => 'NIT','CUIT' => 'CUIT'], ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<br>
				<h4> Cuentas contables por defecto </h4>
				<?php 

					$tabla_existe = DB::select( DB::raw( "SHOW TABLES LIKE 'contab_cuentas'" ) );

			        if ( !empty( $tabla_existe ) )
			        {
						$array_cuentas = App\Contabilidad\ContabCuenta::opciones_campo_select();
					}else{
						$array_cuentas = [0];
					}
				?>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['cta_cartera_default'] ) )
								{
									$cta_cartera_default = $parametros['cta_cartera_default'];
								}else{
									$cta_cartera_default = 27;
								}
							?>
							{{ Form::bsSelect('cta_cartera_default', $cta_cartera_default, 'Cta. Cartera (CxC)', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['cta_anticipo_clientes_default'] ) )
								{
									$cta_anticipo_clientes_default = $parametros['cta_anticipo_clientes_default'];
								}else{
									$cta_anticipo_clientes_default = 219;
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
								if( isset($parametros['cta_por_pagar_default'] ) )
								{
									$cta_por_pagar_default = $parametros['cta_por_pagar_default'];
								}else{
									$cta_por_pagar_default = 131;
								}
							?>
							{{ Form::bsSelect('cta_por_pagar_default', $cta_por_pagar_default, 'Cta. por pagar (CxP)', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['cta_anticipo_proveedores_default'] ) )
								{
									$cta_anticipo_proveedores_default = $parametros['cta_anticipo_proveedores_default'];
								}else{
									$cta_anticipo_proveedores_default = 29;
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
								if( isset($parametros['cta_ingresos_default'] ) )
								{
									$cta_ingresos_default = $parametros['cta_ingresos_default'];
								}else{
									$cta_ingresos_default = 229;
								}
							?>
							{{ Form::bsSelect('cta_ingresos_default', $cta_ingresos_default, 'Cta. ingresos (ventas)', $array_cuentas, ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<br>
				<h4> Otras configuraciones </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['usar_mensajes_internos'] ) )
								{
									$usar_mensajes_internos = $parametros['usar_mensajes_internos'];
								}else{
									$usar_mensajes_internos = 0;
								}
							?>
							{{ Form::bsSelect('usar_mensajes_internos', $usar_mensajes_internos, 'Manejar mensajes internos', ['0'=>'No','1'=>'Si'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							<?php 
								if( isset($parametros['usuario_en_mora'] ) )
								{
									$usar_mensajes_internos = $parametros['usuario_en_mora'];
								}else{
									$usar_mensajes_internos = false;
								}
							?>
							{{ Form::bsSelect('usuario_en_mora', $usar_mensajes_internos, 'Usuario en Mora', ['false'=>'No','true'=>'Si'], ['class'=>'form-control']) }}
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
@endsection