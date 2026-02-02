<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CumplimientoGuiasReporteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $reporte = DB::table('sys_reportes')->where('url_form_action', 'sga_cumplimiento_guias')->first();

        if ($reporte) {
            $reporteId = $reporte->id;
        } else {
            $reporteId = DB::table('sys_reportes')->insertGetId([
                'descripcion' => 'Cumplimiento de guias academicas',
                'core_app_id' => 5,
                'url_form_action' => 'sga_cumplimiento_guias',
                'estado' => 'Activo',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $campos = [
            ['name' => 'periodo_lectivo_id', 'orden' => 0],
            ['name' => 'periodo_id', 'orden' => 2],
            ['name' => 'curso_id', 'orden' => 4],
            ['name' => 'asignatura_id', 'orden' => 6],
            ['name' => 'user_id', 'orden' => 8],
        ];

        foreach ($campos as $campo) {
            $campoId = DB::table('sys_campos')->where('name', $campo['name'])->value('id');
            if (!$campoId) {
                continue;
            }

            $exists = DB::table('sys_reporte_tiene_campos')
                ->where('core_reporte_id', $reporteId)
                ->where('core_campo_id', $campoId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('sys_reporte_tiene_campos')->insert([
                'core_reporte_id' => $reporteId,
                'core_campo_id' => $campoId,
                'orden' => $campo['orden'],
            ]);
        }
    }
}
