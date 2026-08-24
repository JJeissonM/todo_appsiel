<?php
	/*
		*_nota_original es la calificacion original del estudiante (table sga_calificaciones)
		*_nivelacion es la calificacion de la nivelacion (table sga_notas_nivelaciones)
	*/
	
	$decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');

	// Mostrar notas finales periodos anteriores
	foreach($periodos as $periodo_lista)
	{
		$calificacion_nota_original = $linea->calificaciones_todos_los_periodos_asignatura_estudiante->where('id_periodo', $periodo_lista->id)->first();

		$lbl_cali_periodo = '&nbsp;';
		if ( $calificacion_nota_original != null )
		{
			$cali_periodo = (float)$calificacion_nota_original->calificacion;
			$lbl_cali_periodo = number_format( $cali_periodo, $decimales, ',', '.' );

			$cali_nivelacion_periodo = $linea->calificaciones_niveladas_todos_los_periodos_asignatura_estudiante->where('id_periodo', $periodo_lista->id)->first();

			if( $cali_nivelacion_periodo != null )
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
	$calificaciones_auxiliares_linea = $linea->calificaciones_auxiliares;
	foreach($lbl_calificaciones_aux as $lbl_calificacion_aux)
	{
		$calificacion_nota_original = $calificaciones_auxiliares_linea;

		$campo = $lbl_calificacion_aux->columna_calificacion;
		$lbl_cali_periodo = '&nbsp;';
		if ( $calificacion_nota_original != null )
		{
			$cali_periodo = (float)$calificacion_nota_original->$campo;
			$lbl_cali_periodo = number_format( $cali_periodo, $decimales, ',', '.' );

			$n++;
		}
		
		echo '<td align="center"> ' . $lbl_cali_periodo . ' </td>';	
	}

	if ($calificaciones_auxiliares_linea != null)
	{
		$prom = app(App\Calificaciones\Services\CalificacionDefinitivaService::class)->calcular(
			(int)substr($periodo->fecha_desde, 0, 4),
			$periodo->id,
			$curso->id,
			$linea->asignatura_id,
			$calificaciones_auxiliares_linea
		);
	}

	if ($linea->calificacion != null)
	{
		$prom = (float)$linea->calificacion->calificacion;
	}

	$lbl_cali_prom = '&nbsp;';
	if( $n != 0 || $linea->calificacion != null )
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
