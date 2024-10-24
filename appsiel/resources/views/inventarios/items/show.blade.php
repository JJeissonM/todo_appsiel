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
			&nbsp;
		</div>

		<div class="col-md-4">
			<div class="btn-group pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev('inv_item_mandatario/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext('inv_item_mandatario/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
				@endif
			</div>
		</div>	

	</div>

	<hr>

	@include('layouts.mensajes')
	
	<div class="marco_formulario">
		<?php  
			//dd($registro);
		?>
		@include('inventarios.items.tabla_datos_basicos')

		@if( Input::get('id_modelo') == 331)
			@include('inventarios.items.mandatarios.tabla_items_relacionados_proveedor')
		@else
			@include('inventarios.items.tabla_items_relacionados')
		@endif
	</div>

@endsection