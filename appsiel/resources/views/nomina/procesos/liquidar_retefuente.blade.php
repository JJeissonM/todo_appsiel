@extends('core.procesos.layout')

@section( 'titulo', 'Liquidar prestaciones sociales' )

@section('detalles')
	<p>
		Por esta opción, el sistema revisa todo pago laboral que debe incluirse en la base sujeta a retención en la fuente y procede a realizar el respectivo calculo según los procedimientos parametrizados y la tabla de retención configurada.
	</p>
	<br>
	<span class="text-info"> Si no se escoge una fecha final de promedios, se asume la fecha del Documento de liquidación. </span>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">
		{{ Form::open(['url'=>'nom_liquidar_retefuente','id'=>'formulario_inicial','files' => true]) }}
			<div class="row" style="padding:5px;">					
				<label class="control-label col-sm-4" > <b> *Documento de liquidación: </b> </label>

				<div class="col-sm-8">
					{{ Form::select( 'nom_doc_encabezado_id', App\Nomina\NomDocEncabezado::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'nom_doc_encabezado_id', 'required' => 'required' ]) }}
				</div>					 
			</div>
							
			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" > <b> *Opciones de liquidación: </b> </label>

				<div class="col-sm-8">
					{{ Form::select( 'almacenar_registros', ['Previsualizar','Almacenar registros'],null, [ 'class' => 'form-control', 'id' => 'almacenar_registros' ]) }}
				</div>
			</div>

			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" > <b> Fecha final para promedios: </b> </label>

				<div class="col-sm-8">
					{{ Form::date( 'fecha_final_promedios',null, [ 'class' => 'form-control', 'id' => 'fecha_final_promedios' ]) }}
				</div>
			</div>

			<div class="row" style="padding:5px;">
				&nbsp;				 
			</div>

			<div class="row" style="padding:5px; text-align: center;">
				<div class="col-md-6">
					<button class="btn btn-success" id="btn_cargar"> <i class="fa fa-calculator"></i> Liquidar </button>
				</div>
				<div class="col-md-6">
					<button class="btn btn-danger" id="btn_retirar"> <i class="fa fa-trash"></i> Retirar </button>
				</div>
			</div>

				
		{{ Form::close() }}
	</div>


	{{ Form::bsBtnExcel( 'liquidacion_retefuente' ) }}
	{{ Form::bsBtnPdf( 'liquidacion_retefuente' ) }}

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#btn_cargar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
        		$("#div_resultado").html( '' );
				
				var form = $('#formulario_inicial');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("formulario_inicial"));

				$.ajax({
				    url: url,
				    type: "post",
				    dataType: "html",
				    data: datos,
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
			    });
		    });

			$("#btn_retirar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
        		$("#div_resultado").html( '' );

				var form = $('#formulario_inicial');
				var url = "{{ url('nom_retirar_retefuente') }}" + '/' + $('#nom_doc_encabezado_id').val();

				$.ajax({
				    url: url,
				    type: "get",
				    dataType: "html",
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
			    });
		    });

		});
	</script>
@endsection