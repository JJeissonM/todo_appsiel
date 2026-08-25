<?php

namespace App\Core\Services;

use App\Core\TurnoOperativo;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\FacturaPos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TurnoAssignmentResolver
{
    protected $context;
    protected $manager;

    public function __construct(TurnoContext $context, TurnoManager $manager)
    {
        $this->context = $context;
        $this->manager = $manager;
    }

    public function assign(Model $model, $module, $pdvId = null)
    {
        if (!Schema::hasColumn($model->getTable(), 'turno_operativo_id')) {
            return $model->turno_operativo_id;
        }

        $empresaId = (int)$this->companyId($model);
        if (!empty($model->turno_operativo_id)) {
            $turno = TurnoOperativo::find((int)$model->turno_operativo_id);
            if (is_null($turno) || (int)$turno->core_empresa_id !== $empresaId) {
                throw new \UnexpectedValueException('El turno explicito no existe o pertenece a otra empresa.');
            }
            return $model->turno_operativo_id;
        }

        $turno = $this->context->current();
        if (!is_null($turno) && (int)$turno->core_empresa_id === $empresaId) {
            return $model->turno_operativo_id = $turno->id;
        }

        // Propagacion inequivoca por identidad del documento origen, nunca por fecha/hora.
        $turnoId = $this->fromDocumentIdentity($model);
        if (!is_null($turnoId)) {
            return $model->turno_operativo_id = $turnoId;
        }

        $pdvId = (int)($pdvId ?: $model->getAttribute('pdv_id'));
        if ($pdvId > 0 && $this->manager->enabledForPdv($empresaId, $pdvId, $module)) {
            $turno = $this->manager->currentForPdv($empresaId, $pdvId);
            if (!is_null($turno)) {
                return $model->turno_operativo_id = $turno->id;
            }
        }

        return null;
    }

    protected function companyId(Model $model)
    {
        return $model->getAttribute('core_empresa_id') ?: $model->getAttribute('empresa_id');
    }

    protected function fromDocumentIdentity(Model $model)
    {
        $attributes = array('core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo');
        foreach ($attributes as $attribute) {
            if ($model->getAttribute($attribute) === null || $model->getAttribute($attribute) === '') {
                return null;
            }
        }

        $where = array();
        foreach ($attributes as $attribute) {
            $where[$attribute] = $model->getAttribute($attribute);
        }

        if ($model->getTable() !== 'vtas_pos_doc_encabezados' && Schema::hasTable('vtas_pos_doc_encabezados') && Schema::hasColumn('vtas_pos_doc_encabezados', 'turno_operativo_id')) {
            $source = FacturaPos::where($where)->whereNotNull('turno_operativo_id')->orderBy('id', 'DESC')->first();
            if (!is_null($source)) {
                return (int)$source->turno_operativo_id;
            }
        }

        if ($model->getTable() !== 'vtas_doc_encabezados' && Schema::hasTable('vtas_doc_encabezados') && Schema::hasColumn('vtas_doc_encabezados', 'turno_operativo_id')) {
            $source = VtasDocEncabezado::where($where)->whereNotNull('turno_operativo_id')->orderBy('id', 'DESC')->first();
            if (!is_null($source)) {
                return (int)$source->turno_operativo_id;
            }
        }

        return null;
    }
}
