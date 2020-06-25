@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>
	
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

<hr>

@include('layouts.mensajes')
	
<div class="container-fluid">
	<div class="marco_formulario">
	    <h4 style="text-align: center;">
	    	Ingreso de {{ $titulo }} 
			<br> 
			Año lectivo: {{ $periodo_lectivo->descripcion }}
	    </h4>
	    <hr>
		{{ Form::open( [ 'url' => 'calificaciones/almacenar_calificacion', 'method'=> 'POST', 'class' => 'form-horizontal', 'id' => 'formulario'] ) }}
					
			{{ Form::hidden('escala_min', $escala_min_max[0], ['id' =>'escala_min']) }}
			{{ Form::hidden('escala_max', $escala_min_max[1], ['id' =>'escala_max']) }}

			{{ Form::hidden('id_colegio', $id_colegio, ['id' =>'id_colegio']) }}
			{{ Form::hidden('creado_por', $creado_por, ['id' =>'creado_por']) }}
			{{ Form::hidden('modificado_por', $modificado_por, ['id' =>'modificado_por']) }}
			{{ Form::hidden('id_periodo', $periodo->id, ['id' =>'id_periodo']) }}
			{{ Form::hidden('curso_id', $curso->id, ['id' =>'curso_id']) }}
			{{ Form::hidden('anio', $anio, ['id' =>'anio']) }}
			{{ Form::hidden('id_asignatura', $datos_asignatura->id, ['id' =>'id_asignatura']) }}
			{{ Form::hidden('cantidad_estudiantes', $cantidad_estudiantes, ['id' =>'cantidad_estudiantes']) }}			

			{{ Form::hidden('codigo_matricula',null,['id'=>'codigo_matricula']) }}
			{{ Form::hidden('id_estudiante',null,['id'=>'id_estudiante']) }}

			{{ Form::hidden('id_calificacion_aux',null,['id'=>'id_calificacion_aux']) }}
			@for ($c=1; $c < 16; $c++)
				{{ Form::hidden('C'.$c,null,['id'=>'C'.$c]) }}
			@endfor

			{{ Form::hidden('logros',null,['id'=>'logros']) }}

			{{ Form::hidden('calificacion',null,['id'=>'calificacion']) }}
			{{ Form::hidden('id_calificacion',null,['id'=>'id_calificacion']) }}

			{{ Form::hidden('id_app',Input::get('id')) }}		
			{{ Form::hidden('return', $ruta ) }}

		{{Form::close()}}

		<div class="row">
			<div class="col-sm-12">
				<b>Año:</b><code>{{ $anio }}</code>
				<b>Periodo:</b>	<code>{{ $periodo->descripcion }}</code>
				<b>Curso:</b><code>{{ $curso->descripcion }}</code>
				<b>Asignatura:</b><code>{{ $datos_asignatura->descripcion }}</code>

			</div>							
		</div>

		<div class="row">
			<div class="col-sm-12">
				<h4><i class="fa fa-info-circle"> &nbsp; </i>Use las flechas de dirección y tabular para desplazarse: &nbsp;<i class="fa fa-arrow-down"></i>&nbsp;<i class="fa fa-arrow-up"></i>&nbsp;<b >TAB </b></h4>
			</div>
			</br></br>							
		</div>

			<p style="color: gray; text-align: right;" id="mensaje_formulario">
				
				<spam id="mensaje_inicial">
				&nbsp;</spam>
				
				<spam id="mensaje_sin_guardar" style="background-color:#eaabab; display: none;">
				Sin guardar</spam>
				
				<spam id="mensaje_guardando" style="background-color:#a3e7fe; display: none;">
				Guardando...</spam>
				
				<spam id="mensaje_guardadas" style="background-color: #b1e6b2;">
				Calificaciones guardadas</spam>
			</p>

		<div class="row">
			<div class="col-sm-12">

				@yield('tabla')
				
			</div>
		</div>

		<div style="text-align: center; width: 100%;">
			<input class="btn btn-primary btn-xs" id="bs_boton_guardar" type="submit" value="Guardar" disabled="disabled">

			<button class="btn btn-danger btn-xs" id="bs_boton_volver">Volver</button>

			<!-- <a href="{ { url()->previous() }}" class="btn btn-danger btn-xs" id="bs_boton_volver"></a> -->

		</div>

	</div>
</div>

@include('components.design.ventana_modal', [ 'titulo' => 'Ingreso/Actualización encabezados de calificaciones', 'texto_mensaje' => 'Registro actualizado correctamente'])

@endsection

@section('scripts')


	<script language="javascript">
		
		function ventana(id,id_textbox){
			document.getElementById("caja_logro").value = id_textbox;
			
			window.open("{{ url('calificaciones_logros/consultar' )}}" + "/" + id, "Consulta de logros","width=800,height=600,menubar=no")
		}

		function getChildVar(a_value){
			var caja
			caja = document.getElementById("caja_logro").value;
			document.getElementById("logros_"+caja).value = a_value;
		}

		$( document ).ready(function() {

			checkCookie();

			var escala_min = parseFloat( $('#escala_min').val(), 10);
			var escala_max = parseFloat( $('#escala_max').val(), 10);

	   		// 9 = Tab
			// 16 = Shift
	   		// 8 = Backspace
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

			$('#bs_boton_volver').click(function(){
				document.location.href = "{{ url()->previous() }}";
			});

			window.guardar_calificaciones=function(){

				$('#bs_boton_guardar').prop('disabled', true);
				$('#bs_boton_volver').prop('disabled', true);

				
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
					$('#logros').val( $('#logros_'+linea).val() );

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
				$('#bs_boton_volver').prop('disabled', false);

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
				if( obj.attr('class') == 'caja_logros' )
				{
					return true;
				}

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
		        $(".btn_save_modal").show();


				var url = '../calificaciones/encabezados/create?columna_calificacion=' + $(this).val() + '&periodo_id=' + $('#id_periodo').val() + '&curso_id=' + $('#curso_id').val() + '&asignatura_id=' + $('#id_asignatura').val() + '&anio=' + $('#anio').val();

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