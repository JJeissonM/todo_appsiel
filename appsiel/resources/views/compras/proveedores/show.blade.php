@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	
	&nbsp;&nbsp;&nbsp;
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

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev('compras_proveedores/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext('compras_proveedores/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
		@endif
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#datos-basicos"> Datos básicos </a></li>
				<li><a data-toggle="tab" href="#cuentas-bancarias"> Cuentas bancarias </a></li>
		    </ul>

			<div class="tab-content">
				<div id="datos-basicos" class="tab-pane fade in active">
					@include('layouts.form_show',compact('form_create','url_edit','tabla'))
				</div>
				<div id="cuentas-bancarias" class="tab-pane fade">
					@include('compras.proveedores.tabs.cuentas_bancarias')
				</div>
			</div>
		</div>
	</div>

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			if (window.location.hash) {
				$('.nav-tabs a[href="' + window.location.hash + '"]').tab('show');
			}

			$('.nav-tabs a').on('shown.bs.tab', function (e) {
				window.location.hash = e.target.hash;
			});
		});
	</script>
@endsection
