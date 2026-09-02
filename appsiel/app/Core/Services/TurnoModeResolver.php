<?php

namespace App\Core\Services;

use App\Core\TurnoConfiguracion;
use Illuminate\Support\Facades\Schema;

class TurnoModeResolver
{
    protected $cache = array();

    public function enabled($empresaId, $modulo = '*', $contextoTipo = '*', $contextoId = 0)
    {
        $empresaId = (int)$empresaId;
        $contextoId = (int)$contextoId;
        if ($empresaId <= 0 || !Schema::hasTable('core_turno_configuraciones')) {
            return false;
        }

        if (config('turnos.simple_company_mode', false)) {
            $key = 'empresa|' . $empresaId;
            if (array_key_exists($key, $this->cache)) {
                return $this->cache[$key];
            }

            $global = TurnoConfiguracion::where('core_empresa_id', $empresaId)
                ->where('modulo', '*')
                ->where('contexto_tipo', '*')
                ->where('contexto_id', 0)
                ->orderBy('id', 'DESC')
                ->first();
            if (!is_null($global)) {
                return $this->cache[$key] = $global->modo === TurnoConfiguracion::MODO_TURNOS;
            }

            // Compatibilidad con la configuración granular creada durante QA:
            // si aún no existe la fila global, cualquier activación previa
            // mantiene encendido el modo hasta guardar la configuración simple.
            return $this->cache[$key] = TurnoConfiguracion::where('core_empresa_id', $empresaId)
                ->where('modo', TurnoConfiguracion::MODO_TURNOS)
                ->exists();
        }

        $key = implode('|', array($empresaId, $modulo, $contextoTipo, $contextoId));
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $scopes = array(
            array($modulo, $contextoTipo, $contextoId),
            array('*', $contextoTipo, $contextoId),
            array($modulo, '*', 0),
            array('*', '*', 0),
        );

        foreach ($scopes as $scope) {
            $configuration = TurnoConfiguracion::where('core_empresa_id', $empresaId)
                ->where('modulo', $scope[0])
                ->where('contexto_tipo', $scope[1])
                ->where('contexto_id', $scope[2])
                ->orderBy('id', 'DESC')
                ->first();

            if (!is_null($configuration)) {
                return $this->cache[$key] = $configuration->modo === TurnoConfiguracion::MODO_TURNOS;
            }
        }

        return $this->cache[$key] = false;
    }

    public function enabledForModule($empresaId, $modulo)
    {
        return $this->enabled($empresaId, $modulo, '*', 0);
    }

    public function clearCache()
    {
        $this->cache = array();
    }
}
