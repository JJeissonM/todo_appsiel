@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		.dropdown-submenu{position:relative;}
		.dropdown-submenu>.dropdown-menu{top:0;left:100%;margin-top:-6px;margin-left:-1px;-webkit-border-radius:0 6px 6px 6px;-moz-border-radius:0 6px 6px 6px;border-radius:0 6px 6px 6px;}
		.dropdown-submenu:hover>.dropdown-menu{display:block;}
		.dropdown-submenu>a:after{display:block;content:" ";float:right;width:0;height:0;border-color:transparent;border-style:solid;border-width:5px 0 5px 5px;border-left-color:#cccccc;margin-top:5px;margin-right:-10px;}
		.dropdown-submenu:hover>a:after{border-left-color:#ffffff;}
		.dropdown-submenu.pull-left{float:none;}.dropdown-submenu.pull-left>.dropdown-menu{left:-100%;margin-left:10px;-webkit-border-radius:6px 0 6px 6px;-moz-border-radius:6px 0 6px 6px;border-radius:6px 0 6px 6px;}
	</style>
@endsection

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