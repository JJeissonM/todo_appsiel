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
								$tercero_default_cierre_ejercicio = 1;

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