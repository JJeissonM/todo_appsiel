<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TurnoConfiguracion extends Model
{
    const MODO_TRADICIONAL = 'TRADICIONAL';
    const MODO_TURNOS = 'TURNOS';

    protected $table = 'core_turno_configuraciones';

    protected $fillable = array(
        'core_empresa_id', 'modulo', 'contexto_tipo', 'contexto_id', 'modo',
        'creado_por', 'modificado_por'
    );

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","store":"turnos/configuraciones","update":"turnos/configuraciones/id_fila","show":"web/id_fila","eliminar":"turnos/configuraciones/id_fila/eliminar"}';

    public $archivo_js = 'assets/js/core/turno_configuracion.js';

    public $encabezado_tabla = array(
        '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>',
        'Empresa', 'Módulo', 'Contexto', 'ID contexto', 'Modo', 'Actualizado'
    );

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($configuration) {
            $analysis = app(\App\Core\Services\TurnoConfigurationService::class)->analyzeCandidate($configuration->getAttributes());
            $errors = $analysis['errors'];
            if (!empty($errors)) {
                throw new \App\Core\Exceptions\TurnoIntegrityException(implode(' ', $errors));
            }
        });
        static::saved(function () {
            app(\App\Core\Services\TurnoModeResolver::class)->clearCache();
        });
        static::deleting(function ($configuration) {
            app(\App\Core\Services\TurnoConfigurationService::class)->assertCanDelete($configuration);
        });
        static::deleted(function () {
            app(\App\Core\Services\TurnoModeResolver::class)->clearCache();
        });
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'core_empresa_id');
    }

    public function authorizeCrudAction($action, $user)
    {
        return !is_null($user) && $user->can('turnos.configuraciones.gestionar');
    }

    public function getDescripcionAttribute()
    {
        return $this->modulo . ' / ' . $this->contexto_tipo . ' ' . $this->contexto_id . ' / ' . $this->modo;
    }

    public static function consultar_registros($nroRegistros, $search)
    {
        $query = static::queryForCurrentCompany()
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'core_turno_configuraciones.core_empresa_id')
            ->select(
                'core_empresas.descripcion AS campo1',
                'core_turno_configuraciones.modulo AS campo2',
                'core_turno_configuraciones.contexto_tipo AS campo3',
                'core_turno_configuraciones.contexto_id AS campo4',
                'core_turno_configuraciones.modo AS campo5',
                'core_turno_configuraciones.updated_at AS campo6',
                'core_turno_configuraciones.id AS campo7'
            );

        static::applySearch($query, $search);

        return $query->orderBy('core_turno_configuraciones.updated_at', 'DESC')->paginate($nroRegistros);
    }

    public static function sqlString($search)
    {
        $query = static::queryForCurrentCompany()
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'core_turno_configuraciones.core_empresa_id')
            ->select(
                'core_empresas.descripcion AS EMPRESA',
                'core_turno_configuraciones.modulo AS MODULO',
                'core_turno_configuraciones.contexto_tipo AS CONTEXTO',
                'core_turno_configuraciones.contexto_id AS CONTEXTO_ID',
                'core_turno_configuraciones.modo AS MODO',
                'core_turno_configuraciones.updated_at AS ACTUALIZADO'
            );
        static::applySearch($query, $search);
        return static::interpolate($query->orderBy('core_turno_configuraciones.updated_at', 'DESC'));
    }

    public static function tituloExport()
    {
        return 'CONFIGURACIÓN DE TURNOS OPERATIVOS';
    }

    public static function get_campos_adicionales_create($campos)
    {
        return static::prepareAdminFields($campos, null, false);
    }

    public static function get_campos_adicionales_edit($campos, $registro)
    {
        return static::prepareAdminFields($campos, $registro, true);
    }

    protected static function prepareAdminFields($campos, $registro, $editing)
    {
        $empresaId = Auth::check() ? (int)Auth::user()->empresa_id : 0;
        foreach ($campos as $key => $campo) {
            if ($campo['name'] === 'core_empresa_id') {
                $campos[$key]['tipo'] = 'hidden';
                $campos[$key]['value'] = $editing ? (int)$registro->core_empresa_id : $empresaId;
            }
            if ($campo['name'] === 'contexto_id') {
                $campos[$key]['opciones'] = static::contextOptions($empresaId);
            }
            if ($editing && in_array($campo['name'], array('core_empresa_id', 'modulo', 'contexto_tipo', 'contexto_id'), true)) {
                $campos[$key]['editable'] = 0;
                if ($campo['name'] !== 'core_empresa_id') {
                    $campos[$key]['atributos'] = array_merge((array)$campos[$key]['atributos'], array('disabled' => 'disabled'));
                }
            }
        }
        return $campos;
    }

    protected static function contextOptions($empresaId)
    {
        $options = array(0 => 'Empresa / todos los contextos');
        if ($empresaId <= 0) {
            return $options;
        }
        $pdvs = DB::table('vtas_pos_puntos_de_ventas')
            ->where('core_empresa_id', $empresaId)->orderBy('descripcion')->get();
        foreach ($pdvs as $pdv) {
            $options[(int)$pdv->id] = 'PDV ' . $pdv->id . ' - ' . $pdv->descripcion;
        }
        return $options;
    }

    protected static function queryForCurrentCompany()
    {
        $query = static::query();
        if (Auth::check()) {
            $query->where('core_turno_configuraciones.core_empresa_id', (int)Auth::user()->empresa_id);
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
            $inner->where('core_empresas.descripcion', 'LIKE', $like)
                ->orWhere('core_turno_configuraciones.modulo', 'LIKE', $like)
                ->orWhere('core_turno_configuraciones.contexto_tipo', 'LIKE', $like)
                ->orWhere('core_turno_configuraciones.contexto_id', 'LIKE', $like)
                ->orWhere('core_turno_configuraciones.modo', 'LIKE', $like);
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
