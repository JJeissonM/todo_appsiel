@extends('core.procesos.layout')

@section( 'titulo', 'Generar PILA (Planilla Integrada de Autoliquidación de Aportes)' )

@section('detalles')
	<p>
		Este proceso permiter generar la planilla integrada según los últimos cambios y modificaciones definidas en las resoluciones 2388 y 5858 de 2016.
	</p>
	
	<br>
@endsection

@section('formulario')
	<div class="row" id="div_formulario">

		<div class="row">
			<div class="col-md-12">

				<div class="marco_formulario">
					<div class="container-fluid">
						<h4>
							Parámetros de selección
						</h4>
						<hr>
						{{ Form::open(['url'=>'nom_generar_planilla_integrada','id'=>'formulario_inicial','files' => true]) }}
							<div class="row" style="padding:5px;">
								<label class="control-label col-sm-4" > <b> *Fecha final Lapso: </b> </label>

								<div class="col-sm-8">
									<input type="date" name="fecha_final_mes" class="form-control" required="required">
								</div>					 
							</div>
							<br><br>
							<div class="row" style="padding:5px; text-align: center;">
								<div class="col-md-6">
									<button class="btn btn-success" id="btn_generar"> <i class="fa fa-file"></i> Generar </button>
									{{ Form::Spin(48) }}
								</div>

								<div class="col-md-6">
									<button class="btn btn-info" id="btn_descargar" disabled="disabled"> <i class="fa fa-save"></i> Descargar </button>
								</div>
							</div>
						{{ Form::close() }}
					</div>
						
				</div>
					
			</div>
			
		</div>
				
	</div>

	<div class="row" id="div_resultado">
			
	</div>
@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#btn_generar").on('click',function(event){
		    	event.preventDefault();


		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
				$("#div_resultado").html('');

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
			    });
		    });

			$("#btn_descargar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		    	if ( opcion_seleccionada == 0) { alert('Debe seleccionar al menos una prestación.'); return false; }

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
        		$("#div_resultado").html( '' );

				var form = $('#formulario_inicial');
				var prestaciones = '';
				var i;
				$(".check_prestacion").each(function(){
					if ( $(this).is(':checked') )
					{
						prestaciones = prestaciones + '-' + $(this).val();
						//i++;
					}
				  });

				var url = "{{ url('nom_retirar_prestaciones_sociales') }}" + '/' + $('#nom_doc_encabezado_id').val() + '/' + prestaciones;

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