<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiesaDatosCompletosProveedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APPSIEL_CLIENTE') !== 'SIESA') {
            return;
        }
        if (!Schema::hasTable('siesa_datos_completos_proveedores')) {
            Schema::create('siesa_datos_completos_proveedores', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->nullable();
                $table->string('sucursal')->nullable();
                $table->string('razon_social_sucursal')->nullable();
                $table->string('clase_de_proveedor')->nullable();
                $table->string('condicion_de_pago')->nullable();
                $table->string('tipo_proveedor')->nullable();
                $table->string('forma_de_pago')->nullable();
                $table->string('notas')->nullable();
                $table->string('contacto')->nullable();
                $table->string('direccion_1')->nullable();
                $table->string('direccion_2')->nullable();
                $table->string('direccion_3')->nullable();
                $table->string('cod_depto')->nullable();
                $table->string('cod_ciudad')->nullable();
                $table->string('barrio')->nullable();
                $table->string('telefono')->nullable();
                $table->string('email')->nullable();
                $table->string('fecha_ingreso')->nullable();
                $table->string('monto_anual_compras')->nullable();
                $table->string('exige_cotizacion_en_oc_y_entrada')->nullable();
                $table->string('exige_oc_en_entrada_de_almacen')->nullable();
                $table->string('grupo_co')->nullable();
                $table->string('celular')->nullable();
                $table->string('suc_defecto_pe')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APPSIEL_CLIENTE') !== 'SIESA') {
            return;
        }
        if (Schema::hasTable('siesa_datos_completos_proveedores')) {
            Schema::drop('siesa_datos_completos_proveedores');
        }
    }
}


