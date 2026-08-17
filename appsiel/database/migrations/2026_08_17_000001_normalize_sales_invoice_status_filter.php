<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NormalizeSalesInvoiceStatusFilter extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sys_campos')) {
            return;
        }

        DB::table('sys_campos')
            ->where('name', 'estado_facturas')
            ->update([
                'descripcion' => 'Estado de las facturas',
                'opciones' => '{"Contabilizado":"Emitidas/contabilizadas","Pendiente":"Pendientes","Todos":"Todas vigentes"}',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function down()
    {
        if (!Schema::hasTable('sys_campos')) {
            return;
        }

        DB::table('sys_campos')
            ->where('name', 'estado_facturas')
            ->update([
                'descripcion' => 'Estado facturas',
                'opciones' => '{"Contabilizado":"Contabilizado","Pendiente":"Pendiente","Todos":"Todos"}',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
