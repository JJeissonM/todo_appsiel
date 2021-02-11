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
						{{ Form::open(['url'=>'nom_generar_archivo_consignar_cesantias','id'=>'formulario_inicial','files' => true]) }}
							

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

	<a class="btn-gmail btn-excel" id="btn_excel2" style="display: none;" title="liquidacion_cesantias"><i class="fa fa-file-excel-o"></i></a>

	<div class="row" id="div_resultado">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			var opcion_seleccionada = 0;

			$('#nom_doc_encabezado_id').focus();

			$("#btn_visualizar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
		 		$("#btn_excel2").hide();
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
        			$("#btn_excel2").show();


        			$("#div_resultado").html( respuesta );
        			$("#div_resultado").fadeIn( 1000 );
			    });
		    });

		    $('#btn_excel2').click(function (event) {
				event.preventDefault();

				var nombre_listado = $(this).attr('title');
				var tT = new XMLSerializer().serializeToString(document.querySelector('.table_registros')); //Serialised table
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

		});
	</script>
@endsection