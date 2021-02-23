@extends('core.procesos.layout')

@section( 'titulo', 'Generar consolidados de prestaciones sociales' )

@section('detalles')
	<p>
		Este proceso recorre todos los movimientos del MES y acumula los valores para liquidar las prestaciones sociales de cada empleado.
	</p>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">
		{{ Form::open(['url'=>'nom_consolidar_prestaciones','id'=>'formulario_inicial','files' => true]) }}
							
			<div class="row" style="padding:5px;">
				<label class="control-label col-sm-4" > <b> *Opciones de liquidación: </b> </label>

				<div class="col-sm-8">
					{{ Form::select( 'almacenar_registros', ['Previsualizar','Almacenar registros'],null, [ 'class' => 'form-control', 'id' => 'almacenar_registros' ]) }}
				</div>
			</div>

			<div class="row" style="padding:5px;">
				
				<label class="control-label col-sm-4" >
					<span data-toggle="tooltip" title="Escoger día 30 del mes. Para febrero, escoger siempre día 28."> <i class="fa fa-question-circle"></i></span>
					<b> *Fecha fin de MES: </b> 
				</label>

				<div class="col-sm-8">
					{{ Form::date( 'fecha_final_promedios',null, [ 'class' => 'form-control', 'id' => 'fecha_final_promedios', 'required' => 'required' ]) }}
				</div>
			</div>

			<div class="row" style="padding:5px;">
				&nbsp;				 
			</div>

			<div class="row" style="padding:5px; text-align: center;">
				<div class="col-md-6">
					<button class="btn btn-success" id="btn_liquidar"> <i class="fa fa-calculator"></i> Liquidar </button>
				</div>
				<div class="col-md-6">
					<button class="btn btn-danger" id="btn_retirar"> <i class="fa fa-trash"></i> Retirar </button>
				</div>
			</div>

				
		{{ Form::close() }}
	</div>


	{{ Form::bsBtnExcel( 'liquidacion_provisiones' ) }}
	{{ Form::bsBtnPdf( 'liquidacion_provisiones' ) }}

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#fecha_final_promedios").focus();			

			$("#btn_liquidar").on('click',function(event){
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

				var url = "{{ url('nom_retirar_consolidado_prestaciones') }}" + '/' + $('#fecha_final_promedios').val();

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