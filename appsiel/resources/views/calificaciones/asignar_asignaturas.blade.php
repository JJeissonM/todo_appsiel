@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Asignaciones</h4>
		    <hr>
			
			{{ Form::open(['url'=>'calificaciones/guardar_asignacion_asignatura?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'id'=>'formulario']) }}


				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('periodo_lectivo_id','','Año lectivo', $periodos_lectivos,['required'=>'required']) }}
				</div>


				<div class="row" style="padding:5px;">
					{{ Form::bsSelect('curso_id','','Seleccionar curso',$cursos,['required'=>'required']) }}
				</div>

				<br/>

				<div class="row">
	                <div class="col-md-6">
		            	<div class="row" style="padding:5px;">
		            		{{ Form::bsSelect('asignatura_id', null, 'Asignatura',[], ['required'=>'required'] ) }}
		            	</div>
	                </div>

	                <div class="col-md-6">
		            	<div class="row" style="padding:5px;">
		            		{{ Form::bsText('intensidad_horaria',null,'Intensidad horaria',['required'=>'required']) }}
		            	</div>
	                </div>
				</div>

				<div class="row">

	                <div class="col-md-6">
		            	<div class="row" style="padding:5px;">
		            		{{ Form::bsText('orden_boletin',null,'Orden boletín',['required'=>'required']) }}
		            	</div>
	                </div>
	                
	                <div class="col-md-6">
		            	<div class="row" style="padding:5px;">
		            		{{ Form::bsSelect('maneja_calificacion',null,'Maneja calificación',['1'=>'Si','0'=>'No'],[]) }}
		            	</div>
	                </div>


				</div>

				<br/>

				<div class="row" style="padding:5px;">
					<div class='alert alert-danger' id="lbl_danger" style="display: none;">
					        <em> Asignatura eliminada. </em>
					</div>

					<div class='alert alert-success' id="lbl_ok" style="display: none;">
					        <em> Asignatura agregada correctamente. </em>
					</div>


				</div>

					{{ Form::hidden('url_id',Input::get('id'))}}
					{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
			{{ Form::close() }}
					

					
				<button class="btn btn-success btn-xs" id="btn_guardar"> <i class="fa fa-save"></i> Guardar</button>
			<br/><br/>
			
			<div id="listado">
				{!! $tabla !!}
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		$(document).ready(function(){

			$("#periodo_lectivo_id").focus();
		    
		    $("#periodo_lectivo_id").on('change',function(){
		    	resetear_controles();
				$("#curso_id").val('');

		    	if( $(this).val() != '' ){
		    		$("#curso_id").focus();
				}else{
					$('#periodo_lectivo_id').focus();
					alert('Debe escoger un año lectivo.');
				}

		    });
		    
		    $("#curso_id").on('change',function(){
		    	resetear_controles();

		    	cargar_tabla_asignaturas_x_curso();
			});
		    
		    $(document).on('click','#btn_actualizar_lista',function(){
		    	cargar_tabla_asignaturas_x_curso();
			});

			function cargar_tabla_asignaturas_x_curso()
			{
				if( $('#periodo_lectivo_id').val() == '' )
		    	{
		    		$("#curso_id").val('');
					$('#periodo_lectivo_id').focus();
					alert('Debe escoger un año lectivo.');
					return false;
				}


				var curso_id = $('#curso_id').val();
				var periodo_lectivo_id = $('#periodo_lectivo_id').val();

		    	if( curso_id != '' ){

				    $('#div_cargando').show();

					var url = "{{ url('calificaciones/asignar_asignaturas/get_tabla_asignaturas_asignadas/') }}" + "/" + periodo_lectivo_id + "/" + curso_id;
					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){
		                    $('#div_cargando').hide();

							$("#asignatura_id").html( datos[0] );
							$("#listado").html( datos[1] );
		    				$('#asignatura_id').focus();
				        }
				    });
				}else{
					alert('Debe escoger un curso.')
					$('#curso_id').focus();
					$('#div_cargando').hide();
					$("#asignatura_id").html('<option value=""></option>');
			    	$('#intensidad_horaria').val('');
			    	$('#orden_boletin').val('');
				}
			}


		    $("#asignatura_id").on('change',function(){
		    	$('#lbl_danger').hide();
		    	$('#lbl_ok').hide();
		    	$('#intensidad_horaria').focus();
		    });

		    $('#intensidad_horaria').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#orden_boletin').focus();				
				}		
			});

		    $('#orden_boletin').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#btn_guardar').focus();				
				}		
			});

		    function resetear_controles()
		    {
		    	$('#lbl_danger').hide();
		    	$('#lbl_ok').hide();
				
				$("#asignatura_id").html('<option value=""></option>');
		    	$('#intensidad_horaria').val('');
		    	$('#orden_boletin').val('');
		    	
		    	$("#listado").html('');
		    }


		    // ---------  ELIMINAR
		    $(document).on('click', '.eliminar', function(event) {
		    	event.preventDefault();

		    	if (confirm("Realmente quiere eliminar esa asignacion?")) 
		    	{

			    	$('#div_cargando').show();
			    	$('#lbl_ok').hide();
			    	$('#lbl_danger').hide();

			    	var periodo_lectivo_id = $(this).attr('data-periodo_lectivo_id');
			    	var curso_id = $(this).attr('data-curso_id');
			    	var asignatura_id = $(this).attr('data-asignatura_id');

			    	var fila = $(this).closest("tr");					

			    	var url = "{{ url('calificaciones/eliminar_asignacion_asignatura') }}" + "/" + periodo_lectivo_id + "/" + curso_id + "/" + asignatura_id;
						$.ajax({
				        	url: url,
				        	type: 'get',
				        	success: function(datos){
			                    $('#div_cargando').hide();

			                    var ih = parseFloat( $('#ih_total').text() );

			                    var ih2 = ih - parseFloat( datos );

			                    $('#ih_total').text( ih2 );

			                    fila.remove();

			                    $('#lbl_danger').show();
			                    
								$("#asignatura_id").focus();
					        },
					        error: function(xhr) {
						        alert('Error en los datos seleccionados. '+xhr);
						    }
						});
				}else {
			    	$('#div_cargando').hide();
					$('#lbl_danger').hide();
			        return false;
			    }
		    });

		    // --------  Guardar
		    $("#btn_guardar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		    	var curso_id = $("#curso_id").val();
		    	var asignatura_id = $("#asignatura_id").val();
		    	
			    $('#div_cargando').show();
		    	$('#lbl_ok').hide();
		    	$('#lbl_danger').hide();

				var form = $('#formulario');
				var url = form.attr('action');
				var datos = form.serialize();

				$.ajax({
		        	url: url,
		        	type: 'POST',
		        	data : datos,
		        	success: function(tabla){
		        		$('#div_cargando').hide();
				    	$('#lbl_danger').hide();

				    	$('#lbl_ok').show();

						$('#lista_asignaciones').find('tbody:last').append(tabla[0]);

						var ih = parseFloat( $('#ih_total').text() );

	                    var ih2 = ih + parseFloat( tabla[1] );

	                    $('#ih_total').text( ih2 );

	                    $('#intensidad_horaria').val('');
    					$('#orden_boletin').val('');

    					$("#asignatura_id option[value='"+asignatura_id+"']").remove();
    					$("#asignatura_id").val("");
    					$("#asignatura_id").focus();

			        },
			        error: function(data) {
	                    $('#div_cargando').hide();
						$("#listado").hide();
				        $("#lbl_danger").show();
						$("#lbl_ok").hide();
				    	$("#lbl_danger").html( "<br/> Error en lo datos seleccionados.<br/><br/>" + data.responseText );
				    }
			    });			    
			});

			var valor_actual, elemento_modificar, elemento_padre;
					
				// Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
				$(document).on('dblclick','.elemento_modificar',function(){
					
					elemento_modificar = $(this);

					elemento_padre = elemento_modificar.parent();

					valor_actual = $(this).html();

					elemento_modificar.hide();

					elemento_modificar.after( '<input type="text" name="valor_nuevo" id="valor_nuevo" style="display:inline;"> ');

					document.getElementById('valor_nuevo').value = valor_actual;
					document.getElementById('valor_nuevo').select();

				});

				// Si la caja de texto pierde el foco
				$(document).on('blur','#valor_nuevo',function(){
					guardar_valor_nuevo( $(this) );
				});

				// Al presiona teclas en la caja de texto
				$(document).on('keyup','#valor_nuevo',function(){

					var x = event.which || event.keyCode; // Capturar la tecla presionada

					// Abortar la edición
					if( x == 27 ) // 27 = ESC
					{
						elemento_padre.find('#valor_nuevo').remove();
			        	elemento_modificar.show();
			        	return false;
					}

					// Guardar
					if( x == 13 ) // 13 = ENTER
					{
			        	guardar_valor_nuevo( $(this) );
					}
				});

				function guardar_valor_nuevo( caja_texto )
				{
					if( !validar_input_numerico( $( document.getElementById('valor_nuevo') ) ) )
					{
						return false;
					}

					var valor_nuevo = document.getElementById('valor_nuevo').value;

					// Si no cambió el valor_nuevo, no pasa nada
					if ( valor_nuevo == valor_actual) { return false; }

					$('#div_cargando').show();

					var asignatura_id = caja_texto.prev().attr('data-asignatura_id');

					$.ajax({
			        	url: "{{url('calificaciones_cambiar_orden_asignatura')}}" + "/" + $('#periodo_lectivo_id').val() + "/" + $('#curso_id').val() + "/" + asignatura_id + "/" + valor_nuevo,
			        	method: "GET",
			        	success: function( data ){
			        		$('#div_cargando').hide();
					    	
					    	elemento_modificar.html( valor_nuevo );
							elemento_modificar.show();

							elemento_padre.find('#valor_nuevo').remove();

				        },
				        error: function( data ) {
		                    $('#div_cargando').hide();
							elemento_padre.find('#valor_nuevo').remove();
				        	elemento_modificar.show();
				        	return false;
					    }
				    });

				}
		});
	</script>
@endsection