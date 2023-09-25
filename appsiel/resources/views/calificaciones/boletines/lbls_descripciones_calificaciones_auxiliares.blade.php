<?php
	/*
		*_nota_original es la calificacion original del estudiante (table sga_calificaciones)
		*_nivelacion es la calificacion de la nivelacion (table sga_notas_nivelaciones)
	*/
	
	$decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');

	// Mostrar notas finales periodos anteriores
	foreach($periodos as $periodo_lista)
	{
		$calificacion_nota_original = $periodo_lista->get_calificacion( $curso->id, $registro->estudiante->id, $linea->asignatura_id );

		$lbl_cali_periodo = '&nbsp;';
		if ( !is_null( $calificacion_nota_original ) )
		{
			$cali_periodo = (float)$calificacion_nota_original->calificacion;
			$lbl_cali_periodo = number_format( $cali_periodo, $decimales, ',', '.' );

			$cali_nivelacion_periodo = $periodo_lista->get_calificacion_nivelacion( $curso->id, $registro->estudiante->id, $linea->asignatura_id );

			if( !is_null( $cali_nivelacion_periodo ) )
			{
				$cali_periodo = (float)$cali_nivelacion_periodo->calificacion;
				$lbl_cali_periodo = number_format( $cali_periodo, $decimales, ',', '.' ) . '<sup>n</sup>';
			}
		}

		$lbl_calificacion = $lbl_cali_periodo;
		echo '<td align="center"> ' . $lbl_cali_periodo . ' </td>';
	}

	$prom = 0;
	$n = 0;
	foreach($lbl_calificaciones_aux as $lbl_calificacion_aux)
	{
		$calificacion_nota_original = $linea->calificaciones_auxiliares;

		$campo = $lbl_calificacion_aux->label;
		$lbl_cali_periodo = '&nbsp;';
		if ( $calificacion_nota_original != null )
		{
			$cali_periodo = (float)$calificacion_nota_original->$campo;
			$lbl_cali_periodo = number_format( $cali_periodo, $decimales, ',', '.' );

			$prom += $cali_periodo * (float)$lbl_calificacion_aux->peso / 100;
			$n++;
		}
		
		echo '<td align="center"> ' . $lbl_cali_periodo . ' </td>';	
	}

	$lbl_cali_prom = '&nbsp;';
	if( $n != 0 )
	{
		$lbl_cali_prom = number_format( $prom, $decimales, ',', '.' );
	}
	
	// Si el periodo es marcado como periodo_de_promedios, se reemplaza el calculo anterior de lbl_cali_prom
    // El promedio lo trae del que ya esta almacenado en el Periodo FINAL.
	if( $periodo->periodo_de_promedios )
	{
		$cali_promedio_periodo_final = $periodo->get_calificacion( $curso->id, $registro->estudiante->id, $linea->asignatura_id );
		if( $cali_promedio_periodo_final != null )
		{
			$observacion = '';
			if ($cali_promedio_periodo_final->calificacion == null) {
				$observacion = '* Promedio Final no calculado.';
			}
			$lbl_cali_prom = number_format( $cali_promedio_periodo_final->calificacion, $decimales, ',', '.' ) . $observacion;
		}
	}

	if( $linea->calificacion_nivelacion != null )
	{
		$lbl_cali_prom = number_format( $linea->calificacion_nivelacion, $decimales, ',', '.' ) . '<sup>n</sup>';
	}

	echo '<td align="center"> ' . $lbl_cali_prom . ' </td>';

?>


