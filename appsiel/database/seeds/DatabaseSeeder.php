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
        $this->call(IcfesQuestionBankSeeder::class);
        $this->call(CumplimientoGuiasReporteSeeder::class);
        $this->call(SiesaListasDescuentosSeeder::class);
        $this->call(SiesaClientesSeeder::class);
        $this->call(SiesaEncabezadosListasDescuentosSeeder::class);
        $this->call(SiesaProveedoresEnterpriseSeeder::class);
        $this->call(SiesaImpuestosSeeder::class);
        $this->call(SiesaRetencionesSeeder::class);
        $this->call(NominaActualizacionSueldosSeeder::class);
    }
}
