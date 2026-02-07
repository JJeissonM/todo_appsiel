<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiesaProveedoresEnterpriseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('siesa_proveedores_enterprise')) {
            Schema::create('siesa_proveedores_enterprise', function (Blueprint $table) {
                $table->increments('id');
                $table->string('codigo')->nullable();
                $table->string('razon_social')->nullable();
                $table->string('sucursal')->nullable();
                $table->string('razon_social_sucursal')->nullable();
                $table->string('moneda')->nullable();
                $table->string('desc_moneda')->nullable();
                $table->string('fecha_ingreso')->nullable();
                $table->string('antiguedad')->nullable();
                $table->string('clase_de_proveedor')->nullable();
                $table->string('desc_clase_de_proveedor')->nullable();
                $table->string('condicion_de_pago')->nullable();
                $table->string('desc_condicion_de_pago')->nullable();
                $table->string('dias_gracia')->nullable();
                $table->string('cupo_de_credito')->nullable();
                $table->string('tipo_proveedor')->nullable();
                $table->string('desc_tipo_proveedor')->nullable();
                $table->string('sujeto_o_no_interp')->nullable();
                $table->string('rtservic')->nullable();
                $table->string('llave_rtservic')->nullable();
                $table->string('rtsalari')->nullable();
                $table->string('llave_rtsalari')->nullable();
                $table->string('rtiva1')->nullable();
                $table->string('llave_rtiva1')->nullable();
                $table->string('rthonora')->nullable();
                $table->string('llave_rthonora')->nullable();
                $table->string('rtcomisi')->nullable();
                $table->string('llave_rtcomisi')->nullable();
                $table->string('rtbienes')->nullable();
                $table->string('llave_rtbienes')->nullable();
                $table->string('rtarrend')->nullable();
                $table->string('llave_rtarrend')->nullable();
                $table->string('rivagran')->nullable();
                $table->string('iva_interp')->nullable();
                $table->string('incbolsa')->nullable();
                $table->string('icui')->nullable();
                $table->string('icindust')->nullable();
                $table->string('icd')->nullable();
                $table->string('fedegan')->nullable();
                $table->string('icaser')->nullable();
                $table->string('llave_icaser')->nullable();
                $table->string('icacomer')->nullable();
                $table->string('llave_icacomer')->nullable();
                $table->string('ibua')->nullable();
                $table->string('numero_cuenta')->nullable();
                $table->string('tipo_cuenta')->nullable();
                $table->string('tipo_de_pago')->nullable();
                $table->string('tipo_de_tercero')->nullable();
                $table->string('tipo_de_identificacion')->nullable();
                $table->string('nota')->nullable();
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
        if (Schema::hasTable('siesa_proveedores_enterprise')) {
            Schema::drop('siesa_proveedores_enterprise');
        }
    }
}

// CodProveedor, SucurProveedor, DescripSucursal, ClaseProveedor, CondPago, TipoProveedor, FormaPagoProveedores, Observaciones, Contacto, Direccion1, Direccion2, Direccion3, Pais, Departamento, Ciudad, Barrio, Telefono, CorreoElectronico, FechaDeIngreso, MontoAnualDeCompra, IndDelMontoAnual, IndCotizDeCompra, IndOrdenDeCompraEDI, GrupoCentroOperacion, TelefonoCelular, IndSucPagosElectronicos

