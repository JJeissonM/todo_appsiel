$(document).ready(function(){

	var direccion = location.href;

	// Se oculta el DIV de opciones y el select de respuesta correcta
	$('#div_agregar_opciones').hide();
	$('#respuesta_correcta').parent('div').parent('div').hide();
	$('#opciones').removeAttr( 'required' );


	//$('#respuesta_correcta').attr('type','text'); // Esto es temporal para pruebas

	var opciones_1 = '<label class="control-label" for="lista_respuestas">Respuesta correcta:</label><select class="form-control" name="lista_respuestas" id="lista_respuestas" required="required"><option value="" selected="selected"></option></select>';
	var opciones_2 = '<label class="control-label" for="lista_respuestas">Respuesta correcta:</label><select class="form-control" name="lista_respuestas" id="lista_respuestas" required="required"><option value="Falso" selected="selected">Falso</option><option value="Verdadero">Verdadero</option></select>';

	// Al cambiar el tipo de pregunta
	$('#tipo').change(function()
	{
		// Se "resetan" los campos
		$('#div_agregar_opciones').hide();
		$('#respuesta_correcta').parent('div').parent('div').hide();
		$('#lista_respuestas').remove();
		$("[for='lista_respuestas']").remove();
		$('#respuesta_correcta').val('');


		$('#opciones').val('');
		$('#opciones').removeAttr( 'required' );
		$('#ingreso_registros').find('tbody').html('');

		// Según el tipo de pregunta seleccionada
		switch( $('#tipo').val() )
		{
			case 'Abierta':
				// Ya los campo están reseteados
			break;
			case 'Falso-Verdadero':

				$('#respuesta_correcta').parent('div').parent('div').show(500);
				$('#respuesta_correcta').parent('div').parent('div').append( opciones_2 );
				$('#respuesta_correcta').val('Falso');

			break;
			default:
				$('#div_agregar_opciones').show();
				$('#respuesta_correcta').parent('div').parent('div').show(500);
				$('#respuesta_correcta').parent('div').parent('div').append( opciones_1 );
				$('#key_json').val('a');
				$('#value_json').focus();
				$('#opciones').attr( 'required', 'required');
			break;
		}
	});

	// Para el campo tipo json_simple
	$('#btn_nueva_linea').click(function(e)
	{
		e.preventDefault();
		if ( $('#key_json').val() != '' && $('#value_json').val() != '' ) 
		{
			// Se agrega una nueva línea a la tabla de opciones
			var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
			$('#ingreso_registros').find('tbody').append('<tr> <td>' + $('#key_json').val() +'</td> <td>'+ $('#value_json').val() +'</td><td>'+btn_borrar+'</td></tr>');
			
			// Se agrega el valor de la opción ingresada a la lista de respuestas
			$('#lista_respuestas').append($('<option>', { value: $('#key_json').val(), text: $('#key_json').val()}));

			asignar_opciones();

			// Se Vacían las cajas de texto
			$('#key_json').val('');
			$('#value_json').val('');

			$('#key_json').focus();

		}else{
			alert('Debe ingresar una opción y su valor.');
			$('#key_json').focus();
		}
	});

	$(document).on('click', '.btn_eliminar', function() {
		var fila = $(this).closest("tr");
		var key_json_tabla = fila.find('td:first').html();
		
		$("#lista_respuestas option[value='"+key_json_tabla+"']").remove();
		fila.remove();

		asignar_opciones();
	});

	// Al cambiar la repuesta correcta
	$(document).on('change', '#lista_respuestas', function() {
		$('#respuesta_correcta').val( $('#lista_respuestas option:selected').text() );
	});


	/*
	  * Se va crear una cadena en formato JSON con cada una de las filas de la tabla de opciones
	*/
	function asignar_opciones() 
	{
		
		var text = '{ ';

		var primero = true;
		$('#ingreso_registros').find('tbody>tr').each( function(){
			var key_json = $(this).find('td:first').html();
			var value_json = $(this).find('td:first').next('td').html();

			if ( primero ) {
				text = text + '"'+key_json+'":"'+value_json+'"';
				primero = false;
			}else{
				text = text + ', "'+key_json+'":"'+value_json+'"';
			}
			
		});

		var text = text + '}';

		$('#opciones').val( text );
	}

	/*
	  * Cuando se está editando un registro
	*/
	if( direccion.search("edit") >= 0 ) 
	{
		var datos_registro = JSON.parse( $("#datos_registro").val() );

		// Según el tipo de pregunta seleccionada
		switch( $('#tipo').val() )
		{
			case 'Abierta':
				// Ya los campo están reseteados
			break;
			case 'Falso-Verdadero':

				$('#respuesta_correcta').parent('div').parent('div').show(500);
				// Se crea el select lista_respuestas con Falso y Verdadero
				$('#respuesta_correcta').parent('div').parent('div').append( opciones_2 );

				// Posicionar select
				$('#lista_respuestas').val( datos_registro.respuesta_correcta );

				$('#opciones').removeAttr( 'required' );

			break;
			default:
				$('#div_agregar_opciones').show();
				$('#respuesta_correcta').parent('div').parent('div').show(500);

				$('#opciones').attr( 'required', 'required');
				$('#opciones').val( datos_registro.opciones );
				
				// Se crea el select lista_respuestas Vacío
				$('#respuesta_correcta').parent('div').parent('div').append( opciones_1 );
				var opciones = JSON.parse( $('#opciones').val() );
				var linea;

				$.each(opciones, function(k, v) {
					console.log( k );
					$('#lista_respuestas').append( $('<option>', { value: k, text: k } ) );
				});

				$('#lista_respuestas').val( datos_registro.respuesta_correcta );
				$('#key_json').focus();
			break;
		}
	}
	
	function getParameterByName(name)
	{
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

});