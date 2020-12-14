@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'nomina_print/'.$id ) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEmail( 'nomina_enviar_por_email/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}

	&nbsp;&nbsp;&nbsp; {{ Form::bsBtnDropdown( 'Acciones', 'success', 'money', 
		          [ 
		            ['link' => 'nomina/liquidacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 
		            'etiqueta' => 'Liquidación automática'], 
		            ['link' => 'nomina/retirar_liquidacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'etiqueta' => 'Retirar registros automáticos' ]
		          ] ) }}

	<!-- @ include('nomina.incluir.btn_liquidacion') -->

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'nomina/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'nomina/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<div class="container-fluid">
				{!! $view_pdf !!}
			</div>
		</div>
	</div>
	<br/><br/>	

@endsection