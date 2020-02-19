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
		    <h4>{!! $parametros['titulo'] !!}</h4>
		    <hr>

		    {{ Form::open(['url'=>'guardar_config','id'=>'foea_create','files' => true]) }}

		    	<!--
					// NOTA: La variable que no sea enviada en el request (a través de un input) será borrada del archivo de configuración
        			// Si se quiere agregar una nueva variable al archivo de configuración, hay que agregar también un campo nuevo a este formulario
		    	-->

				{{ Form::hidden('titulo', $parametros['titulo'] ) }}

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('ea_modelo_id', $parametros['ea_modelo_id'], 'Modelo para entradas de almacén', App\Sistema\Modelo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('ea_tipo_transaccion_id', $parametros['ea_tipo_transaccion_id'], 'Tipo de transacción para entradas de almacén', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('ea_tipo_doc_app_id', $parametros['ea_tipo_doc_app_id'], 'Documento para entradas de almacén', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							&nbsp;
						</div>
					</div>

				</div>

				<h5> Para Devoluciones en compras </h5>
				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('ea_modelo_id', $parametros['ea_modelo_id'], 'Modelo para devoluciones en compras', App\Sistema\Modelo::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('ea_tipo_transaccion_id', $parametros['ea_tipo_transaccion_id'], 'Tipo de transacción para devoluciones en compras', App\Sistema\TipoTransaccion::opciones_campo_select(), ['class'=>'form-control']) }}
						</div>
					</div>

				</div>

				<div class="row">

					<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('ea_tipo_doc_app_id', $parametros['ea_tipo_doc_app_id'], 'Documento para devoluciones en compras', App\Core\TipoDocApp::opciones_campo_select(), ['class'=>'form-control']) }}
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

	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection