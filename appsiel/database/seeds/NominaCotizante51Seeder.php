<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NominaCotizante51Seeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('nom_contratos') || !Schema::hasColumn('nom_contratos', 'dias_laborados_mes')) {
            return;
        }

        $contratos = DB::table('nom_contratos')
            ->select('id', 'horas_laborales')
            ->where('tipo_cotizante', 51)
            ->whereNull('dias_laborados_mes')
            ->get();

        foreach ($contratos as $contrato) {
            $dias = 0;
            if ((float)$contrato->horas_laborales > 0) {
                $dias = (int)round((float)$contrato->horas_laborales / (float)config('nomina.horas_dia_laboral'), 0);
            }

            if ($dias <= 0) {
                $dias = 7;
            }

            if ($dias > 30) {
                $dias = 30;
            }

            DB::table('nom_contratos')
                ->where('id', $contrato->id)
                ->update(['dias_laborados_mes' => $dias]);
        }
    }
}
