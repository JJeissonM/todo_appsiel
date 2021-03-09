@extends('core.procesos.layout')

@section( 'titulo', 'Promoción Académica' )

@section('detalles')
	<p>
		Este proceso permite TRASLADAR toda la información relacionada con el <b>curso actual</b> de un estudiante hacía un <b>nuevo curso</b>.
		<br>
		<span style="color: red;"> NOTA: Solo se permite hacer traslados entre cursos del mismo grado. Si quiere cambiar al estudiante de grado, debe realizar una nueva matrícula. </span>
		<br>
		Se movera de un curso a otro la siguiente información:
	</p>

	<ul class="list-group">
	  <li class="list-group-item">Matrícula</li>
	  <li class="list-group-item">Asistencia a clases</li>
	  <li class="list-group-item">Control disciplinario</li>
	  <li class="list-group-item">Reconocimientos del estudiante</li>
	  <li class="list-group-item">Calificaciones</li>
	  <li class="list-group-item">Preinformes académicos</li>
	  <li class="list-group-item">Observaciones de boletines</li>
	  <li class="list-group-item">Notas de nivelaciones</li>
	</ul>
	
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
						{{ Form::open(['url'=>'sga_cambio_de_curso_cargar_listado','id'=>'formulario_inicial','files' => true]) }}
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

	<div class="row" id="div_resultado">
			
	</div>

	{{ Form::open(['url'=>'sga_cambio_de_curso_generar','id'=>'formulario_lista_estudiantes','files' => true]) }}
		<input type="hidden" name="cantidad_estudiantes" id="cantidad_estudiantes" value="0">
		<input type="hidden" name="lineas_estudiantes" id="lineas_estudiantes" value="0">

		<input type="hidden" name="nuevo_curso_id" id="nuevo_curso_id" value="0">
		
		<div style="display: none; text-align: center;width: 100%;" id="div_form_cambiar">
			<span class="text-danger">Nota: Al presionar este botón, para los estudiantes seleccionados, se moverá toda la información del curso actual hacia el nuevo curso.</span>
			
			<br><br>

			<button class="btn btn-info btn-lg" id="btn_promover"> <i class="fa fa-rocket"></i> Cambiar de <br> curso </button>
		</div>
		
	{{ Form::close() }}

@endsection

@section('javascripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$("#periodo_lectivo_id").focus();

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

			$(document).on('change',".checkbox_fila",function(){
				if ( $(this).is(':checked') )
				{
					$('#cantidad_estudiantes').val( $('#cantidad_estudiantes').val() + 1);
					$(this).next('.checkbox_aux').text(1);
				}else{
					$('#cantidad_estudiantes').val( $('#cantidad_estudiantes').val() - 1);
					$(this).next('.checkbox_aux').text(0);
				}
			  });

			$("#btn_cargar_estudiantes").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		    	$('#div_form_cambiar').hide();

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
        			$("#checkbox_head").focus();
			    });
		    });


			$(document).on('click',"#btn_promover_check",function(event){
		    	event.preventDefault();

        		$("#div_resultado2").html( '' );

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		    	if ( $('#cantidad_estudiantes').val() == 0)
		    	{ 
		    		alert('Debe seleccionar al menos un estudiante.');
		    		return false;
		    	}

		    	if ( $('#curso_id').val() == $('#curso_promover_id').val() )
		    	{ 
		    		alert('No se pueden cambiar los estudiantes hacia el mismo curso actual: ' + $('#curso_promover_id option:selected').text() );
		    		return false;
		    	}

		 		$('#nuevo_curso_id').val( $('#curso_promover_id').val() );

        		var table = $('#tabla_lista_estudiantes').tableToJSON();
				// Se asigna el objeto JSON a un campo oculto del formulario
		 		$('#lineas_estudiantes').val(JSON.stringify(table));

		 		$('#btn_promover_check').prop('disabled',true);
		 		$("#checkbox_head").prop('disabled',true);
		 		$(".checkbox_fila").prop('disabled',true);
		 		$("#curso_promover_id").prop('disabled',true);

				$('#div_form_cambiar').fadeIn(1000);
		    });

			$(document).on('click',"#btn_promover",function(event){
		    	event.preventDefault();


		    	if ( !confirm('¿Está seguro de promover todos los estudiantes seleccionados al nuevo curso ' + $('#curso_promover_id option:selected').text() + '?') )
		    	{
			 		$("#div_spin").hide();
			 		$("#div_cargando").hide();
		    		return false;
		    	}

		 		$('#formulario_lista_estudiantes').submit();
		    });

		});
	</script>
@endsection