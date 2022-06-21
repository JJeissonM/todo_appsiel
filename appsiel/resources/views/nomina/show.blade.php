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

	<div class="row">
		<div class="col-md-5">
			&nbsp;&nbsp;&nbsp; {{ Form::bsBtnCreate( 'web/create?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
			

			@if ( $encabezado_doc->estado == 'Activo' )
				{{ Form::bsBtnEdit2('web/'.$encabezado_doc_id.'/edit?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
				{{ Form::bsBtnEliminar('web_eliminar/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion') ) }}
				&nbsp;&nbsp;&nbsp; {{ Form::bsBtnDropdown( 'Liquidar', 'primary', 'cogs', 
						[ 
							['link' => 'nomina/liquidacion/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
							'etiqueta' => 'Registros automáticos (todo)'],
							['link' => 'nomina/liquidacion_sp/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
							'etiqueta' => 'Solo salud y pensión'],
							['link' => 'nom_liquidar_prima_antiguedad/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
							'etiqueta' => 'Primas de antigüedad']
						] ) }}
				&nbsp;&nbsp;&nbsp; {{ Form::bsBtnDropdown( 'Retirar', 'warning', 'history', 
						[ 
							['link' => 'nomina/retirar_liquidacion/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 'etiqueta' => 'Registros automáticos (todo)' ],
							['link' => 'nom_retirar_prima_antiguedad/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion'), 
							'etiqueta' => 'Primas de antigüedad']
						] ) }}
			@else
				<small>(Documento está <b>{{ $encabezado_doc->estado }}</b>)</small>
			@endif
		</div>
		<div class="col-md-6">

			Formato: {{ Form::select('formato_impresion_id',['1'=>'Estándar','2'=>'Estándar v2'], null, [ 'id' =>'formato_impresion_id' ] ) }}
			{{ Form::bsBtnPrint( 'nomina_print/'.$encabezado_doc_id.'?id='.Input::get('id').'&id_modelo='. Input::get('id_modelo') . '&id_transaccion='. Input::get('id_transaccion').'&formato_impresion_id=1' ) }}
			
		</div>
		<div class="col-md-1">
			<div class="pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev( 'nomina/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext( 'nomina/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
				@endif
			</div>
		</div>
	</div>
	

	<!-- @ include('nomina.incluir.btn_liquidacion') -->

	
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			@include('nomina.incluir.encabezado_transaccion')

			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#tab1"> Registros de liquidación </a></li>
				<li><a data-toggle="tab" href="#tab2"> Empleados del documento </a></li>
				<li><a data-toggle="tab" href="#tab3"> Contabilización </a></li>
		    </ul>

		    <div class="tab-content">
		    	<div id="tab1" class="tab-pane fade in active">
			        @include( 'nomina.incluir.tabla_registros_documento' )
			    </div>
			    <div id="tab2" class="tab-pane fade">
			        @include( 'nomina.incluir.tabla_empleados_documento' )
		    	</div>
			    <div id="tab3" class="tab-pane fade">
			    	<br><br>
			        @include('transaccion.registros_contables_con_terceros')
		    	</div>
		    </div><!---->
			
			@include('transaccion.auditoria', [ 'doc_encabezado' => $encabezado_doc ])

		</div>
	</div>
	<br/><br/>	

@endsection
@section('scripts9')
<script>
	document.addEventListener("DOMContentLoaded", function(event) {
    //código a ejecutar cuando el DOM está listo para recibir acciones
	//console.log(event)
	document.getElementsByClassName("buttons-excel")[0].classList.add("btn-gmail","btn-excel");
	document.getElementsByClassName("buttons-excel")[0].innerHTML = '<i class="fa fa-file-excel-o"></i>';
	document.getElementsByClassName("buttons-pdf")[0].classList.add("btn-gmail","btn-pdf");
	document.getElementsByClassName("buttons-pdf")[0].innerHTML = '<i class="fa fa-file-pdf-o"></i>';
	document.getElementsByClassName("dt-buttons")[0].classList.add("d-inline");
	document.getElementById('myTable_filter').children[0].children[0].classList.add('form-control');
	document.getElementById('myTable_filter').children[0].children[0].placeholder = 'Escriba aquí para buscar...';	
	
	
});
			$('#formato_impresion_id').on('change',function(){
				var btn_print = $('#btn_print').attr('href');

				n = btn_print.search('formato_impresion_id');
				var url_aux = btn_print.substr(0,n);
				var new_url = url_aux + 'formato_impresion_id=' + $(this).val();
				
				$('#btn_print').attr('href', new_url);



				var btn_email = $('#btn_email').attr('href');

				n = btn_email.search('formato_impresion_id');
				var url_aux = btn_email.substr(0,n);
				var new_url = url_aux + 'formato_impresion_id=' + $(this).val();
				
				$('#btn_email').attr('href', new_url);
				
			});
</script>	
@endsection
