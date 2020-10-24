<?php 
	switch( config('calificaciones.etiqueta_calificacion_boletines') )
	{
	    case 'numero_y_letras':
	        echo $linea->calificacion->calificacion . ' (' . $linea->escala_valoracion->nombre_escala . ')';
	        break;

	    case 'solo_numeros':
	        echo $linea->calificacion->calificacion;
	        break;

	    case 'solo_letras':
	        echo $linea->calificacion->calificacion->escala_descripcion;
	        break;

	    default:
	        echo $linea->calificacion->calificacion . ' (' . $linea->escala_valoracion->nombre_escala . ')';
	        break;
	}
?>