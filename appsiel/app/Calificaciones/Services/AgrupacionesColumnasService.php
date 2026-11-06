<?php
namespace App\Calificaciones\Services;

use App\Calificaciones\AgrupacionCalificacion;
use App\Calificaciones\ColumnaAgrupacionCalificacion;
use App\Calificaciones\EncabezadoCalificacion;
use Illuminate\Support\Facades\DB;

class AgrupacionesColumnasService
{
    public function activo() { return config('calificaciones.usar_agrupaciones_columnas_calificaciones', 'No') === 'Si'; }

    public function grupos($anio, $periodoId)
    {
        $query = AgrupacionCalificacion::where('anio', $anio)->where('estado', 'Activo');
        $propios = (clone $query)->where('periodo_id', $periodoId)->exists();
        return $propios ? $query->where('periodo_id', $periodoId) : $query->whereNull('periodo_id');
    }

    public function columnas($anio, $periodoId)
    {
        $grupos = $this->grupos($anio, $periodoId)->with('columnas')->get();
        $columnas = [];
        foreach ($grupos as $grupo) {
            if (!$grupo->columnas->count() || (float)$grupo->peso <= 0) abort(422, 'Cada grupo activo debe tener columnas y un porcentaje positivo.');
            foreach ($grupo->columnas as $columna) {
                $nombre = $columna->columna_calificacion;
                if (isset($columnas[$nombre])) abort(422, 'La columna '.$nombre.' está asignada a más de una agrupación.');
                $columna->setRelation('agrupacion', $grupo);
                $columna->titulo = $grupo->titulo;
                $columnas[$nombre] = $columna;
            }
        }
        $total = (float)$grupos->sum('peso');
        if (!$columnas || abs($total - 100) > 0.0001) abort(422, 'Configure las agrupaciones de columnas para este año/periodo. Sus porcentajes deben sumar 100%.');
        return $columnas;
    }

    public function validarContexto($cursoId, $asignaturaId)
    {
        $colegio = \App\Core\Colegio::where('empresa_id', auth()->user()->empresa_id)->value('id');
        if (!$colegio || !\App\Matriculas\Curso::where('id', $cursoId)->where('id_colegio', $colegio)->exists()
            || !\App\Calificaciones\Asignatura::where('id', $asignaturaId)->where('id_colegio', $colegio)->exists()) {
            abort(404, 'Curso o asignatura no disponible para este colegio.');
        }
    }

    public function aplicarActividad(EncabezadoCalificacion $actividad)
    {
        if (!$this->activo()) return;
        $this->validarContexto($actividad->curso_id, $actividad->asignatura_id);
        $periodo = \App\Calificaciones\Periodo::find($actividad->periodo_id);
        if (!$periodo || !(int)$actividad->curso_id || !(int)$actividad->asignatura_id) abort(422, 'La actividad requiere periodo, curso y asignatura.');
        $anio = (int)substr($periodo->fecha_desde, 0, 4);
        if ($anio !== (int)$actividad->anio) abort(422, 'El año de la actividad no corresponde al periodo.');
        $columnas = $this->columnas($anio, $actividad->periodo_id);
        if (!isset($columnas[$actividad->columna_calificacion])) abort(422, 'La columna no pertenece a una agrupación activa.');
        $columna = $columnas[$actividad->columna_calificacion];
        $actividad->peso = $columna->peso;
        $actividad->titulo = $columna->titulo;
        $actividad->label = $columna->label;
        $actividad->agrupacion_columna_id = $columna->id;
    }

    public function bloquearPorCalificacion($auxiliar)
    {
        if (!$this->activo()) return;
        $ids = [];
        foreach ($this->columnas($auxiliar->anio, $auxiliar->id_periodo) as $columna) $ids[] = $columna->agrupacion_id;
        AgrupacionCalificacion::whereIn('id', array_unique($ids))->update(['bloqueado' => true]);
    }

    public function eliminarColumna(AgrupacionCalificacion $grupo, $id)
    {
        return DB::transaction(function () use ($grupo, $id) {
            AgrupacionCalificacion::where('empresa_id', $grupo->empresa_id)->lockForUpdate()->get();
            $grupo = AgrupacionCalificacion::findOrFail($grupo->id);
            if ($grupo->enUso()) abort(422, 'La agrupación tiene actividades o notas y no puede modificarse.');
            $grupo->columnas()->where('id', $id)->firstOrFail()->delete();
        });
    }

    public function guardarColumna(AgrupacionCalificacion $grupo, array $datos)
    {
        $validator = \Validator::make($datos, ['columna_calificacion' => ['required', 'regex:/^C([1-9]|1[0-5])$/'],
            'label' => 'required|max:60', 'porcentaje' => 'required|numeric|min:0.0001|max:100']);
        if ($validator->fails()) throw new \Illuminate\Foundation\Validation\ValidationException($validator);
        return DB::transaction(function () use ($grupo, $datos) {
            // Serializar cambios de agrupaciones de la empresa, incluso entre grupos distintos.
            AgrupacionCalificacion::where('empresa_id', $grupo->empresa_id)->lockForUpdate()->get();
            $grupo = AgrupacionCalificacion::findOrFail($grupo->id);
            if ($grupo->enUso()) abort(422, 'La agrupación ya tiene actividades; no puede modificar sus columnas.');
            $nombre = $datos['columna_calificacion'];
            $mismoAlcance = AgrupacionCalificacion::where('anio', $grupo->anio)->where('periodo_id', $grupo->periodo_id)->lists('id')->toArray();
            $duplicada = ColumnaAgrupacionCalificacion::whereIn('agrupacion_id', $mismoAlcance)
                ->where('columna_calificacion', $nombre)->where('agrupacion_id', '<>', $grupo->id)->exists();
            if ($duplicada) abort(422, 'La columna ya está asociada a otra agrupación del mismo año y periodo.');
            $columna = $grupo->columnas()->where('columna_calificacion', $nombre)->first() ?: new ColumnaAgrupacionCalificacion;
            $columna->fill($datos);
            $columna->agrupacion_id = $grupo->id;
            $columna->orden = (int)substr($nombre, 1);
            $columna->save();
            return $columna;
        });
    }
}
