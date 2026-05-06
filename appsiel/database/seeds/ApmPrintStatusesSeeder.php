<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Ventas\ApmPrintStatus;

class ApmPrintStatusesSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('apm_print_statuses')) {
            return;
        }

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
