@extends('layouts.principal')

@section('content')

	<style>
		table th {
		    padding: 15px;
		    text-align: center;
			border-bottom:solid 2px;
			background-color: #E5E4E3;
		}
		table td {
		    padding: 2px;
		}
	</style>

	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4 style="color: gray;">Ingreso de calificaciones</h4>
		    <hr>
			{{ Form::open( array('url'=>'/calificaciones/calificar2?id='.Input::get('id'), 'id'=>'form_filtros' ) ) }}

				<div class="row">
                	<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{ Form::bsSelect('id_periodo','','Seleccionar periodo',$periodos,['required'=>'required']) }}
						</div>
                	</div>

                	<div class="col-md-6">
                		<div class="row" style="padding:5px;">
							{{ Form::bsSelect('curso_id','','Seleccionar curso',$cursos,['required'=>'required']) }}
						</div>
                	</div>
                </div>

				<div class="row">
                	<div class="col-md-6">
						<div class="row" style="padding:5px;">
							{{Form::bsSelect('id_asignatura', null, 'Asignatura',[], ['required'=>'required'])}}
						</div>
                	</div>

                	<div class="col-md-6">
                		<button class="btn btn-primary" id="btn_continuar">
							<i class="fa fa-btn fa-arrow-right"></i>Continuar
						</button>
                	</div>
                </div>	
				{{ Form::hidden('id_app',Input::get('id')) }}
				
				{{ Form::hidden( 'ruta', 'calificaciones/create?id='.Input::get('id') ) }}

			{{ Form::close() }}

			<div id="div_form_ingreso">
				
			</div>
		</div>
	</div>
	<br/><br/>	
@endsection


@section('scripts')
	<script>
		$(document).ready(function(){

			$("#id_periodo").focus();

			$("#id_periodo").on('change',function(){
				$('#div_form_ingreso').html( '' );
				$("#curso_id").focus();
			});

			$("#curso_id").on('change',function(){
				var periodo_id = $("#id_periodo").val();

				if ( periodo_id == '')
				{
					alert('Debe seleccionar primero un periodo.');
					$(this).val( '' );
					$("#id_periodo").focus();
					return false;
				}

				$('#div_form_ingreso').html( '' );
				var curso_id = $(this).val();
				$("#id_asignatura").html('<option value=""></option>');
		    	
		    	if( curso_id != '' ){

				    $('#div_cargando').show();

					var url = "{{ url('get_select_asignaturas') }}" + "/" + curso_id + "/" + periodo_id;
					$.ajax({
			        	url: url,
			        	type: 'get',
			        	success: function(datos){
		                    
		                    $('#div_cargando').hide();
							
							$("#id_asignatura").html(datos);
							
							$("#id_asignatura").focus();
				        },
				        error: function(xhr) {
		                    $('#div_cargando').hide();
					        alert('Error en los datos seleccionados. '+xhr);
					    }
				    });
				}else{
					$("#id_asignatura").html('<option value=""></option>');
				}
			});

			$("#id_asignatura").on('change',function(){
				$('#div_form_ingreso').html( '' );
				$("#btn_continuar").focus();
			});

			$("#btn_continuar").on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				$('#form_filtros').submit();
				
				/*
				$('#div_form_ingreso').html( '' );
				$('#div_cargando').show();

				var form_consulta = $('#form_filtros');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize();
				// Enviar formulario de ingreso de productos vía POST
				$.post(url,datos,function(respuesta){
					$('#div_cargando').hide();

					$('#div_form_ingreso').html( respuesta );

				})
				*/
			});

			checkCookie();

			var escala_min = parseFloat( $('#escala_min').val(), 10);
			var escala_max = parseFloat( $('#escala_max').val(), 10);

	   		// 8 = Backspace
	   		// 9 = Tab
			// 16 = Shift
	   		var teclas_especiales = [9,16];

	   		var guardando = false;



	   		// Guardar calificaciones cada diez (10) segundos
	   		/*setInterval( function(){ 
	   			if( !guardando )
	   			{
	   				guardar_calificaciones();
	   			}
	   		}, 10000);
			*/

			// Vaciar los inputs que tienen cero (0)
			$("input[type=text]").each(function () {
				var val = $(this).val();
				if(val == 0){
					$(this).val("");
				}
			});

			// Sombrear la columna al seleccionar text input
			$("input[type=text]").on('focus',function(){
				var id = $(this).attr('id');
				var vec_id = id.split("_");
				$(".celda_"+vec_id[0]).css('background-color','#a3e7fe');
			});

			// Quitar Sombra de la columna cuando el text input pierde el foco
			$("input[type=text]").on('blur',function()
			{
				var id = $(this).attr('id');
					var vec_id = id.split("_");
					$(".celda_"+vec_id[0]).css('background-color','transparent');	
			});


			// Cuando se presiona una caja de texto
			$("input[type=text]").keyup(function(e) 
			{
				if(e.keyCode!=40){ //Si NO se presiona flecha hacia abajo
				   if(e.keyCode!=38){ // Si NO se presiona flecha hacia arriba
				   		if ( $.inArray(e.keyCode,teclas_especiales) < 0 )
				   		{	// Si NO se presionan teclas especiales

				   			if ( validar_valor_ingresado( $(this)  ) ) 
				   			{
				   				calcular_promedio( $(this) );	
				   			}

				   			// Cuando cambie el valor de una celda, se cambian los mensajes
				   			$('#mensaje_inicial').hide();
							$('#mensaje_guardadas').hide();
							$('#mensaje_sin_guardar').show();
							$('#bs_boton_guardar').prop('disabled', false);

				   		}

					}else{
						var ID = $(this).attr("id");
						var n = ID.split("_");
						var j = parseInt(n[1])-1;
						var sig = ("#"+n[0]+"_"+j);
						$(sig).focus().select();
					}
				}else{ // Si SI se presiona flecha hacia abajo

					var ID = $(this).attr("id");
					var n = ID.split("_");
		    		var j = parseInt(n[1])+1;
					var sig = ("#"+n[0]+"_"+j);
					$(sig).focus().select();
				}
			});

			$('#bs_boton_guardar').click(function(){
				guardar_calificaciones();
			});

			window.guardar_calificaciones=function(){
				// DESCOMENTAR LA LINEA DE ABAJO EN PRODUCCION
				$('#bs_boton_guardar').prop('disabled', true);
				
				guardando = true;

				var item;

				$('#mensaje_inicial').hide();
				$('#mensaje_sin_guardar').hide();
				$('#mensaje_guardando').show();

				var linea = 1;
				$('#tabla_registros > tbody > tr').each( function(i,item)
				{
					$('#codigo_matricula').val( $(item).attr('data-codigo_matricula') );
					$('#id_estudiante').val( $(item).attr('data-id_estudiante') );

					$('#id_calificacion').val( $(item).attr('data-id_calificacion') );
					$('#calificacion').val( $('#calificacion_texto'+linea).val() );

					$('#id_calificacion_aux').val( $(item).attr('data-id_calificacion_aux') );

					var c=1;
					$('.valores_'+linea).each(function () 
					{
						$('#C'+c).val( this.value );
						c++;
						
					});

					var url = $("#formulario").attr('action');
					var data = $("#formulario").serialize();

					$.post(url, data, function( respuesta ){

						$(item).attr('data-id_calificacion',respuesta[0]);
						$(item).attr('data-calificacion',respuesta[1]);
						$(item).attr('data-id_calificacion_aux',respuesta[2]);
					});

					linea++;
				});

				$('#mensaje_guardando').hide();
				$('#mensaje_guardadas').show();
				
				guardando = false;
			};

			/*
			  * calcular_promedio
			  * obj corresponde al text input, se usa para obtener la fila de la tabla
			*/
			function calcular_promedio( obj )
			{	
				var total, valid_labels, average, val;
				var clase = '.' + obj.attr('class');

				average = 0;
				total = 0;
				valid_labels = 0;
				$(clase).each(function () 
				{

					if ( !validar_valor_ingresado( $('#'+this.id) ) ) 
					{
						return;
					}

					val = this.value; // este this es diferente a $(this)

					if(val !== "" && val !== 0)
					{
						val = parseFloat(val, 10);
					    valid_labels += 1;
					    total = total + val;
					}
				});

				if ( valid_labels != 0) 
				{
					average = total / valid_labels;	
				}

				var n = clase.split("_");
				var j = n[1];				
				$('#calificacion_texto'+j).val(average);
			}

			// Validar que sea númerico y que esté entre la escala de valoración
			function validar_valor_ingresado( obj )
			{
				var valido = true;
				if ( obj.val() !='' && !$.isNumeric( obj.val() ) ) 
		   		{	
		   			alert("Debe ingresar solo números. Para decimales use punto (.). No la coma (,).");
		   			obj.val( '' );
		   			calcular_promedio( obj );
		   			valido = false;
		   		}

		   		if ( obj.val() !='' && (obj.val() < escala_min || obj.val() > escala_max) ) 
		   		{	
		   			alert("La calificación ingresada está por fuera de la escala de valoración. Ingrese un número entre "+escala_min+" y "+escala_max);
		   			obj.val( '' );
		   			calcular_promedio( obj );
		   			valido = false;
		   		}

		   		return valido;
			}

			$(".encabezado_calificacion").on('click', function(e){
				e.preventDefault();
				$("#alert_mensaje").hide();

				$("#contenido_modal").html( '' );

		        $("#myModal").modal(
		        	{keyboard: 'true'}
		        );
				$('#div_spin').fadeIn();
				$('.btn_edit_modal').hide();


				var url = "{{url('/')}}" + '/calificaciones_encabezados/create?columna_calificacion=' + $(this).val() + '&periodo_id=' + $('#id_periodo').val() + '&curso_id=' + $('#curso_id').val() + '&asignatura_id=' + $('#id_asignatura').val() + '&anio=' + $('#anio').val();

				$.get(url, function( respuesta ){
					$('#div_spin').hide();
					$("#contenido_modal").html( respuesta );
				});
				
			});

			$(document).on('click', '.btn_save_modal', function(e) {

				e.preventDefault();
				$("#alert_mensaje").hide();

				$('#div_spin').fadeIn();

				var url = $("#formulario_modal").attr('action');
				var data = $("#formulario_modal").serialize();

				$.post(url, data, function( respuesta ){
					$('#div_spin').hide();
					$("#alert_mensaje").fadeIn();
					if ( respuesta == "true" ) {
						$("#myModal").modal('hide');
						alert('Encabezado de la calificación guardado.');
					}
				});
				
			});

			function setCookie(cname, cvalue, exdays) {
			  var d = new Date();
			  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			  var expires = "expires="+d.toUTCString();
			  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
			}

			function getCookie(cname) {
			  var name = cname + "=";
			  var ca = document.cookie.split(';');
			  for(var i = 0; i < ca.length; i++) {
			    var c = ca[i];
			    while (c.charAt(0) == ' ') {
			      c = c.substring(1);
			    }
			    if (c.indexOf(name) == 0) {
			      return c.substring(name.length, c.length);
			    }
			  }
			  return "";
			}

			function checkCookie() {
			  var mostrar_ayuda = getCookie("mostrar_ayuda_calificaciones_form");

			  //alert(mostrar_ayuda);

			  if (mostrar_ayuda == "true" || mostrar_ayuda == "") {
			    
			    $("#myModal").modal(
		        	{keyboard: 'true'}
		        );

		        $(".modal-title").html('Ayuda');
		        $(".btn_edit_modal").hide();
		        $(".btn_save_modal").hide();

		        /* <li class="list-group-item">Las calificaciones se almacenan automáticamente cada diez (10) segundos.</li> */
		        $("#contenido_modal").html('<div class="well well-lg"><ul class="list-group"><li class="list-group-item">Se pueden guardar las calificaciones en cualquier momento presionando el botón guardar y seguir ingresando información.</li>  <li class="list-group-item">Verifique que antes de salir de la página se muestre el mensaje <spam id="mensaje_guardadas" style="background-color: #b1e6b2;">Calificaciones guardadas</spam></li></ul> <div class="checkbox">  <label><input type="checkbox" name="mostrar_ayuda_calificaciones_form" id="mostrar_ayuda_calificaciones_form" value="true">No volver a mostrar este mensaje.</label> </div></div>');
		        
		        setCookie("mostrar_ayuda_calificaciones_form", true, 365);

		        $(document).on('click','#mostrar_ayuda_calificaciones_form',function(){
		        	if( $(this).val() == "true" ){
		        		$(this).val( "false" );
		        		setCookie("mostrar_ayuda_calificaciones_form", "false", 365);
		        	}else{
		        		$(this).val( "true" );
		        		setCookie("mostrar_ayuda_calificaciones_form", "true", 365);
		        	}
		        });
			  }
			}



		});
	</script>
@endsection