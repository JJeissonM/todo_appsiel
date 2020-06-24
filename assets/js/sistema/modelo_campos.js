$(document).ready(function(){

	var direccion = location.href;

	$('#opciones').val('null');
	$('#value_json').focus();

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
		asignar_opciones();
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

		$('#opciones').attr( 'required', 'required');
		$('#opciones').val( datos_registro.opciones );
		
		$('#key_json').focus();
	}
});