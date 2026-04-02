<?php

use Illuminate\Database\Seeder;
use App\Ventas\ApmPrintStatus;

class ApmPrintStatusesSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['code' => 'pending', 'description' => 'Pendiente de reimpresion manual'],
            ['code' => 'printed', 'description' => 'Impreso correctamente'],
            ['code' => 'cancelled', 'description' => 'Cancelado manualmente'],
            ['code' => 'retired', 'description' => 'Retirado manualmente']
        ];

        foreach ($statuses as $status) {
            ApmPrintStatus::firstOrCreate(['code' => $status['code']], $status);
        }
    }
}
