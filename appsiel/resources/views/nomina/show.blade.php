@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		.dropdown-submenu{position:relative;}
		.dropdown-submenu>.dropdown-menu{top:0;left:100%;margin-top:-6px;margin-left:-1px;-webkit-border-radius:0 6px 6px 6px;-moz-border-radius:0 6px 6px 6px;border-radius:0 6px 6px 6px;}
		.dropdown-submenu:hover>.dropdown-menu{display:block;}
		.dropdown-submenu>a:after{display:block;content:" ";float:right;width:0;height:0;border-color:transparent;border-style:solid;border-width:5px 0 5px 5px;border-left-color:#cccccc;margin-top:5px;margin-right:-10px;}
		.dropdown-submenu:hover>a:after{border-left-color:#ffffff;}
		.dropdown-submenu.pull-left{float:none;}.dropdown-submenu.pull-left>.dropdown-menu{left:-100%;margin-left:10px;-webkit-border-radius:6px 0 6px 6px;-moz-border-radius:6px 0 6px 6px;border-radius:6px 0 6px 6px;}

		table{
			margin-top: 0 !important;
		}
		td > table > tbody{
			background-color: unset;
		}
	</style>
@endsection

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp; {{ Form::bsBtnCreate( 'web/create?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
	&nbsp;&nbsp;&nbsp; {{ Form::bsBtnPrint( 'nomina_print/'.$id.'?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}

	@if ( $encabezado_doc->estado == 'Activo' )
		{{ Form::bsBtnEdit2('web/'.$id.'/edit?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
		{{ Form::bsBtnEliminar('web_eliminar/'.$id.'?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
		&nbsp;&nbsp;&nbsp; {{ Form::bsBtnDropdown( 'Liquidar', 'primary', 'cogs', 
		          [ 
		            ['link' => 'nomina/liquidacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
		            'etiqueta' => 'Registros automáticos (todo)'],
		            ['link' => 'nom_liquidar_prima_antiguedad/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
		            'etiqueta' => 'Primas de antigüedad']
		          ] ) }}
		&nbsp;&nbsp;&nbsp; {{ Form::bsBtnDropdown( 'Retirar', 'warning', 'history', 
		          [ 
		            ['link' => 'nomina/retirar_liquidacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 'etiqueta' => 'Registros automáticos (todo)' ],
		            ['link' => 'nom_retirar_prima_antiguedad/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
		            'etiqueta' => 'Primas de antigüedad']
		          ] ) }}
	@else
		<small>(Documento está <b>{{ $encabezado_doc->estado }}</b>)</small>
	@endif

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

			<div class="container-fluid">
				<br/><br/>
				<ul class="nav nav-tabs">
				  <li class="active"><a href="#">{{ $titulo_tab }}</a></li>
				</ul>
				
				<br/><br/>
				
				{!! $tabla !!}

				{{ Form::open(array('url'=>'nom_guardar_asignacion')) }}
					<div class="row">
						<div class="col-md-8 col-md-offset-2" style="vertical-align: center; border: 1px solid gray;">
							<h3>Asignar nuevo</h3>
							<div class="row">
								<div class="col-md-6">
									{{ Form::bsSelect('registro_modelo_hijo_id',null,$titulo_tab,$opciones,['class'=>'combobox']) }}
								</div>
								<div class="col-md-6">
									{{ Form::bsText('nombre_columna1',null,'Orden',[]) }}
								</div>
								{{ Form::hidden('registro_modelo_padre_id',$registro_modelo_padre_id) }}

								{{ Form::hidden('url_id',Input::get('id'))}}
								{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
								{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion'))}}
							</div>
							<div align="center">
								<br/>
								{{ Form::submit('Guardar', array('class' => 'btn btn-primary btn-sm')) }}
							</div>
							<br/><br/>
						</div>
					</div>
				{{ Form::close() }}
			</div>
		</div>
	</div>
	<br/><br/>	

@endsection
@section('scripts9')
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
    //código a ejecutar cuando el DOM está listo para recibir acciones
	console.log(event)
	document.getElementsByClassName("buttons-excel")[0].classList.add("btn-gmail","btn-excel");
	document.getElementsByClassName("buttons-excel")[0].innerHTML = '<i class="fa fa-file-excel-o"></i>';
	document.getElementsByClassName("buttons-pdf")[0].classList.add("btn-gmail","btn-pdf");
	document.getElementsByClassName("buttons-pdf")[0].innerHTML = '<i class="fa fa-file-pdf-o"></i>';
	document.getElementsByClassName("dt-buttons")[0].classList.add("d-inline");
	document.getElementById('myTable_filter').children[0].children[0].classList.add('form-control');
	document.getElementById('myTable_filter').children[0].children[0].placeholder = 'Escriba aquí para buscar...';	
	
	
});
	
</script>	
@endsection
