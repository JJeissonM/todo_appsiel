<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeLastIssuedNumberInTesoChequeras extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('teso_chequeras') ||
            !Schema::hasTable('teso_control_cheques') ||
            !Schema::hasColumn('teso_control_cheques', 'teso_chequera_id')) {
            return;
        }

        // La implementación inicial guardaba en consecutivo_actual el número
        // siguiente. Solo se corrigen registros que conservan exactamente ese
        // patrón y cuentan con trazabilidad del cheque emitido.
        $ultimosEmitidos = DB::table('teso_control_cheques')
            ->whereNotNull('teso_chequera_id')
            ->select(
                'teso_chequera_id',
                DB::raw('MAX(CAST(numero_cheque AS UNSIGNED)) AS ultimo_emitido')
            )
            ->groupBy('teso_chequera_id')
            ->get();

        foreach ($ultimosEmitidos as $registro) {
            $ultimoEmitido = (int)$registro->ultimo_emitido;

            DB::table('teso_chequeras')
                ->where('id', (int)$registro->teso_chequera_id)
                ->where('consecutivo_actual', $ultimoEmitido + 1)
                ->update(['consecutivo_actual' => $ultimoEmitido]);
        }
    }

    public function down()
    {
        // No se revierte: incrementar este valor podría saltar un cheque físico.
    }
}
