<?php

	$escala = App\Calificaciones\EscalaValoracion::where('periodo_lectivo_id',$periodo->periodo_lectivo_id)->orderBy('calificacion_minima','ASC')->get();

	$tbody = '<div style="width: 100%; border: 1px solid #ddd; font-size:12px;padding:5px;"><b>Escalas de valoración: </b>';

    $primer_dato = true;
	foreach($escala as $linea)
	{
		if ( $primer_dato )
        {
            $tbody .= $linea->nombre_escala. ': ' . $linea->calificacion_minima . ' - ' . $linea->calificacion_maxima;
            $primer_dato = false;
        }else{
            $tbody .= ', ' . $linea->nombre_escala. ': ' . $linea->calificacion_minima . ' - ' . $linea->calificacion_maxima;
        }
		
	}

	$tbody.='</div>';
	echo $tbody;
?>