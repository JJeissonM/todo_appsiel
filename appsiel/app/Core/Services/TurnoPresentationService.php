<?php

namespace App\Core\Services;

use App\Core\TurnoOperativo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TurnoPresentationService
{
    protected $turnCache = array();
    protected $columnCache = array();

    public function describe($document)
    {
        if (is_null($document)) {
            return null;
        }

        $turnAttributeLoaded = $document instanceof Model
            && array_key_exists('turno_operativo_id', $document->getAttributes());
        $turnId = (int)$this->attribute($document, 'turno_operativo_id');
        if ($turnId <= 0 && $document instanceof Model && !$turnAttributeLoaded) {
            $turnId = $this->turnIdFromPersistedModel($document);
        }
        if ($turnId <= 0) {
            return null;
        }

        $companyId = (int)($this->attribute($document, 'core_empresa_id')
            ?: $this->attribute($document, 'empresa_id'));
        $cacheKey = $companyId . '|' . $turnId;
        if (array_key_exists($cacheKey, $this->turnCache)) {
            return $this->turnCache[$cacheKey];
        }

        $query = TurnoOperativo::where('id', $turnId);
        if ($companyId > 0) {
            $query->where('core_empresa_id', $companyId);
        }
        $turn = $query->first();
        if (is_null($turn)) {
            return $this->turnCache[$cacheKey] = null;
        }

        $context = $turn->contexto_tipo . ' ' . $turn->contexto_id;
        if ($turn->contexto_tipo === 'pdv') {
            $pdvName = DB::table('vtas_pos_puntos_de_ventas')
                ->where('core_empresa_id', (int)$turn->core_empresa_id)
                ->where('id', (int)$turn->contexto_id)
                ->value('descripcion');
            $context = 'PDV ' . $turn->contexto_id;
            if (!empty($pdvName)) {
                $context .= ' - ' . $pdvName;
            }
        }

        return $this->turnCache[$cacheKey] = array(
            'id' => (int)$turn->id,
            'codigo' => $turn->codigo,
            'fecha_operativa' => $turn->fecha_operativa,
            'contexto' => $context,
            'estado' => $turn->estado,
        );
    }

    protected function turnIdFromPersistedModel(Model $document)
    {
        $table = $document->getTable();
        $id = (int)$document->getKey();
        if ($id <= 0 || !$this->hasTurnColumn($table)) {
            return 0;
        }
        return (int)DB::table($table)->where($document->getKeyName(), $id)->value('turno_operativo_id');
    }

    protected function hasTurnColumn($table)
    {
        if (!array_key_exists($table, $this->columnCache)) {
            $this->columnCache[$table] = Schema::hasTable($table)
                && Schema::hasColumn($table, 'turno_operativo_id');
        }
        return $this->columnCache[$table];
    }

    protected function attribute($document, $name)
    {
        if ($document instanceof Model) {
            return $document->getAttribute($name);
        }
        if (is_array($document)) {
            return array_key_exists($name, $document) ? $document[$name] : null;
        }
        return is_object($document) && isset($document->{$name}) ? $document->{$name} : null;
    }
}
