<?php
	$asignaturas_niveladas = [];

	$areas = $asignaturas->groupBy('area_id');

	$cantidad_asignaturas = 0;
	$sumatoria_calificaciones = 0;
	foreach($areas as $asignaturas)
	{
		$valor_calificacion = 0;
		$cantidad = 0;
		foreach($asignaturas as $asignatura)
		{
			if($asignatura->id == 146) // Italiano
			{
				$valor_calificacion += 5;
			}else{
				$calificacion = App\Calificaciones\Calificacion::get_la_calificacion( $periodo_id, $curso->id, $estudiante->id, $asignatura->id);
				$valor_calificacion += $calificacion->valor;
			}
					
			$cantidad_asignaturas++;
			$sumatoria_calificaciones += $calificacion->valor;
		}
	}  
		
	$promedio_calificaciones = 0;
    if($cantidad_asignaturas != 0)
    {
        $promedio_calificaciones = $sumatoria_calificaciones / $cantidad_asignaturas;
    }
?>

<div style="text-align: justify;">
	Observaciones:

	@if( $promedio_calificaciones >= 3.8 )
		<br>
		<b>El estudiante es promovido al grado siguiente.</b>
	@else
		<br>
		<b>El estudiante No alcanzó el promedio para ser promovido.</b>
	@endif

	<br>
	{{ $observacion_adicional }}
</div>

<br>

<div style="text-align: justify; font-size: 1em;">
	Para mayor constancia, se firma la presente en la ciudad de {{ $colegio->ciudad }} a los {{ $array_fecha[0] }} días
	del mes de {{ $array_fecha[1] }} de {{ $array_fecha[2] }}.
</div>