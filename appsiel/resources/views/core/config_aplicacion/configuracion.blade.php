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
							{{ Form::bsText('url_instancia_cliente', $parametros['url_instancia_cliente'], 'Url Dominio y Directorio de la aplicacion)', ['class'=>'form-control']) }}
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
							{{ Form::bsText('alto_logo_formatos', $parametros['alto_logo_formatos'], 'Alto logo formatos (px)', ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsText('ancho_logo_formatos', $parametros['ancho_logo_formatos'], 'Ancho logo formatos (px)', ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<br>
				<h4> Configuraciones de empresa </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('liquidacion_impuestos', $parametros['liquidacion_impuestos'], 'Liquida impuestos', ['0' => 'No liquida','1' => 'Si liquida'], ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<br>
				<h4> Cuentas contables por defecto </h4>
				<hr>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('cta_cartera_default', $parametros['cta_cartera_default'], 'Cta. Cartera (CxC)', App\Contabilidad\ContabCuenta::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('cta_anticipo_clientes_default', $parametros['cta_anticipo_clientes_default'], 'Cta. Anticipo clientes', App\Contabilidad\ContabCuenta::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('cta_por_pagar_default', $parametros['cta_por_pagar_default'], 'Cta. por pagar (CxP)', App\Contabilidad\ContabCuenta::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('cta_anticipo_proveedores_default', $parametros['cta_anticipo_proveedores_default'], 'Cta. Anticipo proveedores', App\Contabilidad\ContabCuenta::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('cta_ingresos_default', $parametros['cta_ingresos_default'], 'Cta. ingresos (ventas)', App\Contabilidad\ContabCuenta::opciones_campo_select(), ['class'=>'combobox']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
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
@endsection