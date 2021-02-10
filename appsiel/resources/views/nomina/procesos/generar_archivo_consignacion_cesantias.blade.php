@extends('core.procesos.layout')

@section( 'titulo', 'Generar archivo para consignación de cesantías' )

@section('detalles')
	<div style="text-align: justify; text-justify: inter-word;">
		<p>
			Las empresas cuentan hasta el 14 de febrero de cada año para hacer el pago a los respectivos Fondos de Pensiones y Cesantías a los cuales están afiliados cada uno de sus empleados.
		</p>
		<p>
			Este proceso permite generar archivos de interfaces para cargar a los distintos Operadores o Fondos, segun las estructuras de cada uno.
		</p>
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
								<label class="control-label col-sm-4" > <b> *Documento de liquidación: </b> </label>

								<div class="col-sm-8">
									{{ Form::select( 'nom_doc_encabezado_id', App\Nomina\NomDocEncabezado::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'nom_doc_encabezado_id', 'required' => 'required' ]) }}
								</div>
							</div>

							<div class="row" style="padding:5px;">
								<label class="control-label col-sm-4" > <b> *Formato Entidad: </b> </label>

								<div class="col-sm-8">
									{{ Form::select( 'formato_entidad', [ '' => '', 'aportes_en_linea' => 'Aportes en línea', 'soi' => 'SOI', 'arus' => 'ARUS', 'simple_sas' => 'Simple S.A', 'afp_proteccion' => 'Protección', 'porvenir' => 'Porvenir', 'skandia' => 'Skandia', 'fondo_nacional_del_ahorro' => 'Fondo Nacional del Ahorro', 'colfondos' => 'Colfondos' ], null, [ 'class' => 'form-control', 'id' => 'formato_entidad' ]) }}
								</div>
							</div>

							<div class="row" style="padding:5px;">
								<label class="control-label col-sm-4" > <b> *Fondo destino consignación: </b> </label>

								<div class="col-sm-8">
									{{ Form::select( 'fondo_cesantias_destino', [ '' => '', '02' => 'Fondo de cesantías Protección', '03' => 'Porvenir Cesantías', '10' => 'Colfondos S.A.', '15' => 'Fondo Nacional del Ahorro (FNA)', '19' => 'Old Mutual (Antes Skandia)' ], null, [ 'class' => 'form-control', 'id' => 'fondo_cesantias_destino' ]) }}
								</div>
							</div>

							<div class="row" style="padding:5px; text-align: center;">
								<div class="col-md-6">
									<button class="btn btn-success" id="btn_visualizar"> <i class="fa fa-eye"></i> Visualizar </button>
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

			$("#btn_visualizar").on('click',function(event){
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