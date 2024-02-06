@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	@can('CALI: Asignacion asignaturas')
		<a class="btn btn-primary btn-sm" href="{{ url( 'calificaciones/asignar_asignaturas?id=2&id_modelo=' ) }}" title="Revisar Asignaciones de Asignaturas" target="_blank"><i class="fa fa-eye"></i> Revisar Asignaciones de Asignaturas </a>
		<br><br>
	@endcan	

	<div class="row">
		<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">

		    <h4 style="color: gray;">Asignación carga académica</h4>
		    <hr>
			
			{{ Form::open(['url'=>'academico_docente/profesores/guardar_asignacion','id'=>'formulario']) }}

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('id_user', $profesor->id, 'Profesor', $profesores, ['required'=>'required']) }}
				</div>
				
				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('periodo_lectivo_id', $periodo_lectivo->id,'Año lectivo', $periodos_lectivos,['required'=>'required']) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('curso_id','','Seleccionar curso',$cursos,['required'=>'required']) }}
				</div>


				<div class="row" style="padding:5px;">
					{{Form::bsSelect('id_asignatura', null, 'Asignatura',[], ['required'=>'required'])}}
				</div>
					
			{{ Form::close() }}
			
			<div style="width:100%; text-align:center;">
				<button class="btn btn-success btn-sm" id="btn_guardar">Guardar</button>
			</div>
		    <hr>
			
			<div class="alert alert-danger" id="div_error" style="display: none;">
				<em> {!! session('mensaje_error') !!}</em>
			</div>

			<div class='alert alert-success' id="div_success" style="display: none;">
		        <em> Asignación agregada correctamente. </em>
		    </div>

		    <div class='alert alert-danger' id="lbl_danger" style="display: none;">
					        <em> Asignación eliminada. </em>
					</div>

		    <div class='alert alert-warning' id="div_msg_asignaturas" style="display: none;">
		        <em> No hay asignaturas pendientes por asignar en ese curso. </em>
		    </div>

			<div id="listado">
				{{ Form::bsBtnExcel('asignacion_academica_docente') }}

					<h3>Carga Académica asignada</h3>
					
					<div class="row">
						<div class="col col-md-12">

							<div id="tabla_asignaciones">
								@include( 'academico_docente.profesores.asignacion_academica_tabla' )	
							</div>						

						</div>
					</div>
			</div>

			<div id="listado2" style="display: none;">
				
			</div>
		</div>
	</div>
		<br><br><br><br>
@endsection

@section('scripts')
	<script>
		$(document).ready(function(){

			$('#btn_excel').show();

			$("#curso_id").focus();			
		    
		    $("#id_user").on('change',function(){
		    	resetear_controles();
				$("#periodo_lectivo_id").val('');
				$("#curso_id").val('');
		    	$('#tabla_asignaciones').html('');
				
				$("#periodo_lectivo_id").focus();
			});
		    
		    $("#periodo_lectivo_id").on('change',function(){
		    	resetear_controles();
				$("#curso_id").val('');

		    	if( $(this).val() != '' ){

		    		var user_id = $('#id_user').val();
		    		var periodo_lectivo_id = $('#periodo_lectivo_id').val();

		    		$('#div_cargando').show();
		    		$('#tabla_asignaciones').html('');

					var url = "{{ url('academico_docente/get_tabla_carga_academica/') }}" + "/" + user_id + "/" + periodo_lectivo_id;

					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){
		                    $('#div_cargando').hide();
							$("#tabla_asignaciones").html( datos );
		    				$("#curso_id").focus();
				        }
				    });

				}else{
					$('#periodo_lectivo_id').focus();
					alert('Debe escoger un año lectivo.');
				}

		    });
		    
		    $("#curso_id").on('change',function(){
				var curso_id = $(this).val();

				var periodo_lectivo_id = $('#periodo_lectivo_id').val();
		    	
		    	if( curso_id != '')
		    	{
		    		
		    		if( $('#periodo_lectivo_id').val() == '' )
			    	{
			    		$("#curso_id").val('');
						$('#periodo_lectivo_id').focus();
						alert('Debe escoger un año lectivo.');
						return false;
					}

		    		$('#div_cargando').show();
		    		resetear_controles();

					var url = "{{ url('academico_docente/profesores/buscar_asignaturas/') }}" + "/" + curso_id + "/" + periodo_lectivo_id;
					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){

		                    $('#div_cargando').hide();
							
							$("#id_asignatura").html(datos);
							
							$("#id_asignatura").focus();
							
							if( datos == '<option value="">Seleccionar... </option>')
							{
								$("#div_msg_asignaturas").show();
							}
				        },
				        error: function(xhr) {
					        alert('Error en los datos seleccionados. '+xhr);
					    }
				    });
				}else{
					$("#id_asignatura").html('<option value=""></option>');
				}
			});

			$("#id_asignatura").on('change',function(){
				$("#btn_guardar").focus();
			});

		    function resetear_controles()
		    {
				$("#div_error").hide();
				$("#div_success").hide();
				$('#lbl_danger').hide();
				$("#div_msg_asignaturas").hide();
				$("#id_asignatura").html( '<option value=""></option>' );
		    }

		    // ++++++++++++++++++++++
		    // Guardar
		    $("#btn_guardar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}
		    	
		    	var curso_id = $("#curso_id").val();

		    	var id_asignatura = $("#id_asignatura").val();


			    $('#div_cargando').show();

				var form = $('#formulario');
				var url = form.attr('action');
				var datos = form.serialize();

				$.ajax({
		        	url: url,
		        	type: 'POST',
		        	data : datos,
		        	success: function(tabla){

			    		$('#div_cargando').hide();

			    		if (tabla != 'No') {
							$('#lista_asignaciones').find('tbody:last').append(tabla[0]);

							$("#id_asignatura option[value='"+id_asignatura+"']").remove();
							
							$("#id_asignatura").val('');
							
							$("#div_success").show();
							$('#lbl_danger').hide();

							$("#id_asignatura").focus();

							var ih = parseFloat( $('#ih_total').text() );

		                    var ih2 = ih + parseFloat( tabla[1] );

		                    $('#ih_total').text( ih2 );

		                    actualizar_total_asignaturas( 1 );

						}else{
							alert('No');
						}
			        },
			        error: function(data) {
	                    $('#spin2').hide();
						$("#listado").hide();
						$("#listado2").hide();
				        $("#div_error").show();
						$("#div_success").hide();
						$('#lbl_danger').hide();
				    	$("#div_error").html( "<br/> Error en lo datos seleccionados.<br/><br/>" + data.responseText );
				    }
			    });
			    
			});

			function actualizar_total_asignaturas( nueva_cantidad )
			{
				var total_asignaturas = parseInt( $('#total_asignaturas').val() );
                $('#total_asignaturas').val( total_asignaturas + nueva_cantidad );
                $('#lbl_total_asignaturas').text( total_asignaturas + nueva_cantidad );
			}

			// Elminar asignacion
			 $(document).on('click', '.eliminar_asignacion', function(event){
			 	event.preventDefault();
				
				$("#div_success").hide();
				$("#lbl_danger").hide();
				$('#div_cargando').show();

			 	if ( !confirm("Realmente quiere eliminar esa asignacion?") )
			 	{
			    	$('#div_cargando').hide();
					$('#lbl_danger').hide();
			        return false;
			    }

				var fila = $(this).closest("tr");
		    	var asignacion_id = $(this).attr('data-asignacion_id');

		    	var app_id = getParameterByName('id');
		    	var modelo_id = getParameterByName('id_modelo');

				$.ajax({
		        	url: "{{ url('academico_docente/profesores/eliminar_asignacion') }}" + "/" + asignacion_id + "&id=" + app_id + "?id_modelo=" + modelo_id,
		        	type: 'get',
		        	success: function(datos){
			    		$('#div_cargando').hide();
			    		fila.remove();

			    		$('#lbl_danger').show();

			    		var ih = parseFloat( $('#ih_total').text() );

	                    var ih2 = ih - parseFloat( datos );

	                    $('#ih_total').text( ih2 );

	                    actualizar_total_asignaturas( -1 );

			        },
			        error: function(xhr) {
				        alert('Error al eliminar la asignacion. Intente nuevamente. '+xhr);
				        $("#listado2").show();
						$("#listado2").html(xhr);
						$('#lbl_danger').hide();
						$('#div_cargando').hide();
				    }
			    });
				    
			 });
		});
	</script>
@endsection