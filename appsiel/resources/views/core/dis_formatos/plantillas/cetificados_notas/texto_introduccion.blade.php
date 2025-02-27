
<br>
<div align="center">
	{{ config('calificaciones.texto_titulo_inicial') }} {{ $colegio->descripcion }}
</div>		
<div align="center">
	CERTIFICAN QUE:
</div>
<br>

<?php
	if( config('calificaciones.detallar_curso_grado') == 'grado' ){
		$label = 'grado <b>'.$curso->grado->descripcion.'</b>';
	}else {
		$label = 'curso <b>'.$curso->descripcion.'</b>';
	}

	$texto_resultado = 'cursó';

	if( $resultado_academico != '' ){
		$texto_resultado = 'cursó y <b>' . $resultado_academico . '</b>';
	}

	$texto_numero_identificacion = ', identificado con ' . $estudiante->tipo_documento . ' No. ' .  number_format( $estudiante->numero_identificacion, 0, ',', '.' );
	if ($mostrar_numero_identificacion_estudiante == 0) {
		$texto_numero_identificacion = '';
	}
?>

<div style="text-align: justify;">
<b>{{ $estudiante->nombre_completo }}</b>{!! $texto_numero_identificacion !!}, {!! $texto_resultado !!} en esta institución educativa los estudios correspondientes al {!! $label !!} de educación en Nivel <b>{{ $curso->nivel->descripcion }}</b>, según pensum oficial. Habiendo obtenido en el periodo lectivo {{ $periodo_lectivo->descripcion }} los resultados que a continuación se registran:
</div>		

<br>
				