@extends('core.procesos.layout')

@section( 'titulo', 'Cargar archivo plano para liquidación de conceptos' )

@section('detalles')
	<p>
		Este proceso permiter cargar un archivo plano (.txt) con una estructura definida para liquidar conceptos de nómina en un documento específico.
	</p>
	
	Luego se almacenan los registros del documento de nómina según las líneas de registros cargadas en el archivo plano.
	
	<br>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">
		{{ Form::open(['url'=>'nom_procesar_archivo_plano','id'=>'formulario_inicial','files' => true]) }}
			<div class="row" style="padding:5px;">					
				<label class="control-label col-sm-4" > <b> *Documento de liquidación: </b> </label>

				<div class="col-sm-8">
					{{ Form::select( 'nom_doc_encabezado_id', App\Nomina\NomDocEncabezado::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'nom_doc_encabezado_id', 'required' => 'required' ]) }}
				</div>					 
			</div>

			<div class="row" style="padding:5px;">					
				<label class="control-label col-sm-4" > <b> *Archivo plano: </b> </label>

				<div class="col-sm-8">
					{{ Form::file('archivo_plano', [ 'class' => 'form-control', 'id' => 'archivo_plano', 'accept' => 'text/plain', 'required' => 'required' ]) }}
				</div>					 
			</div>

			<div class="col-md-4">
				<button class="btn btn-success" id="btn_cargar"> <i class="fa fa-calculator"></i> Cargar </button>
			</div>
		{{ Form::close() }}
	</div>

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

			$(document).on('click', '.btn_eliminar', function(event) {
				event.preventDefault();
				var fila = $(this).closest("tr");
				if ( confirm('¿Esta seguro de eliminar esta fila de los registros a almacenar?') )
				{
					fila.remove();
				}
			});

			$(document).on('click', '#btn_almacenar_registros', function(event) {
				event.preventDefault();

				var table = $( '#ingreso_registros' ).tableToJSON();
				$('#lineas_registros').val(JSON.stringify(table));

				$('#form_almacenar_registros').submit();
				
				/*
				$("#div_resultado").fadeOut( 1000 );

				$("#div_spin").show();
		 		$("#div_cargando").show();
				
				var form = $('#form_almacenar_registros');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("form_almacenar_registros"));

				$("#div_resultado").html( '' );

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
			    */
			});

		});
	</script>
@endsection