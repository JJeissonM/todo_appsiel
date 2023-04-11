@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	<style>
		table th {
			padding: 15px;
			text-align: center;
			border-bottom: solid 2px;
			background-color: #E5E4E3;
		}

		table td {
			padding: 2px;
		}
		#tabla_lineas_registros_calificaciones{
			color: white;
		}

		#tabla_lineas_registros_calificaciones td{
			color: white;
			background-color: white;
		}

		#tabla_lineas_registros_calificaciones th{
			color: white;
			background-color: white;
			border: 0px solid;
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

			<div class="row">
				<div class="col-sm-12">
					<b>Año:</b><code>{{ $anio }}</code>
					<b>Periodo:</b> <code>{{ $periodo->descripcion }}</code>
					<b>Curso:</b><code>{{ $curso->descripcion }}</code>
					<b>Asignatura:</b><code>{{ $datos_asignatura->descripcion }}</code>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-12">
					<h4><i class="fa fa-info-circle"> &nbsp; </i>Use las flechas de dirección y tabular para desplazarse: &nbsp;<i class="fa fa-arrow-down"></i>&nbsp;<i class="fa fa-arrow-up"></i>&nbsp;<b>TAB </b></h4>
				</div>
				</br></br>
			</div>

			<p style="color: gray; text-align: right;" id="mensaje_formulario">

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
				<button class="btn btn-primary btn-xs" id="bs_boton_guardar" disabled="disabled">Guardar</button>
				<a href="{{ url($ruta) }}" class="btn btn-danger btn-xs" id="bs_boton_volver"> Volver </a>
			</div>

			<div class="row">
				<div class="col-sm-12">
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

						{{ Form::hidden('id_app',Input::get('id')) }} 
						{{ Form::hidden('return', $ruta ) }}

						{{ Form::bsHidden( 'hay_pesos', $hay_pesos ) }}

						{{ Form::bsHidden( 'lineas_registros_calificaciones', 0 ) }}
					{{Form::close()}}

					<table class="table" id="tabla_lineas_registros_calificaciones" border="0">
						<thead>
							<tr>
								<th>id_calificacion</th>
								<th>id_calificacion_aux</th>
								<th>codigo_matricula</th>
								<th>id_estudiante</th>
								@for($c=1; $c < 16; $c++) 
									<th>C{{$c}}</th>
								@endfor
								<th>calificacion</th>
								<th>logros</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>

				</div>
			</div>

		</div>
	</div>

	@include('components.design.ventana_modal', [ 'titulo' => 'Ingreso/Actualización encabezados de calificaciones', 'texto_mensaje' => 'Registro actualizado correctamente.'])

@endsection

@section('scripts')


<script language="javascript">
	function ventana(id, id_textbox) {
		document.getElementById("caja_logro").value = id_textbox;

		window.open("{{ url('calificaciones_logros/consultar' )}}" + "/" + id, "Consulta de logros", "width=800,height=600,menubar=no")
	}

	function getChildVar(a_value) {
		var caja
		caja = document.getElementById("caja_logro").value;
		document.getElementById("logros_" + caja).value = a_value;
		$('#mensaje_guardadas').hide();
		$('#mensaje_sin_guardar').show();
		$('#bs_boton_guardar').prop('disabled', false);
	}

	$(document).ready(function() {

		checkCookie();

		var escala_min = parseFloat($('#escala_min').val(), 10);
		var escala_max = parseFloat($('#escala_max').val(), 10);

		// 9 = Tab
		// 16 = Shift
		// 8 = Backspace
		var teclas_especiales = [9, 16];

		var guardando = false;

		// Vaciar los inputs que tienen cero (0)
		$("input[type=text]").each(function() {
			var val = $(this).val();
			if (val == 0) {
				$(this).val("");
			}
		});

		// Sombrear la columna al seleccionar text input
		$("input[type=text]").on('focus', function() {
			var id = $(this).attr('id');
			var vec_id = id.split("_");
			$(".celda_" + vec_id[0]).css('background-color', '#a3e7fe');
		});

		// Quitar Sombra de la columna cuando el text input pierde el foco
		$("input[type=text]").on('blur', function() {
			var id = $(this).attr('id');
			var vec_id = id.split("_");
			$(".celda_" + vec_id[0]).css('background-color', 'transparent');
			$("#tabla_registros th.celda_" + vec_id[0]).css('background-color', '#e5e4e3');
		});

		// Cuando se presiona una caja de texto
		$("input[type=text]").keyup(function(e) {

			var caja_texto_id = $(this).attr("id"); // Cx_x, x=1,2,3...15
			var n = caja_texto_id.split("_");
			var numero_fila = parseInt( n[1] );

			// Si se presiona flecha hacia abajo
			if (e.keyCode == 40) 
			{
				var j = numero_fila + 1;
				var sig = ("#" + n[0] + "_" + j);
				$(sig).focus().select();
				return false;
			}

			// Si se presiona flecha hacia arriba
			if (e.keyCode == 38)
			{
				var j = numero_fila - 1;
				var sig = ("#" + n[0] + "_" + j);
				$(sig).focus().select();
				return false;
			}

			// inArray devuelve la posicion del codigo de la tecla presionada (e.keyCode) dentro del array: 0,1,... y un valor negativo si no se halla el codigo.

			// Si NO se presionan teclas especiales (El codigo no esta en el Array)
			if( $.inArray(e.keyCode, teclas_especiales) < 0)
			{
				validar_valor_ingresado( $(this) ); // Si el valor esta errado, borra el valor ingresado. Luego tambien hay que calcular la definitva

				if( verificar_hay_pesos() )
				{
					$( '#calificacion_texto' + numero_fila ).val( calcular_definitiva_una_fila_promedio_poderado( numero_fila ).toFixed(2) );
				}else{
					$( '#calificacion_texto' + numero_fila ).val( calcular_definitiva_una_fila_promedio_simple( numero_fila ).toFixed(2) );
				}
				
				// Cuando cambie el valor de una celda, se cambian los mensajes
				$('#mensaje_guardadas').hide();
				$('#mensaje_sin_guardar').show();
				$('#bs_boton_guardar').prop('disabled', false);
			}
		});

		$('#bs_boton_guardar').click(function() {
			guardar_calificaciones();
		});

		$('#bs_boton_volver').click(function() {
			document.location.href = "{{ url()->previous() }}";
		});

		window.guardar_calificaciones = function() {

			$('#lineas_registros_calificaciones').val(0);

			$('#bs_boton_guardar').prop('disabled', true);
			$('#bs_boton_volver').prop('disabled', true);

			guardando = true;

			llenar_tabla_lineas_registros();

			var table = $('#tabla_lineas_registros_calificaciones').tableToJSON();
			$('#lineas_registros_calificaciones').val( JSON.stringify( table ) );

			$('#div_cargando').show();
			$('#mensaje_sin_guardar').hide();
			
			var url = $("#formulario").attr('action');
			var data = $("#formulario").serialize();

			$.post( url, data, function( respuesta ) {

				$.each(respuesta, function(i, item) {
					$( "#fila_" + respuesta[i].numero_fila ).attr('data-id_calificacion', respuesta[i].id_calificacion );
					$( "#fila_" + respuesta[i].numero_fila ).attr('data-id_calificacion_aux', respuesta[i].id_calificacion_aux );
				});

				$('#tabla_lineas_registros_calificaciones').find('tbody:last').html('');

		    	$('#popup_alerta_danger').hide();
				$('#div_cargando').hide();
				$('#mensaje_guardadas').show();

				$('#bs_boton_volver').prop('disabled', false);

				guardando = false;

			}).fail(function( respuesta_error ) {
			    
		    	$('#popup_alerta_danger').show();
				$('#popup_alerta_danger').css('background-color','red');
				$('#popup_alerta_danger').text( 'Error. Algunos datos no se pudieron almacenar. Por favor actualice la información e intente nuevamente.' );

			    if( respuesta_error.status == 401 )
			    {
					$('#popup_alerta_danger').text( 'Error. Su sesión ha terminado de manera inesperada. La información no se pudo almacenar.' );
					document.location.href = "{ { url()->previous() }}";
			    }
			});
			/**/
		};
		
		function llenar_tabla_lineas_registros()
		{
			var numero_fila = 1;
			$('#tabla_registros > tbody > tr').each(function(i, obj_fila_tabla ) {
				var string_fila = generar_string_celdas( obj_fila_tabla, numero_fila );
				$('#tabla_lineas_registros_calificaciones').find('tbody:last').append('<tr>' + string_fila + '</tr>');
				numero_fila++;
			});
		}

		function generar_string_celdas( obj_fila_tabla, numero_fila )
		{
			var celdas = [];
			var num_celda = 0;
			
			celdas[ num_celda ] = '<td>'+ $(obj_fila_tabla).attr('data-id_calificacion') +'</td>';
			
			num_celda++;
			
			celdas[ num_celda ] = '<td>'+ $(obj_fila_tabla).attr('data-id_calificacion_aux') +'</td>';
			
			num_celda++;
			
			celdas[ num_celda ] = '<td>'+ $(obj_fila_tabla).attr('data-codigo_matricula') +'</td>';
			
			num_celda++;
			
			celdas[ num_celda ] = '<td>'+ $(obj_fila_tabla).attr('data-id_estudiante') +'</td>';
			
			num_celda++;

			$('.valores_' + numero_fila ).each(function() {
				celdas[ num_celda ] = '<td>' + this.value + '</td>';
				num_celda++;
			});
			
			celdas[ num_celda ] = '<td>'+ $( '#calificacion_texto' + numero_fila ).val() +'</td>';
			
			num_celda++;
			
			celdas[ num_celda ] = '<td>'+ $( '#logros_' + numero_fila ).val() +'</td>';

			var cantidad_celdas = celdas.length;
			var string_celdas = '';
			for (var i = 0; i < cantidad_celdas; i++)
			{
				string_celdas = string_celdas + celdas[i];
			}

			return string_celdas;
		}

		// Validar que sea númerico y que esté entre la escala de valoración
		function validar_valor_ingresado(obj)
		{
			
			if (obj.attr('class') == 'caja_logros')
			{
				return true;
			}

			var valido = true;
			if (obj.val() != '' && !$.isNumeric(obj.val())) {
				alert("Debe ingresar solo números. Para decimales use punto ( . ). No la coma ( , ).");
				obj.val('');
				valido = false;
			}

			if (obj.val() != '' && (obj.val() < escala_min || obj.val() > escala_max)) {
				alert("La calificación ingresada está por fuera de la escala de valoración. Ingrese un número entre " + escala_min + " y " + escala_max);
				obj.val('');
				valido = false;
			}

			return valido;
		}

		$(".encabezado_calificacion").on('click', function(e) {
			e.preventDefault();
			$("#alert_mensaje").hide();

			$("#contenido_modal").html('');


			$("#myModal").modal({
				keyboard: 'true'
			});

			$('#div_spin').fadeIn();
			$('.btn_edit_modal').hide();
			$(".btn_save_modal").show();
			$(".modal-title").html('Ingreso/Actualización encabezados de calificaciones');

			var url = "{{url('/')}}" + '/calificaciones_encabezados/create?columna_calificacion=' + $(this).val() + '&periodo_id=' + $('#id_periodo').val() + '&curso_id=' + $('#curso_id').val() + '&asignatura_id=' + $('#id_asignatura').val() + '&anio=' + $('#anio').val();

			$.get(url, function(respuesta) {
				$('#div_spin').hide();
				$("#contenido_modal").html(respuesta);
			});

		});

		$(document).on('keyup','#peso', function() {

			if ( !validar_input_numerico( $(this) ) )
			{
				$('.btn_save_modal').hide();
			}else{
				$('.btn_save_modal').show();
			}
		});

		$(document).on('click', '.btn_save_modal', function(e) {

			e.preventDefault();

			$("#alert_mensaje").hide();

			// Para un nuevo registro de encabezado que se le quiera almacenar un peso sin descripcion
			if ( $('#id_encabezado_calificacion').val() == 0 && $('#descripcion').val() == '' )
			{
				$('#descripcion').focus();
				alert('Debe ingresar una descripción para la actividad.');
				return false;
			}

			$('#div_spin').fadeIn();

			// Este formulario se genera al hacer click en el botón Cn (n=1,2,3,4,5,6,...,15) del encabezado
			var url = $("#formulario_modal").attr('action');
			var data = $("#formulario_modal").serialize();

			$.post(url, data, function(respuesta) {
				if (respuesta == 'pesos')
				{
					$('#div_spin').hide();
					alert('El peso total sobrepasa 100%, debe indicar un peso menor.');
				} else {

					$('#div_spin').hide();

					$("#alert_mensaje").fadeIn();

					// Si la Descripcion de la actividad esta vacia entonces, fue eliminada.
					if ( $('#descripcion').val() == '' )
					{
						cambiar_datos_boton( 0 );
					}else{
						cambiar_datos_boton( $('#peso').val() );
					}

					if ( verificar_hay_pesos() )
					{
						$("#nota_hay_pesos").show();
					}else{
						$("#nota_hay_pesos").hide();
					}
					
					recalcular_definitivas();

					guardar_calificaciones();
					
					$("#myModal").modal('hide');
					alert('El encabezado de la calificación ha sido ' + respuesta + ' correctamente.');
				}
			});

		});

		// Se debe ejecutar cuando la ventana modal este abierta. 
		function cambiar_datos_boton( peso )
		{
			var columna_calificacion = $("#columna_calificacion").val();
			var btn_columna = $( "#btn_" + columna_calificacion );
			btn_columna.attr( 'data-peso', peso );
			btn_columna.attr( 'title', 'Peso= ' + peso + '%');					

			if ( peso == 0 )
			{
				var color_btn = 'white';
			}else{
				var color_btn = '#50B794';
			}

			btn_columna.attr( 'style', 'background-color: ' + color_btn );
		}

		function verificar_hay_pesos()
		{
			var totalPesos = 0;
			$('.encabezado_calificacion').each(function() {
				totalPesos += parseFloat( $(this).attr('data-peso') );
			});

			if ( totalPesos > 0 )
			{
				return true;
			}

			return false;
		}

		function recalcular_definitivas()
		{
			if( verificar_hay_pesos() )
			{
				calcular_definitivas_promedio_poderado();
			}else{
				calcular_definitivas_promedio_simple();
			}
		}

		function calcular_definitivas_promedio_poderado()
		{
			var numero_fila = 1;
			// Por cada fila de la tabla
			$('#tabla_registros > tbody > tr').each(function(i, item) {
				
				$( '#calificacion_texto' + numero_fila ).val( calcular_definitiva_una_fila_promedio_poderado( numero_fila ).toFixed(2) );

				numero_fila++;
			});
		}

		function calcular_definitiva_una_fila_promedio_poderado( numero_fila )
		{

			// Por cada caja de texto de la fila
			var total_def = 0;
			$('.valores_' + numero_fila).each(function() {

				var peso_columna = get_valor_peso_columna( $(this) );

				total_def += this.value * (peso_columna / 100);
			});
			return total_def;
		}

		function calcular_definitivas_promedio_simple()
		{
			var total_def = 0;
			var numero_fila = 1;
			// Por cada fila de la tabla
			$('#tabla_registros > tbody > tr').each(function(i, item) {
				
				$( '#calificacion_texto' + numero_fila ).val( calcular_definitiva_una_fila_promedio_simple( numero_fila ).toFixed(2) );

				numero_fila++;
			});
		}

		function calcular_definitiva_una_fila_promedio_simple( numero_fila )
		{
			var total_def = 0;
				
			// Por cada caja de texto de la fila
			var sumatoria_calificaciones = 0;
			var n = 0;
			$( '.valores_' + numero_fila ).each(function() {
				if ( $.isNumeric( parseFloat( this.value ) ) && parseFloat( this.value ) != 0 )
				{
					sumatoria_calificaciones += parseFloat( this.value );
					n++;
				}
			});
			
			if ( n!= 0 )
			{
				total_def = sumatoria_calificaciones / n;
			}

			return total_def;
		}

		function get_valor_peso_columna( obj_input_text )
		{
			var btn_columna, peso_columna;
			btn_columna = get_obj_boton_columna( obj_input_text );
			peso_columna = parseFloat( btn_columna.attr('data-peso') );
			return peso_columna;
		}

		function get_obj_boton_columna( obj_input_text )
		{
			var id_input_text, vec_aux_id, celda_encabezado_columna;

			id_input_text = obj_input_text.attr('id');
			vec_aux_id = id_input_text.split("_");
			celda_encabezado_columna = $( ".celda_" + vec_aux_id[0] );			
			return celda_encabezado_columna.find('button');
		}

		function setCookie(cname, cvalue, exdays) {
			var d = new Date();
			d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
			var expires = "expires=" + d.toUTCString();
			document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
		}


		function getCookie(cname) {
			var name = cname + "=";
			var ca = document.cookie.split(';');
			for (var i = 0; i < ca.length; i++) {
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

			if (mostrar_ayuda == "true" || mostrar_ayuda == "") {

				$("#myModal").modal({
					keyboard: 'true'
				});

				$(".modal-title").html('Ayuda');
				$(".btn_edit_modal").hide();
				$(".btn_save_modal").hide();

				/* <li class="list-group-item">Las calificaciones se almacenan automáticamente cada diez (10) segundos.</li> */
				$("#contenido_modal").html('<div class="well well-lg"><ul class="list-group"><li class="list-group-item">Se pueden guardar las calificaciones en cualquier momento presionando el botón guardar y seguir ingresando información.</li>  <li class="list-group-item">Verifique que antes de salir de la página se muestre el mensaje <spam id="mensaje_guardadas" style="background-color: #b1e6b2;">Calificaciones guardadas</spam></li></ul> <div class="checkbox">  <label><input type="checkbox" name="mostrar_ayuda_calificaciones_form" id="mostrar_ayuda_calificaciones_form" value="true">No volver a mostrar este mensaje.</label> </div></div>');

				setCookie("mostrar_ayuda_calificaciones_form", true, 365);

				$(document).on('click', '#mostrar_ayuda_calificaciones_form', function() {
					if ($(this).val() == "true") {
						$(this).val("false");
						setCookie("mostrar_ayuda_calificaciones_form", "false", 365);
					} else {
						$(this).val("true");
						setCookie("mostrar_ayuda_calificaciones_form", "true", 365);
					}
				});
			}
		}

	});
</script>
@endsection