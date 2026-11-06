<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AgrupacionCalificacion extends Model
{
    protected $table = 'sga_agrupaciones_calificacion';
    protected $fillable = ['titulo', 'peso', 'anio', 'periodo_id', 'estado'];
    public $encabezado_tabla = ['<i class="fa fa-check-square-o"></i>', 'Título', 'Porcentaje del grupo', 'Año', 'Periodo', 'Estado'];
    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('empresa', function ($query) {
            $query->where('sga_agrupaciones_calificacion.empresa_id', Auth::check() ? Auth::user()->empresa_id : 0);
        });
        static::saving(function ($grupo) {
            if (!$grupo->authorizeCrudAction('edit', Auth::user())) abort(403);
            if (Auth::check()) $grupo->empresa_id = Auth::user()->empresa_id;
            $grupo->periodo_id = $grupo->periodo_id ?: null;
            $validator = Validator::make($grupo->getAttributes(), [
                'titulo' => 'required|max:150', 'peso' => 'required|numeric|min:0.01|max:100', 'anio' => 'required|integer|min:2000|max:2200',
                'periodo_id' => 'integer|exists:sga_periodos,id', 'estado' => 'required|in:Borrador,Activo'
            ]);
            if ($validator->fails()) throw new \Illuminate\Foundation\Validation\ValidationException($validator);
            if ($grupo->periodo_id) {
                $periodo = Periodo::findOrFail($grupo->periodo_id);
                if ((int)substr($periodo->fecha_desde, 0, 4) !== (int)$grupo->anio) abort(422, 'El periodo debe pertenecer al año de la agrupación.');
            }
            if ($grupo->exists && $grupo->getDirty() && $grupo->enUso()) abort(422, 'Esta agrupación ya tiene actividades o notas. Cree una nueva agrupación para otro periodo o año.');
            $total = static::where('anio', $grupo->anio)->where('periodo_id', $grupo->periodo_id)
                ->where('id', '<>', $grupo->id ?: 0)->sum('peso');
            if ((float)$total + (float)$grupo->peso > 100.0001) abort(422, 'Los porcentajes de los grupos del mismo alcance no pueden superar 100%.');
            if ($grupo->exists && ($grupo->isDirty('anio') || $grupo->isDirty('periodo_id'))) {
                $otros = static::where('anio', $grupo->anio)->where('periodo_id', $grupo->periodo_id)
                    ->where('id', '<>', $grupo->id)->lists('id')->toArray();
                if (ColumnaAgrupacionCalificacion::whereIn('agrupacion_id', $otros)
                    ->whereIn('columna_calificacion', $grupo->columnas()->lists('columna_calificacion')->toArray())->exists()) {
                    abort(422, 'El nuevo alcance ya tiene asignadas estas columnas.');
                }

            }
        });
        static::deleting(function ($grupo) {
            if (!$grupo->authorizeCrudAction('delete', Auth::user())) abort(403);
            if ($grupo->enUso()) abort(422, 'No se puede eliminar una agrupación con actividades registradas.');
            $grupo->columnas()->delete();
        });
    }

    public function columnas() { return $this->hasMany(ColumnaAgrupacionCalificacion::class, 'agrupacion_id'); }
    public function enUso()
    {
        return $this->bloqueado || EncabezadoCalificacion::whereIn('agrupacion_columna_id', $this->columnas()->lists('id')->toArray())->exists();
    }
    public function authorizeCrudAction($action, $user)
    {
        return $user && ($user->hasRole('SuperAdmin') || $user->hasRole('Administrador') || $user->hasPermissionTo('cali_agrupaciones_columnas'));
    }
    public static function consultar_registros($nro, $search)
    {
        return static::select('titulo AS campo1', 'peso AS campo2', 'anio AS campo3', 'periodo_id AS campo4', 'estado AS campo5', 'id AS campo6')
            ->where(function ($q) use ($search) { $q->where('titulo', 'like', '%'.$search.'%')->orWhere('anio', 'like', '%'.$search.'%'); })
            ->orderBy('anio', 'desc')->orderBy('titulo')->paginate($nro);
    }
    public static function sqlString($search)
    {
        $query = static::select('titulo AS Titulo', 'peso AS Porcentaje', 'anio AS Anio', 'periodo_id AS Periodo', 'estado AS Estado')
            ->where('titulo', 'like', '%'.$search.'%');
        $sql = $query->toSql();
        foreach ($query->getBindings() as $binding) {
            $sql = preg_replace_callback('/\?/', function () use ($binding) { return DB::getPdo()->quote((string)$binding); }, $sql, 1);
        }
        return $sql;
    }
    public static function tituloExport() { return 'AGRUPACIONES DE COLUMNAS'; }
    public static function get_tabla($registro_modelo_padre, $registros_asignados)
    {
        return view('calificaciones.agrupaciones_columnas.columnas', ['grupo' => $registro_modelo_padre, 'columnas' => $registro_modelo_padre->columnas()->orderBy('orden')->get()])->render();
    }
    public static function get_opciones_modelo_relacionado($id) { return ['' => 'ColumnasCalificacion']; }
}
