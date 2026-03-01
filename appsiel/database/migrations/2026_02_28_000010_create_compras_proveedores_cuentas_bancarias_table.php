<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateComprasProveedoresCuentasBancariasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('compras_proveedores_cuentas_bancarias')) {
            return;
        }

        Schema::create('compras_proveedores_cuentas_bancarias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tercero_id');
            $table->unsignedInteger('entidad_financiera_id');
            $table->string('tipo_cuenta', 30);
            $table->string('numero_cuenta', 80);
            $table->string('codigo_ciudad', 20);
            $table->string('estado', 20)->default('Activo');
            $table->timestamps();

            $table->index('tercero_id', 'idx_cpcb_tercero');
            $table->index('entidad_financiera_id', 'idx_cpcb_entidad');
            $table->index('codigo_ciudad', 'idx_cpcb_ciudad');
            $table->unique(['tercero_id', 'entidad_financiera_id', 'numero_cuenta'], 'uniq_tercero_entidad_numero');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('compras_proveedores_cuentas_bancarias');
    }
}
