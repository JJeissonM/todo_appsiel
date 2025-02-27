
<?php 
    $promedio_calificaciones = 0;
    if($cantidad_asignaturas != 0)
    {
        $promedio_calificaciones = $sumatoria_calificaciones / $cantidad_asignaturas;
    }
    $escala_promedio_calificaciones = '';
    $escala = \App\Calificaciones\EscalaValoracion::get_escala_segun_calificacion($promedio_calificaciones, $periodo_lectivo->id);
    if ($escala != null) {
        $escala_promedio_calificaciones = $escala->escala_nacional;
    }
?>

El (la) estudiante obtuvo un promedio final de {{ number_format($promedio_calificaciones, config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.' ) }} ({{ $escala_promedio_calificaciones }}).