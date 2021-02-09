@extends('core.procesos.layout')

@section( 'titulo', 'Liquidar prestaciones sociales' )

@section('detalles')
	<div style="text-align: justify; text-justify: inter-word;">
		<p>
			Este proceso se determina el porcentaje fijo aplicable semestralmente al procedimiento número 2 de ReteFuente, según está contemplado en el artículo 386 del estatuto tributario. Cada semestre se determina el porcentaje de retención que se aplicará mes a mes en los siguientes seis meses.
		</p>
		<p>
			El procedimiento número 2, se debe calcular en los meses de diciembre y de junio de cada año, y se tomará el promedio de los 12 meses anteriores al mes en el que se hace el cálculo, es decir que si se hace el cálculo en diciembre de 2019, se toman los ingresos desde el mes de diciembre de 2018 hasta noviembre de 2019; si el cálculo se hace en junio de 2020, se tomarán los ingresos desde el mes de junio de 2019 hasta mayo de 2020.
		</p>
		<p>
			<b>En el caso que el empleado lleve menos de un año trabajando</b>, se tomarán en cuenta los meses que lleve y se dividirá por ese mismo número. Ejemplo: si el empleado lleva laborando 10 meses, se sumaran los ingresos de esos 10 meses y una vez depurados se dividirán por 10 para determinar la base. Recuerde que cuando se lleva un año o más, la sumatoria resultante de los 12 meses anteriores se dividirá en 13.
		</p>
		
		<br>
	</div>
		
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
						{{ Form::open(['url'=>'nom_calcular_porcentaje_fijo_retefuente','id'=>'formulario_inicial','files' => true]) }}
							
							<div class="row" style="padding:5px;">
								<label class="control-label col-sm-4" > <b> *Opciones de liquidación: </b> </label>

								<div class="col-sm-8">
									{{ Form::select( 'almacenar_registros', ['Previsualizar','Almacenar registros'],null, [ 'class' => 'form-control', 'id' => 'almacenar_registros' ]) }}
								</div>
							</div>

							<div class="row" style="padding:5px;">
								<label class="control-label col-sm-4" > <b> *Cantidad meses a promediar: </b> </label>

								<div class="col-sm-8">
									{{ Form::number( 'meses_a_promediar', 12, [ 'class' => 'form-control', 'id' => 'meses_a_promediar', 'min' => 1 ]) }}
								</div>
							</div>

							<div class="row" style="padding:5px;">
								<label class="control-label col-sm-4" > <b> *Fecha final para promedios: </b> </label>

								<div class="col-sm-8">
									{{ Form::date( 'fecha_final_promedios',null, [ 'class' => 'form-control', 'id' => 'fecha_final_promedios' ]) }}
								</div>
							</div>

							<div class="row" style="padding:5px;">
								<label class="control-label col-sm-4" > <b> *Valor UVT actual: </b> </label>

								<div class="col-sm-8">
									{{ Form::text( 'valor_uvt_actual', (float)config('nomina.valor_uvt_actual'), [ 'class' => 'form-control', 'id' => 'valor_uvt_actual' ]) }}
								</div>
							</div>


							<div class="row" style="padding:5px; text-align: center;">
								<div class="col-md-6">
									<button class="btn btn-success" id="btn_calcular"> <i class="fa fa-calculator"></i> Calcular </button>
									{{ Form::Spin(48) }}
								</div>
								<div class="col-md-6">
									<!-- button class="btn btn-danger" id="btn_retirar"> <i class="fa fa-trash"></i> Retirar </button> -->
								</div>
							</div>
						{{ Form::close() }}
					</div>
						
				</div>
					
			</div>
			<!--
			<div class="col-md-6">
				<h4>
					Empleados del documento
				</h4>
				<hr>
				<div class="div_lista_empleados_del_documento">
					
				</div>
			</div>
		-->
		</div>
				
	</div>

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			var opcion_seleccionada = 0;

			$('#fecha_final_promedios').val( get_fecha_hoy() );

			$("#btn_calcular").on('click',function(event){
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
        			$("#div_resultado").fadeIn( 1000 );
			    });
		    });



			$("#btn_retirar").on('click',function(event){
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