<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ProveedorCuentasBancariasSeeder::class);
        $this->call(TesoreriaChequerasPermissionSeeder::class);
        $this->call(ComprasRetencionesLineaSeeder::class);
        $this->call(ApmPrintStatusesSeeder::class);
        $this->call(NominaParametrosLegalesSeeder::class);
    }
}
