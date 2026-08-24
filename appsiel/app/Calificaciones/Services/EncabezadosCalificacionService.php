<?php

namespace App\Calificaciones\Services;

use App\Calificaciones\EncabezadoCalificacion;
use App\Calificaciones\Periodo;
use Illuminate\Support\Facades\Schema;

class EncabezadosCalificacionService
{
    protected $columnasTabla = null;
    protected $periodosConEncabezadosFijos = [];

    /**
     * La clave de configuración conserva su nombre por compatibilidad con las
     * instalaciones existentes, pero los encabezados fijos se aíslan por periodo.
     */
    public function manejarEncabezadosFijosPorPeriodo()
    {
        return config('calificaciones.manejar_encabezados_por_anio_lectivo_en_calificaciones', 'No') === 'Si';
    }

    /**
     * Alias conservado para no romper integraciones que ya consuman el servicio.
     */
    public function usarEncabezadosPorAnio()
    {
        return $this->manejarEncabezadosFijosPorPeriodo();
    }

    public function usarEncabezadosFijosEnPeriodo($periodoId)
    {
        return $this->manejarEncabezadosFijosPorPeriodo()
            && $this->periodoTieneEncabezadosFijos($periodoId);
    }

    public function periodoTieneEncabezadosFijos($periodoId)
    {
        $periodoId = (int)$periodoId;

        if ($periodoId <= 0) {
            return false;
        }

        if (!array_key_exists($periodoId, $this->periodosConEncabezadosFijos)) {
            $this->periodosConEncabezadosFijos[$periodoId] = EncabezadoCalificacion::query()
                ->where('periodo_id', $periodoId)
                ->whereNull('curso_id')
                ->whereNull('asignatura_id')
                ->exists();
        }

        return $this->periodosConEncabezadosFijos[$periodoId];
    }

    public function getResumenParaCarga($anio, $periodoId, $cursoId, $asignaturaId)
    {
        $encabezados = $this->getEncabezados($anio, $periodoId, $cursoId, $asignaturaId);

        $columnas = [];
        $array_pesos = array_fill(0, 16, 0);
        $hay_pesos = false;
        $suma_porcentajes = 0;
        $usarEncabezadosFijos = $this->usarEncabezadosFijosEnPeriodo($periodoId);

        for ($k = 1; $k < 16; $k++) {
            $columna = 'C' . $k;
            $encabezado = $encabezados->where('columna_calificacion', $columna)->first();

            $columnas[$k] = (object)[
                'columna_calificacion' => $columna,
                'descripcion' => $encabezado ? $encabezado->descripcion : '',
                'peso' => $encabezado ? (float)$encabezado->peso : 0,
                'label' => $encabezado && !empty($encabezado->label) ? $encabezado->label : $columna,
                'titulo' => $encabezado ? $encabezado->titulo : '',
                'configurado' => $encabezado !== null
            ];

            $array_pesos[$k] = $columnas[$k]->peso;
            if ($columnas[$k]->peso > 0) {
                $hay_pesos = true;
                $suma_porcentajes += $columnas[$k]->peso;
            }
        }

        $columnasParaTitulos = $columnas;
        if ($usarEncabezadosFijos) {
            $columnasParaTitulos = array_filter($columnas, function ($columna) {
                return $columna->configurado;
            });
        }

        return [
            'encabezados' => $encabezados,
            'columnas' => $columnas,
            'array_pesos' => $array_pesos,
            'hay_pesos' => $hay_pesos,
            'suma_porcentajes' => $suma_porcentajes,
            'grupos_titulo' => $this->agruparTitulos($columnasParaTitulos),
            'usar_encabezados_fijos' => $usarEncabezadosFijos
        ];
    }

    public function getEncabezados($anio, $periodoId, $cursoId, $asignaturaId = null)
    {
        return $this->getQuery($anio, $periodoId, $cursoId, $asignaturaId)
            ->orderBy('columna_calificacion')
            ->get();
    }

    public function getEncabezado($anio, $periodoId, $cursoId, $asignaturaId, $columnaCalificacion)
    {
        return $this->getQuery($anio, $periodoId, $cursoId, $asignaturaId)
            ->where('columna_calificacion', $columnaCalificacion)
            ->first();
    }

    public function getQuery($anio, $periodoId, $cursoId, $asignaturaId = null)
    {
        $query = EncabezadoCalificacion::query();

        if ($this->usarEncabezadosFijosEnPeriodo($periodoId)) {
            return $query->where('periodo_id', $periodoId)
                ->whereNull('curso_id')
                ->whereNull('asignatura_id');
        }

        $query->where('periodo_id', $periodoId)
            ->where('curso_id', $cursoId);

        if (!is_null($asignaturaId)) {
            $query->where('asignatura_id', $asignaturaId);
        }

        return $query;
    }

    public function getAtributosDePersistencia($anio, $periodoId, $cursoId, $asignaturaId)
    {
        if ($this->usarEncabezadosFijosEnPeriodo($periodoId)) {
            return [
                'anio' => $anio,
                'periodo_id' => $periodoId,
                'curso_id' => null,
                'asignatura_id' => null
            ];
        }

        return [
            'anio' => $anio,
            'periodo_id' => $periodoId,
            'curso_id' => $cursoId,
            'asignatura_id' => $asignaturaId
        ];
    }

    public function getAtributosDesdePeriodo($periodoId, $cursoId, $asignaturaId)
    {
        $periodo = Periodo::find($periodoId);
        $anio = 0;

        if ($periodo) {
            $anio = (int)explode('-', $periodo->fecha_desde)[0];
        }

        return $this->getAtributosDePersistencia($anio, $periodoId, $cursoId, $asignaturaId);
    }

    public function agruparTitulos(array $columnas)
    {
        $grupos = [];
        $grupoActual = null;

        foreach ($columnas as $indice => $columna) {
            $titulo = trim((string)$columna->titulo);

            if ($grupoActual !== null && $grupoActual['titulo'] === $titulo) {
                $grupoActual['cantidad']++;
                continue;
            }

            if ($grupoActual !== null) {
                $grupos[] = (object)$grupoActual;
            }

            $grupoActual = [
                'titulo' => $titulo,
                'cantidad' => 1,
                'inicio' => $indice
            ];
        }

        if ($grupoActual !== null) {
            $grupos[] = (object)$grupoActual;
        }

        return $grupos;
    }

    public function tieneColumna($nombreColumna)
    {
        if ($this->columnasTabla === null) {
            $this->columnasTabla = Schema::getColumnListing((new EncabezadoCalificacion())->getTable());
        }

        return in_array($nombreColumna, $this->columnasTabla);
    }

    public function soportaLabelYTitulo()
    {
        return $this->tieneColumna('label') && $this->tieneColumna('titulo');
    }
}
