<?php

namespace App\Calificaciones\Services;

/** Presentación de notas; no modifica las notas ni sus promedios. */
class EtiquetaCalificacionBoletin
{
    private $escalas;

    public function __construct($escalas)
    {
        $this->escalas = $escalas;
    }

    public function formatear($valor)
    {
        if ($valor === null) {
            return '&nbsp;';
        }

        $numero = number_format((float)$valor,
            (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
        $modo = config('calificaciones.etiqueta_calificacion_boletines');
        if ($modo === 'solo_numeros') {
            return $numero;
        }

        $nombre = '';
        foreach ($this->escalas as $escala) {
            if ($valor >= $escala->calificacion_minima && $valor <= $escala->calificacion_maxima) {
                $nombre = e($escala->nombre_escala);
                break;
            }
        }

        if ($modo === 'solo_letras') {
            return $nombre;
        }

        return $numero . ($nombre !== '' ? ' (' . $nombre . ')' : '');
    }
}
