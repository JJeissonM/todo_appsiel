
/*
	NOTA: Se recarga la pagina al guardar el nuevo valor.
*/

$(document).ready(function(){

	var valor_actual, elemento_modificar, elemento_padre;
			
	// Al hacer Doble Click en el elemento a modificar
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

		$.ajax({
        	url: caja_texto.prev().attr('data-url_modificar') + "/" + valor_nuevo,
        	method: "GET",
        	success: function( data ){
        		$('#div_cargando').hide();
		    	
		    	elemento_modificar.html( valor_nuevo );
				elemento_modificar.show();

				elemento_padre.find('#valor_nuevo').remove();

				location.reload();
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