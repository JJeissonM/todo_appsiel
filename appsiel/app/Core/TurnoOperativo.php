<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TurnoOperativo extends Model
{
    const ESTADO_ABIERTO = 'ABIERTO';
    const ESTADO_CERRADO = 'CERRADO';
    const ESTADO_AUDITANDO = 'AUDITANDO';
    const ESTADO_AUDITADO = 'AUDITADO';

    protected $table = 'core_turnos_operativos';

    protected $fillable = array(
        'core_empresa_id', 'contexto_tipo', 'contexto_id', 'pdv_id', 'teso_caja_id',
        'fecha_operativa', 'abierto_en', 'cerrado_en', 'abierto_por', 'cerrado_por',
        'saldo_inicial', 'saldo_cierre', 'estado', 'codigo', 'clave_contexto_abierto', 'observaciones'
    );

    protected $dates = array('abierto_en', 'cerrado_en');

    protected $stateTransitionAuthorized = false;

    public $urls_acciones = '{"create":"no","edit":"no","eliminar":"no","show":"web/id_fila"}';

    public $encabezado_tabla = array(
        '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>',
        'Código', 'Fecha operativa', 'Apertura',
        'Abierto por', 'Cierre', 'Cerrado por', 'Estado'
    );

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($turno) {
            $identityFields = array('core_empresa_id', 'contexto_tipo', 'contexto_id', 'pdv_id', 'teso_caja_id', 'fecha_operativa', 'abierto_en', 'codigo');
            foreach ($identityFields as $field) {
                if ($turno->isDirty($field)) {
                    throw new \App\Core\Exceptions\TurnoIntegrityException('La identidad del turno es inmutable después de la apertura. No se puede modificar ' . $field . '.');
                }
            }
            $previousState = $turno->getOriginal('estado');
            if ($turno->isDirty('estado') && !$turno->stateTransitionAuthorized) {
                throw new \App\Core\Exceptions\TurnoStateException('Los cambios de estado del turno deben realizarse mediante TurnoManager.');
            }
            if ($previousState === self::ESTADO_AUDITADO && !$turno->stateTransitionAuthorized && $turno->isDirty()) {
                throw new \App\Core\Exceptions\TurnoStateException('Un turno auditado es inmutable; utilice un ajuste o proceso excepcional autorizado.');
            }
            $turno->stateTransitionAuthorized = false;
        });
    }

    public function authorizeStateTransition()
    {
        $this->stateTransitionAuthorized = true;
        return $this;
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'core_empresa_id');
    }

    public function authorizeCrudAction($action, $user)
    {
        return !is_null($user) && $user->can('turnos.operativos.consultar');
    }

    public function pdv()
    {
        return $this->belongsTo('App\VentasPos\Pdv', 'pdv_id');
    }

    public function eventos()
    {
        return $this->hasMany(TurnoEvento::class, 'turno_operativo_id');
    }

    public function scopeAbiertos($query)
    {
        return $query->where('estado', self::ESTADO_ABIERTO);
    }

    public function estaAbierto()
    {
        return $this->estado === self::ESTADO_ABIERTO && is_null($this->cerrado_en);
    }

    public function getDescripcionAttribute()
    {
        return $this->codigo . ' - ' . $this->fecha_operativa . ' - ' . $this->estado;
    }

    public static function consultar_registros($nroRegistros, $search)
    {
        $query = static::queryForCurrentCompany()
            ->leftJoin('vtas_pos_puntos_de_ventas AS turno_contexto_pdv', function ($join) {
                $join->on('turno_contexto_pdv.id', '=', 'core_turnos_operativos.contexto_id')
                    ->on('turno_contexto_pdv.core_empresa_id', '=', 'core_turnos_operativos.core_empresa_id');
            })
            ->leftJoin('users AS turno_usuario_apertura', 'turno_usuario_apertura.id', '=', 'core_turnos_operativos.abierto_por')
            ->leftJoin('users AS turno_usuario_cierre', 'turno_usuario_cierre.id', '=', 'core_turnos_operativos.cerrado_por')
            ->select(
            'core_turnos_operativos.codigo AS campo1',
            'core_turnos_operativos.fecha_operativa AS campo2',
            'core_turnos_operativos.abierto_en AS campo3',
            DB::raw(static::userWithResponsibleSql('turno_usuario_apertura') . ' AS campo4'),
            'core_turnos_operativos.cerrado_en AS campo5',
            DB::raw(static::userWithResponsibleSql('turno_usuario_cierre') . ' AS campo6'),
            'core_turnos_operativos.estado AS campo7',
            'core_turnos_operativos.id AS campo8'
        );
        static::applySearch($query, $search);
        return $query->orderBy('core_turnos_operativos.id', 'DESC')->paginate($nroRegistros);
    }

    public static function sqlString($search)
    {
        $query = static::queryForCurrentCompany()
            ->leftJoin('vtas_pos_puntos_de_ventas AS turno_contexto_pdv', function ($join) {
                $join->on('turno_contexto_pdv.id', '=', 'core_turnos_operativos.contexto_id')
                    ->on('turno_contexto_pdv.core_empresa_id', '=', 'core_turnos_operativos.core_empresa_id');
            })
            ->leftJoin('users AS turno_usuario_apertura', 'turno_usuario_apertura.id', '=', 'core_turnos_operativos.abierto_por')
            ->leftJoin('users AS turno_usuario_cierre', 'turno_usuario_cierre.id', '=', 'core_turnos_operativos.cerrado_por')
            ->select(
            'core_turnos_operativos.codigo AS CODIGO',
            'core_turnos_operativos.fecha_operativa AS FECHA_OPERATIVA',
            DB::raw(static::contextDescriptionSql() . ' AS CONTEXTO_OPERATIVO'),
            'core_turnos_operativos.abierto_en AS APERTURA',
            DB::raw(static::userWithResponsibleSql('turno_usuario_apertura') . ' AS ABIERTO_POR'),
            'core_turnos_operativos.cerrado_en AS CIERRE',
            DB::raw(static::userWithResponsibleSql('turno_usuario_cierre') . ' AS CERRADO_POR'),
            'core_turnos_operativos.estado AS ESTADO'
        );
        static::applySearch($query, $search);
        return static::interpolate($query->orderBy('core_turnos_operativos.id', 'DESC'));
    }

    public static function tituloExport()
    {
        return 'TURNOS OPERATIVOS';
    }

    public static function opciones_campo_select()
    {
        $options = array('' => '');
        $query = static::queryForCurrentCompany()->orderBy('id', 'DESC')->limit(250);
        foreach ($query->get() as $turno) {
            $options[$turno->id] = $turno->codigo . ' | ' . $turno->fecha_operativa . ' | '
                . $turno->contexto_tipo . ' ' . $turno->contexto_id . ' | ' . $turno->estado;
        }
        return $options;
    }

    protected static function queryForCurrentCompany()
    {
        $query = static::query();
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
                ->orWhere('core_turnos_operativos.fecha_operativa', 'LIKE', $like)
                ->orWhere('core_turnos_operativos.contexto_tipo', 'LIKE', $like)
                ->orWhere('core_turnos_operativos.contexto_id', 'LIKE', $like)
                ->orWhere('turno_contexto_pdv.descripcion', 'LIKE', $like)
                ->orWhere('turno_usuario_apertura.name', 'LIKE', $like)
                ->orWhere('turno_usuario_cierre.name', 'LIKE', $like)
                ->orWhere(DB::raw(static::responsibleSql()), 'LIKE', $like)
                ->orWhere('core_turnos_operativos.estado', 'LIKE', $like);
        });
    }

    protected static function contextDescriptionSql()
    {
        return "CASE
            WHEN core_turnos_operativos.contexto_tipo = 'pdv' THEN
                COALESCE(
                    CONCAT('PDV ', turno_contexto_pdv.id, ' - ', turno_contexto_pdv.descripcion),
                    CONCAT('PDV ', core_turnos_operativos.contexto_id, ' - No encontrado')
                )
            ELSE CONCAT(core_turnos_operativos.contexto_tipo, ' ', core_turnos_operativos.contexto_id)
        END";
    }

    protected static function responsibleSql()
    {
        return "(SELECT turno_apertura.responsable
            FROM vtas_pos_apertura_encabezados AS turno_apertura
            WHERE turno_apertura.turno_operativo_id = core_turnos_operativos.id
            ORDER BY turno_apertura.id DESC
            LIMIT 1)";
    }

    protected static function userWithResponsibleSql($userAlias)
    {
        return "COALESCE(
            NULLIF(
                CONCAT_WS(', ', NULLIF(" . $userAlias . ".name, ''), NULLIF(" . static::responsibleSql() . ", '')),
                ''
            ),
            '—'
        )";
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
