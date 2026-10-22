<?php

namespace App\VentasPos\Services;

use App\Core\TurnoOperativo;

class SalesReportShiftService
{
    public function available($companyId)
    {
        return TurnoOperativo::where('core_empresa_id', $companyId)
            ->where('contexto_tipo', 'pdv')
            ->whereIn('estado', [TurnoOperativo::ESTADO_CERRADO, TurnoOperativo::ESTADO_AUDITADO])
            ->whereNotNull('abierto_en')->whereNotNull('cerrado_en')
            ->orderBy('abierto_en', 'desc');
    }

    public function resolve($id, $companyId, $pdvId = 0)
    {
        if (!ctype_digit((string)$id) || (int)$id <= 0) {
            throw new \InvalidArgumentException('Seleccione un turno operativo válido.');
        }
        $shift = $this->available($companyId)->where('id', (int)$id)->first();
        if (!$shift || $shift->cerrado_en < $shift->abierto_en) {
            throw new \InvalidArgumentException('El turno no está cerrado/auditado o no pertenece a la empresa.');
        }
        $shiftPdv = (int)($shift->pdv_id ?: $shift->contexto_id);
        if ((int)$pdvId > 0 && (int)$pdvId !== $shiftPdv) {
            throw new \InvalidArgumentException('El turno no corresponde al punto de venta seleccionado.');
        }
        $shift->responsable = \App\VentasPos\AperturaEncabezado::where('turno_operativo_id', $shift->id)
            ->orderBy('id', 'desc')->value('responsable');
        return $shift;
    }
}
