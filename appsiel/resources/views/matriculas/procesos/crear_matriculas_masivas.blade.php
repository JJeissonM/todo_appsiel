@extends('core.procesos.layout')

@section( 'titulo', 'Promoción Académica' )

@section('detalles')
	<p>
		Este Proceso permite generar las matrículas de estudiantes para el año lectivo siguiente.
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
						{{ Form::open(['url'=>'sga_crear_matriculas_masivas','id'=>'formulario_inicial','files' => true]) }}


							<div class="row">
								<div class="col-sm-4">
									<div class="row">
										<label class="control-label col-sm-4" > <b> *Año lectivo: </b> </label>

										<div class="col-sm-8">
											{{ Form::select( 'periodo_lectivo_id', App\Matriculas\PeriodoLectivo::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'periodo_lectivo_id', 'required' => 'required' ]) }}
										</div>
									</div>									
								</div>
								<div class="col-sm-4">
									<div class="row">
										<label class="control-label col-sm-4" > <b> *Curso: </b> </label>

										<div class="col-sm-8">
											{{ Form::select( 'curso_id', App\Matriculas\Curso::opciones_campo_select(),null, [ 'class' => 'form-control', 'id' => 'curso_id', 'required' => 'required' ]) }}
										</div>
									</div>
								</div>
								<div class="col-sm-4">
									{{ Form::label(' ','.') }}
									<button class="btn btn-success" id="btn_cargar_estudiantes"> <i class="fa fa-paper-plane"></i> Cargar estudiantes </button>
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


			$("#btn_cargar_estudiantes").on('click',function(event){
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