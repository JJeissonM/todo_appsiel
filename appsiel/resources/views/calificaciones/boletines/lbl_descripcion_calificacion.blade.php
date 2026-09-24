<?php
	/*
		*_nota_original es la calificacion original del estudiante (table sga_calificaciones)
		*_nivelacion es la calificacion de la nivelacion (table sga_notas_nivelaciones)
	*/
	
	$decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');
	$calificacion_nota_original = $linea->calificacion->calificacion;
	$escala_valoracion_nota_original = $linea->escala_valoracion->nombre_escala;
	
	$calificacion_nivelacion = '';
	$escala_valoracion_nivelacion = '';

    $tiene_nivelacion = false;
    if (in_array($mostrar_nota_nivelacion, [
        'solo_nota_nivelacion_con_etiqueta', 'solo_nota_nivelacion_sin_etiqueta', 'ambas_notas'
    ], true)) {
        if (property_exists($linea, 'calificacion_nivelacion')) {
            // preparar_datos_boletin ya cargó las nivelaciones y su escala.
            $tiene_nivelacion = $linea->calificacion_nivelacion !== null;
            if ($tiene_nivelacion) {
                $calificacion_nivelacion = $linea->calificacion_nivelacion;
                $escala_valoracion_nivelacion = $linea->escala_valoracion->nombre_escala;
            }
        } else {
            // Compatibilidad con vistas que construyen sus propias líneas.
            $nota_nivelacion = $linea->calificacion->nota_nivelacion();
            $tiene_nivelacion = $nota_nivelacion !== null;
            if ($tiene_nivelacion) {
                $calificacion_nivelacion = $nota_nivelacion->calificacion;
                $escala_valoracion_nivelacion = $nota_nivelacion->escala_valoracion()->nombre_escala;
            }
        }
    }

	$lbl_peso_asignatura = '';

	switch( config('calificaciones.etiqueta_calificacion_boletines') )
	{
	    case 'numero_y_letras':
	        $lbl_nota_original = number_format( (float)$calificacion_nota_original, $decimales, ',', '.' ) . ' (' . $escala_valoracion_nota_original . ')' . $lbl_peso_asignatura;
	        $lbl_nivelacion = number_format( (float)$calificacion_nivelacion, $decimales, ',', '.' ) . ' (' . $escala_valoracion_nivelacion . ')';
	        break;

	    case 'solo_numeros':
	        $lbl_nota_original = number_format( (float)$calificacion_nota_original, $decimales, ',', '.' ) . $lbl_peso_asignatura;
	        $lbl_nivelacion = number_format( (float)$calificacion_nivelacion, $decimales, ',', '.' );
	        break;

	    case 'solo_letras':
	        $lbl_nota_original = $escala_valoracion_nota_original . $lbl_peso_asignatura;
	        $lbl_nivelacion = $escala_valoracion_nivelacion;
	        break;

	    default:
	        $lbl_nota_original = number_format( (float)$calificacion_nota_original, $decimales, ',', '.' ) . ' (' . $escala_valoracion_nota_original . ')' . $lbl_peso_asignatura;
	        $lbl_nivelacion = number_format( (float)$calificacion_nivelacion, $decimales, ',', '.' ) . ' (' . $escala_valoracion_nivelacion . ')';
	        break;
	}

	// 169 = FASE MILITAR
	if ( $linea->calificacion->id_asignatura == 169) {
		$lbl_nota_original = 'APROBÓ';
		if ( (float)$calificacion_nota_original < 5) {
			$lbl_nota_original = 'REPROBÓ';
		}
	}

	switch ( $mostrar_nota_nivelacion )
	{
		case 'solo_nota_nivelacion_con_etiqueta':
			if ( $tiene_nivelacion )
			{
				echo $lbl_nivelacion . '<sup>n</sup>';
			}else{
				echo $lbl_nota_original;
			}
			break;
		
		case 'solo_nota_nivelacion_sin_etiqueta':
			if ( $tiene_nivelacion )
			{
				echo $lbl_nivelacion;
			}else{
				echo $lbl_nota_original;
			}
			break;
		
		case 'ambas_notas':
			if ( $tiene_nivelacion )
			{
				echo '<span style="color: gray">' . $lbl_nota_original . '</span> &nbsp;' . $lbl_nivelacion . '<sup>n</sup>';
			}else{
				echo $lbl_nota_original;
			}
			
			break;
		
		default:
			echo $lbl_nota_original;
			break;
	}

?>


