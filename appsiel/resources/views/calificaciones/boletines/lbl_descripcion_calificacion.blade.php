<?php 
	switch( config('calificaciones.etiqueta_calificacion_boletines') )
	{
	    case 'numero_y_letras':
	        echo $calificacion->valor . '(' . $calificacion->escala_descripcion . ')';
	        break;

	    case 'solo_numeros':
	        echo $calificacion->valor;
	        break;

	    case 'solo_letras':
	        echo $calificacion->escala_descripcion;
	        break;

	    default:
	        echo $calificacion->valor . '(' . $calificacion->escala_descripcion . ')';
	        break;
	}
?>