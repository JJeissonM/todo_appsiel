<?php
	/*
		*_nota_original es la calificacion original del estudiante (table sga_calificaciones)
		*_nivelacion es la calificacion de la nivelacion (table sga_notas_nivelaciones)
	*/
	
	$decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');

	$prom = 0;
	$n = 0;
	foreach($periodos as $periodo)
	{
		$calificacion_nota_original = $periodo->get_calificacion( $curso->id, $registro->estudiante->id, $linea->asignacion_asignatura->asignatura->id );

		if ( is_null( $calificacion_nota_original ) )
		{
			echo '<td align="center"> &nbsp; </td>';
		}else{
			echo '<td align="center">' . number_format( (float)$calificacion_nota_original->calificacion, $decimales, ',', '.' ) . '</td>';
			$prom += (float)$calificacion_nota_original->calificacion;
			$n++;
		}			
	}

	if( $n == 0 )
	{
		echo '<td align="center"> &nbsp; </td>';
	}else{
		echo '<td align="center">' . number_format( $prom / $n, $decimales, ',', '.' ) . '</td>';
	}
?>


