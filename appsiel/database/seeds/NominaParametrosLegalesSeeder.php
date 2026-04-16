<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NominaParametrosLegalesSeeder extends Seeder
{
    public function run()
    {
        $parametros = [
            [
                'fecha_inicio' => '2024-01-01',
                'fecha_fin' => '2024-12-31',
                'smmlv' => 1300000,
                'auxilio_transporte' => 162000,
                'uvt' => 47065,
                'normatividad' => 'Decretos 2292 y 2293 de 2023 / Resolucion DIAN 187 de 2023',
            ],
            [
                'fecha_inicio' => '2025-01-01',
                'fecha_fin' => '2025-12-31',
                'smmlv' => 1423500,
                'auxilio_transporte' => 200000,
                'uvt' => 49799,
                'normatividad' => 'Decretos 1572 y 1573 de 2024 / Resolucion DIAN 193 de 2024',
            ],
            [
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'smmlv' => 1750905,
                'auxilio_transporte' => 249095,
                'uvt' => 52374,
                'normatividad' => 'Decretos 1469 y 1470 de 2025 / Resolucion DIAN 238 de 2025',
            ],
        ];

        foreach ($parametros as $parametro) {
            $exists = DB::table('nom_parametros_legales')
                ->where('fecha_inicio', $parametro['fecha_inicio'])
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('nom_parametros_legales')->insert($parametro + [
                'horas_laborales' => (float)config('nomina.horas_laborales'),
                'horas_dia_laboral' => (float)config('nomina.horas_dia_laboral'),
                'estado' => 'Activo',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
