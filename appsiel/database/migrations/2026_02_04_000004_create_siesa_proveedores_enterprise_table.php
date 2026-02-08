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


/*
- TipoRegistro campo calculado de acuerdo al CodClaseImpRetencion: Impuestos = 49, Retención = 50

- CodClienteProveedor, SucurClienteProveedor vienen de la tabla siesa_proveedores_enterprise, el CodClienteProveedor es el campo Codigo y el SucurClienteProveedor es el campo Sucursal. Estos campos se deben repetir en cada fila que se cree para ese proveedor dependiendo de la cantidad de siglas de impuestos y retenciones que tenga el proveedor en las columnas de impuestos y retenciones. Por ejemplo, si el proveedor tiene 3 siglas de impuestos y 2 siglas de retenciones, se deben crear 5 filas para ese proveedor con el mismo CodClienteProveedor y SucurClienteProveedor pero con diferente CodClaseImpRetencion (49 para impuestos y 50 para retenciones) y diferente Llave (según la sigla del impuesto o retención).

- CodClaseImpRetencion es el campo Clase de la tabla siesa_retenciones o de la tabla siesa_impuestos dependiendo de la Sigla que el proveedor tenga en al alguna columna. Debe haber n filas segun la cantidad de siglas que tenga el proveedor en las columnas de impuestos y retenciones. Por ejemplo, si el proveedor tiene 3 siglas de impuestos y 2 siglas de retenciones, se deben crear 5 filas para ese proveedor con el mismo CodClienteProveedor y SucurClienteProveedor pero con diferente CodClaseImpRetencion y TipoRegistro (49 para impuestos y 50 para retenciones).

- ConfTercero (si es tipo IVA: 0=NO APLICA, 1=APLICA), si es RET (0=NO APLICA, 1=APLICA, 2=AUTORETENEDOR)

- Llave es el campo Llave de la tabla siesa_retenciones o de la tabla siesa_impuestos dependiendo de la Sigla que el proveedor tenga en al alguna columna. Debe haber n filas segun la cantidad de siglas que tenga el proveedor en las columnas de impuestos y retenciones. Por ejemplo, si el proveedor tiene 3 siglas de impuestos y 2 siglas de retenciones, se deben crear 5 filas para ese proveedor con el mismo CodClienteProveedor y SucurClienteProveedor pero con diferente Llave y CodClaseImpRetencion (49 para impuestos y 50 para retenciones).
*/

