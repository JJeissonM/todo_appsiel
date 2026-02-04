<?php

namespace App\Contratotransporte\Services;

use App\Contratotransporte\Contrato;
use App\Contratotransporte\FuecAdicional;
use Carbon\Carbon;

class FuecAnulacionService
{
    public function anularContrato(Contrato $contrato, string $motivo, int $userId): bool
    {
        if ($contrato->estado === 'ANULADO') {
            return false;
        }

        $contrato->estado = 'ANULADO';
        $contrato->anulacion_motivo = $motivo;
        $contrato->anulado_por = $userId;
        $contrato->anulado_el = Carbon::now();

        return (bool) $contrato->save();
    }

    public function anularFuecAdicional(FuecAdicional $fuecAdicional, string $motivo, int $userId): bool
    {
        if ($fuecAdicional->estado === 'ANULADO') {
            return false;
        }

        $fuecAdicional->estado = 'ANULADO';
        $fuecAdicional->anulacion_motivo = $motivo;
        $fuecAdicional->anulado_por = $userId;
        $fuecAdicional->anulado_el = Carbon::now();

        return (bool) $fuecAdicional->save();
    }
}
