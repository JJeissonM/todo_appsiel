<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class EnsurePdvApmFields extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vtas_pos_puntos_de_ventas')) {
            return;
        }

        Schema::table('vtas_pos_puntos_de_ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('vtas_pos_puntos_de_ventas', 'serial_maquina')) {
                $table->string('serial_maquina', 120)->nullable();
            }

            if (!Schema::hasColumn('vtas_pos_puntos_de_ventas', 'plantilla_factura_pos_default')) {
                $table->string('plantilla_factura_pos_default', 120)->nullable();
            }

            if (!Schema::hasColumn('vtas_pos_puntos_de_ventas', 'impresora_principal_por_defecto')) {
                $table->string('impresora_principal_por_defecto', 120)->nullable();
            }

            if (!Schema::hasColumn('vtas_pos_puntos_de_ventas', 'impresora_cocina_por_defecto')) {
                $table->string('impresora_cocina_por_defecto', 120)->nullable();
            }

            if (!Schema::hasColumn('vtas_pos_puntos_de_ventas', 'imprimir_factura_automaticamente')) {
                $table->unsignedTinyInteger('imprimir_factura_automaticamente')->default(0);
            }

            if (!Schema::hasColumn('vtas_pos_puntos_de_ventas', 'enviar_impresion_directamente_a_la_impresora')) {
                $table->unsignedTinyInteger('enviar_impresion_directamente_a_la_impresora')->default(0);
            }

            if (!Schema::hasColumn('vtas_pos_puntos_de_ventas', 'usar_complemento_JSPrintManager')) {
                $table->unsignedTinyInteger('usar_complemento_JSPrintManager')->default(0);
            }
        });
    }

    public function down()
    {
        // No se eliminan columnas porque algunas instalaciones ya podrian tenerlas
        // creadas por migraciones antiguas o ajustes manuales.
    }
}
