// NO FUNCIONA PROBLEMAS DE ENTORNO Y AMBITO
$(document).ready(function(){

var valor_actual, elemento_modificar, elemento_padre;
					
// Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
$(document).on('dblclick','.elemento_modificar',function(){
	
	elemento_modificar = $(this);

	elemento_padre = elemento_modificar.parent();

	valor_actual = $(this).html();

	elemento_modificar.hide();

	elemento_modificar.after( '<input type="text" name="valor_nuevo" id="valor_nuevo" style="display:inline;">' );

	document.getElementById('valor_nuevo').value = valor_actual;
	document.getElementById('valor_nuevo').select();

	console.log('es la misma');
});

// Si la caja de texto pierde el foco
$(document).on('blur','#valor_nuevo',function(){
	guardar_valor_nuevo();
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
    	guardar_valor_nuevo();
	}
});

function guardar_valor_nuevo()
{
	var valor_nuevo = document.getElementById('valor_nuevo').value;

	// Si no cambió el valor_nuevo, no pasa nada
	if ( valor_nuevo == valor_actual) { return false; }

	elemento_modificar.html( valor_nuevo );
	elemento_modificar.show();

	elemento_padre.find('#valor_nuevo').remove();

	console.log('llar ar doble_click_guardar_valor_nuevo');
	doble_click_guardar_valor_nuevo(); // !!!Esta funcion llama a otras funciones que no están en el AMBITO!!!!!
}

});