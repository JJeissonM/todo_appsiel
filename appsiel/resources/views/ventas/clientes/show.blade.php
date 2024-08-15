@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-4">
			<div class="btn-group">
				@if( isset($url_crear) )
					@if($url_crear!='')
						{{ Form::bsBtnCreate($url_crear) }}
					@endif
				@endif

				@if( isset($url_edit) )
					@if($url_edit!='')
						{{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
					@endif
				@endif
			</div>
		</div>

		<div class="col-md-4 text-center">
			<a class="btn btn-primary btn-xs" href="{{ url( 'vtas_cotizacion/create?id=13&id_modelo=155&id_transaccion=30' )}}">
				<i class="fa fa-file"> </i>	Crear cotización
			</a>
		</div>

		<div class="col-md-4">
			<div class="btn-group pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev('vtas_clientes/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext('vtas_clientes/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
				@endif
			</div>
		</div>	

	</div>

	<hr>

	@include('layouts.mensajes')
	
	<div class="marco_formulario">
		@include('ventas.clientes.tabla_datos_basicos')

		@include('ventas.clientes.tabla_datos_contactos')

		@include('ventas.clientes.tabla_datos_ventas')

		@include('ventas.clientes.tabla_datos_auditoria')
	</div>

@endsection