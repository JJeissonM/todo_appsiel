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
        if (env('APPSIEL_CLIENTE') === 'SIESA') {
            $this->call(SiesaListasDescuentosSeeder::class);
            $this->call(SiesaClientesSeeder::class);
            $this->call(SiesaEncabezadosListasDescuentosSeeder::class);
            $this->call(SiesaProveedoresEnterpriseSeeder::class);
            $this->call(SiesaImpuestosSeeder::class);
            $this->call(SiesaRetencionesSeeder::class);
            $this->call(SiesaDatosCompletosProveedoresSeeder::class);
        }
        $this->call(NominaActualizacionSueldosSeeder::class);
        $this->call(ProveedorCuentasBancariasSeeder::class);
        $this->call(TesoreriaChequerasPermissionSeeder::class);
        $this->call(ComprasRetencionesLineaSeeder::class);
        $this->call(NominaCotizante51Seeder::class);
    }
}

