<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        'Turno', 'Fecha', 'Evento', 'Estado anterior', 'Estado nuevo',
        'Usuario', 'Responsable del turno', 'Motivo'
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
            'core_turno_eventos.created_at AS campo2',
            DB::raw(static::eventTypeSql() . ' AS campo3'),
            'core_turno_eventos.estado_anterior AS campo4',
            'core_turno_eventos.estado_nuevo AS campo5',
            DB::raw("COALESCE(NULLIF(users.name, ''), '—') AS campo6"),
            DB::raw("COALESCE(NULLIF(" . static::responsibleSql() . ", ''), '—') AS campo7"),
            DB::raw("COALESCE(NULLIF(core_turno_eventos.motivo, ''), '—') AS campo8"),
            'core_turno_eventos.id AS campo9'
        );
        static::applySearch($query, $search);
        return $query->orderBy('core_turno_eventos.id', 'DESC')->paginate($nroRegistros);
    }

    public static function sqlString($search)
    {
        $query = static::baseQuery()->select(
            'core_turnos_operativos.codigo AS TURNO',
            'core_turno_eventos.created_at AS FECHA_EVENTO',
            DB::raw(static::eventTypeSql() . ' AS EVENTO'),
            'core_turno_eventos.estado_anterior AS ESTADO_ANTERIOR',
            'core_turno_eventos.estado_nuevo AS ESTADO_NUEVO',
            DB::raw("COALESCE(NULLIF(users.name, ''), '—') AS USUARIO_EJECUTOR"),
            DB::raw("COALESCE(NULLIF(" . static::responsibleSql() . ", ''), '—') AS RESPONSABLE_TURNO"),
            DB::raw("COALESCE(NULLIF(core_turno_eventos.motivo, ''), '—') AS MOTIVO")
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
                ->orWhere(DB::raw(static::eventTypeSql()), 'LIKE', $like)
                ->orWhere('core_turno_eventos.estado_anterior', 'LIKE', $like)
                ->orWhere('core_turno_eventos.estado_nuevo', 'LIKE', $like)
                ->orWhere('users.name', 'LIKE', $like)
                ->orWhere(DB::raw(static::responsibleSql()), 'LIKE', $like)
                ->orWhere('core_turno_eventos.motivo', 'LIKE', $like);
        });
    }

    protected static function responsibleSql()
    {
        return "(SELECT turno_apertura.responsable
            FROM vtas_pos_apertura_encabezados AS turno_apertura
            WHERE turno_apertura.turno_operativo_id = core_turnos_operativos.id
            ORDER BY turno_apertura.id DESC
            LIMIT 1)";
    }

    protected static function eventTypeSql()
    {
        return "CASE core_turno_eventos.tipo
            WHEN 'APERTURA' THEN 'Apertura'
            WHEN 'CIERRE' THEN 'Cierre'
            WHEN 'REAPERTURA' THEN 'Reapertura'
            WHEN 'AJUSTE_POSTERIOR' THEN 'Ajuste posterior'
            WHEN 'INICIO_AUDITORIA' THEN 'Inicio de auditoría'
            WHEN 'FIN_AUDITORIA' THEN 'Finalización de auditoría'
            ELSE core_turno_eventos.tipo
        END";
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
