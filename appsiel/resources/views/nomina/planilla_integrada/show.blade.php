@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		.dropdown-submenu{position:relative;}
		.dropdown-submenu>.dropdown-menu{top:0;left:100%;margin-top:-6px;margin-left:-1px;-webkit-border-radius:0 6px 6px 6px;-moz-border-radius:0 6px 6px 6px;border-radius:0 6px 6px 6px;}
		.dropdown-submenu:hover>.dropdown-menu{display:block;}
		.dropdown-submenu>a:after{display:block;content:" ";float:right;width:0;height:0;border-color:transparent;border-style:solid;border-width:5px 0 5px 5px;border-left-color:#cccccc;margin-top:5px;margin-right:-10px;}
		.dropdown-submenu:hover>a:after{border-left-color:#ffffff;}
		.dropdown-submenu.pull-left{float:none;}.dropdown-submenu.pull-left>.dropdown-menu{left:-100%;margin-left:10px;-webkit-border-radius:6px 0 6px 6px;-moz-border-radius:6px 0 6px 6px;border-radius:6px 0 6px 6px;}

		.celda_datos_basicos{
			background: #62a3ca;
		}
		.celda_novedades{
			background: #e2ef26;
		}
		.celda_salud{
			background: #77de6a;
		}
		.celda_pension{
			background: #cd77f1;
		}
		.celda_riesgos_laborales{
			background: #f56183;
		}
		.celda_parafiscales{
			background: #ff9b55;
		}
	</style>
@endsection

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	<?php 
		$variables_url = '?id=17&id_modelo=271&id_transaccion=';
        $color = 'green'; 
    ?>

	@if ( $planilla_generada->estado == 'Activo' )
		&nbsp;&nbsp;&nbsp; 
		<a class="btn btn-primary" id="btn_generar" href="{{ url('nom_pila_liquidar_planilla') . '/' . $planilla_generada->id . $variables_url }}"> <i class="fa fa-calculator"></i> Generar </a>
		<button class="btn btn-danger" id="btn_anular" title="Eliminar"><i class="fa fa-close"></i> </button>
	@else
		<small>(Documento está <b>{{ $planilla_generada->estado }}</b>)</small>
		<?php 
	        $color = 'red'; 
	    ?>
	@endif

	<button class="btn btn-default" id="btn_descargar_plano" disabled="disabled" title="Descargar archivo plano"> <i class="fa fa-file-text-o"></i> </button>

	<button class="btn btn-success" id="btn_excel" title="Descargar Excel"> <i class="fa fa-file-excel-o"></i> <span style="display: none;">{{ $planilla_generada->descripcion }}</span> </button>

	{{ Form::Spin(48) }}
	<hr>

	<div class="alert alert-warning" style="display: none;">
        <a href="#" id="close" class="close">&times;</a>
        <strong>Advertencia!</strong>
        <br>
        Al eliminar la planilla, se borrarán todos los registros de Novedades, Salud, Pensión, Riesgos laborales y Parafiscales asociados. La eliminación no se puede revertir.
        <br>
        Si realmente quiere eliminar la planilla, haga click en el siguiente enlace: <small> <a href="{{ url( 'nom_pila_eliminar_planilla/' . $planilla_generada->id . $variables_url ) }}"> Eliminar </a> </small>
    </div>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<b style="font-size: 1.6em; text-align: center; display: block;">Planilla integrada autoliquidación de aportes</b>

			<div class="row">
				
				<div class="col-md-4">
					<h4>Datos de la planilla</h4>
					<hr>
	                <b>Documento:</b> {{ $planilla_generada->descripcion }}
	                <br/>

	                @php 
	                    $fecha = explode("-",$planilla_generada->fecha_final_mes) 
	                @endphp

	                <b>Fecha: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}

	                <div>
	                    <b> Estado: </b> <i class="fa fa-circle" style="color: {{$color}}"> </i> {{ $planilla_generada->estado }}
	                </div>
				</div>

				<div class="col-md-8">
					<h4>Datos de la empresa</h4>
					<hr>
					@include('nomina.planilla_integrada.datos_empresa')
				</div>
			</div>                

			<div class="table-responsive">
			
				{!! $tabla_planilla !!}
			
			</div>

		</div>
	</div>
	<br/><br/>	

@endsection


@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			$('#btn_excel').click(function(event){
				event.preventDefault();
				var nombre_listado = $(this).children('span').text();
				var tT = new XMLSerializer().serializeToString(document.querySelector('.table-planilla')); //Serialised table
				var tF = nombre_listado + '.xls'; //Filename
				var tB = new Blob([tT]); //Blub

				if(window.navigator.msSaveOrOpenBlob){
				    //Store Blob in IE
				    window.navigator.msSaveOrOpenBlob(tB, tF)
				}
				else{
				    //Store Blob in others
				    var tA = document.body.appendChild(document.createElement('a'));
				    tA.href = URL.createObjectURL(tB);
				    tA.download = tF;
				    tA.style.display = 'none';
				    tA.click();
				    tA.parentNode.removeChild(tA)
				}
			});



			$('#btn_anular').on('click',function(e){
				e.preventDefault();
				$('.alert.alert-warning').show(1000);
			});

			$('#close').on('click',function(e){
				e.preventDefault();
				$('.alert.alert-warning').hide(1000);
			});
		});
	</script>
@endsection


