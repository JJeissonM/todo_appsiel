<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComprasRetencionesLineaSeeder extends Seeder
{
    public function run()
    {
        if (Schema::hasTable('compras_doc_registros')) {
            DB::table('compras_doc_registros')
                ->whereNull('contab_retencion_id')
                ->update(['contab_retencion_id' => 0]);

            DB::table('compras_doc_registros')
                ->whereNull('tasa_retencion')
                ->update(['tasa_retencion' => 0]);

            DB::table('compras_doc_registros')
                ->whereNull('valor_retencion')
                ->update(['valor_retencion' => 0]);
        }

        if (Schema::hasTable('contab_registros_retenciones')) {
            DB::table('contab_registros_retenciones')
                ->whereNull('compras_doc_registro_id')
                ->update(['compras_doc_registro_id' => 0]);
        }
    }
}
