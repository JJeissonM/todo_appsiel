<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TurnoEvento extends Model
{
    protected $table = 'core_turno_eventos';

    protected $fillable = array(
        'turno_operativo_id', 'tipo', 'estado_anterior', 'estado_nuevo',
        'entidad_tipo', 'entidad_id', 'usuario_id', 'motivo', 'datos'
    );

    public $urls_acciones = '{"create":"no","edit":"no","eliminar":"no","show":"web/id_fila"}';

    public $encabezado_tabla = array(
        '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>',
        'Turno', 'Evento', 'Estado anterior', 'Estado nuevo', 'Usuario', 'Motivo', 'Fecha'
    );

    public function turno()
    {
        return $this->belongsTo(TurnoOperativo::class, 'turno_operativo_id');
    }

    public function authorizeCrudAction($action, $user)
    {
        return !is_null($user) && $user->can('turnos.eventos.consultar');
    }

    public function getDescripcionAttribute()
    {
        return $this->tipo . ' - turno ' . $this->turno_operativo_id;
    }

    public static function consultar_registros($nroRegistros, $search)
    {
        $query = static::baseQuery()->select(
            'core_turnos_operativos.codigo AS campo1',
            'core_turno_eventos.tipo AS campo2',
            'core_turno_eventos.estado_anterior AS campo3',
            'core_turno_eventos.estado_nuevo AS campo4',
            'users.name AS campo5',
            'core_turno_eventos.motivo AS campo6',
            'core_turno_eventos.created_at AS campo7',
            'core_turno_eventos.id AS campo8'
        );
        static::applySearch($query, $search);
        return $query->orderBy('core_turno_eventos.id', 'DESC')->paginate($nroRegistros);
    }

    public static function sqlString($search)
    {
        $query = static::baseQuery()->select(
            'core_turnos_operativos.codigo AS TURNO',
            'core_turno_eventos.tipo AS EVENTO',
            'core_turno_eventos.estado_anterior AS ESTADO_ANTERIOR',
            'core_turno_eventos.estado_nuevo AS ESTADO_NUEVO',
            'users.name AS USUARIO',
            'core_turno_eventos.motivo AS MOTIVO',
            'core_turno_eventos.created_at AS FECHA'
        );
        static::applySearch($query, $search);
        return static::interpolate($query->orderBy('core_turno_eventos.id', 'DESC'));
    }

    public static function tituloExport()
    {
        return 'EVENTOS DE TURNOS OPERATIVOS';
    }

    protected static function baseQuery()
    {
        $query = static::query()
            ->join('core_turnos_operativos', 'core_turnos_operativos.id', '=', 'core_turno_eventos.turno_operativo_id')
            ->leftJoin('users', 'users.id', '=', 'core_turno_eventos.usuario_id');
        if (Auth::check()) {
            $query->where('core_turnos_operativos.core_empresa_id', (int)Auth::user()->empresa_id);
        }
        return $query;
    }

    protected static function applySearch($query, $search)
    {
        $search = trim((string)$search);
        if ($search === '') {
            return;
        }
        $query->where(function ($inner) use ($search) {
            $like = '%' . $search . '%';
            $inner->where('core_turnos_operativos.codigo', 'LIKE', $like)
                ->orWhere('core_turno_eventos.tipo', 'LIKE', $like)
                ->orWhere('core_turno_eventos.estado_anterior', 'LIKE', $like)
                ->orWhere('core_turno_eventos.estado_nuevo', 'LIKE', $like)
                ->orWhere('users.name', 'LIKE', $like)
                ->orWhere('core_turno_eventos.motivo', 'LIKE', $like);
        });
    }

    protected static function interpolate($query)
    {
        $sql = $query->toSql();
        foreach ($query->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . str_replace("'", "''", $binding) . "'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }
}
