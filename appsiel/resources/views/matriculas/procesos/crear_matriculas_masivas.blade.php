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
						{{ Form::open(['url'=>'sga_matriculas_masivas_cargar_listado','id'=>'formulario_inicial','files' => true]) }}
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
									<button class="btn btn-success" id="btn_cargar_estudiantes"> <i class="fa fa-list"></i> Listar estudiantes </button>
								</div>
							</div>
						{{ Form::close() }}
					</div>
						
				</div>
					
			</div>
		</div>
				
	</div>

	{{ Form::open(['url'=>'sga_matriculas_masivas_generar','id'=>'formulario_lista_estudiantes','files' => true]) }}
		<input type="hidden" name="cantidad_estudiantes" id="cantidad_estudiantes" value="0">
		<input type="hidden" name="lineas_estudiantes" id="lineas_estudiantes" value="0">
	{{ Form::close() }}

	<div class="row" id="div_resultado">
			
	</div>

	<div class="row" id="div_resultado2">
			
	</div>

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			var opcion_seleccionada;

			$(document).on('change',".checkbox_fila",function(){
				if ( $(this).is(':checked') )
				{
					opcion_seleccionada++;
					$('#cantidad_estudiantes').val( $('#cantidad_estudiantes').val() + 1);
					$(this).next('.checkbox_aux').text(1);
				}else{
					$('#cantidad_estudiantes').val( $('#cantidad_estudiantes').val() - 1);
					$(this).next('.checkbox_aux').text(0);
				}
			  });

			$(document).on('change',"#checkbox_head",function(){

				$(".checkbox_fila").each(function(){
					if ( $('#checkbox_head').is(':checked') )
					{
						$(this).prop('checked',true);
						$('#cantidad_estudiantes').val( $('#cantidad_estudiantes').val() + 1);
						$(this).next('.checkbox_aux').text(1);
					}else{
						$(this).prop('checked',false);
						$('#cantidad_estudiantes').val( $('#cantidad_estudiantes').val() - 1);
						$(this).next('.checkbox_aux').text(0);
					}					
				});

			});

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


			$(document).on('click',"#btn_promover",function(event){
		    	event.preventDefault();

        		$("#div_resultado2").html( '' );

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		    	if ( $('#cantidad_estudiantes').val() == 0) { alert('Debe seleccionar al menos un estudiante.'); return false; }

		    	if ( $('#curso_id').val() == $('#curso_promover_id').val() ) { alert('No se pueden promover los estudiantes hacia el mismo curso actual: ' + $('#curso_promover_id option:selected').text() ); return false; }

		    	if ( $('#periodo_lectivo_id').val() == $('#periodo_lectivo_promover_id').val() ) { alert('No se pueden promover los estudiantes hacia el mismo Año lectivo: ' + $('#periodo_lectivo_promover_id option:selected').text() ); return false; }

		    	if ( !confirm('¿Está seguro de promover todos los estudiantes seleccionados al nuevo curso ' + $('#curso_promover_id option:selected').text() + '?') )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();

        		var table = $('#tabla_lista_estudiantes').tableToJSON();
				// Se asigna el objeto JSON a un campo oculto del formulario
		 		$('#lineas_estudiantes').val(JSON.stringify(table));

				var form = $('#formulario_lista_estudiantes');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("formulario_lista_estudiantes"));
				/*
				var prestaciones = '';
				var i;
				$(".check_prestacion").each(function(){
					if ( $(this).is(':checked') )
					{
						prestaciones = prestaciones + '-' + $(this).val();
						//i++;
					}
				  });

				var url = "{ { url('nom_retirar_prestaciones_sociales') }}" + '/' + $('#nom_doc_encabezado_id').val() + '/' + prestaciones;
				*/
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

        			$("#div_resultado2").html( respuesta );
			    });
		    });

		});
	</script>
@endsection