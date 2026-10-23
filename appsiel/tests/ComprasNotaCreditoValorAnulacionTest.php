<?php

use App\Http\Controllers\Compras\NotaCreditoController;
use App\Http\Controllers\Compras\NotaCreditoValorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotaCreditoValorAnulacionControllerStub extends NotaCreditoValorController
{
    public $recosteos = [];
    public $fallarRecosteo = false;

    public function set_variables_globales()
    {
        $this->app = (object)['id'=>9];
        $this->modelo = (object)['id'=>332];
        $this->transaccion = (object)['id'=>61];
    }

    public function recostear_registros_entrada_almacen($factura, $total, $accion)
    {
        if ($this->fallarRecosteo) {
            throw new RuntimeException('Fallo durante recosteo');
        }
        $this->recosteos[] = $total;
    }
}

class ComprasNotaCreditoValorAnulacionTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        // Todas las operaciones de estas pruebas se ejecutan en memoria.
        config(['database.connections.ncvc_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('ncvc_test');
        Auth::setUser(new App\User(['id'=>1,'empresa_id'=>1,'email'=>'prueba@example.com']));
        Schema::create('compras_doc_encabezados', function ($t) {
            $t->increments('id'); $t->integer('core_empresa_id'); $t->integer('core_tipo_transaccion_id');
            $t->integer('core_tipo_doc_app_id'); $t->integer('consecutivo');
            $t->integer('compras_doc_relacionado_id')->default(0); $t->integer('entrada_almacen_id')->default(0);
            $t->integer('core_tercero_id')->default(235); $t->integer('proveedor_id')->default(18);
            $t->string('fecha')->default('2026-10-18'); $t->string('creado_por')->default('prueba@example.com');
            $t->string('modificado_por')->default(''); $t->string('forma_pago')->default('contado');
            $t->string('estado')->default('Activo'); $t->double('valor_total'); $t->timestamps();
        });
        Schema::create('compras_doc_registros', function ($t) {
            $t->increments('id'); $t->integer('compras_doc_encabezado_id'); $t->double('precio_total');
            $t->string('estado')->default('Activo'); $t->string('modificado_por')->default(''); $t->timestamps();
        });
        foreach (['teso_movimientos','contab_movimientos','compras_movimientos'] as $table) {
            Schema::create($table, function ($t) {
                $t->increments('id'); $t->integer('core_empresa_id'); $t->integer('core_tipo_transaccion_id');
                $t->integer('core_tipo_doc_app_id'); $t->integer('consecutivo');
                $t->double('valor_movimiento')->default(510273); $t->timestamps();
            });
        }
        Schema::create('cxp_movimientos', function ($t) {
            $t->increments('id'); $t->integer('core_empresa_id'); $t->integer('core_tipo_transaccion_id');
            $t->integer('core_tipo_doc_app_id'); $t->integer('consecutivo');
            $t->double('saldo_pendiente'); $t->double('valor_pagado'); $t->string('estado'); $t->timestamps();
        });
        Schema::create('cxp_abonos', function ($t) {
            $t->increments('id');
            foreach (['core_empresa_id','core_tercero_id','core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','referencia_tercero_id','doc_cxp_transacc_id','doc_cxp_tipo_doc_id','doc_cxp_consecutivo','doc_cruce_transacc_id','doc_cruce_tipo_doc_id','doc_cruce_consecutivo'] as $c) $t->integer($c);
            $t->string('fecha'); $t->string('modelo_referencia_tercero_index'); $t->string('creado_por'); $t->double('abono'); $t->timestamps();
        });
        DB::table('compras_doc_encabezados')->insert([
            ['id'=>1,'core_empresa_id'=>1,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>1,'consecutivo'=>100,'valor_total'=>1000000,'compras_doc_relacionado_id'=>0],
            ['id'=>2,'core_empresa_id'=>1,'core_tipo_transaccion_id'=>61,'core_tipo_doc_app_id'=>59,'consecutivo'=>211,'valor_total'=>-495.30643171178,'compras_doc_relacionado_id'=>1],
        ]);
        DB::table('compras_doc_registros')->insert([
            ['compras_doc_encabezado_id'=>2,'precio_total'=>-509777.69356829],
            ['compras_doc_encabezado_id'=>2,'precio_total'=>-495.30643171178],
        ]);
        foreach (['teso_movimientos','contab_movimientos','compras_movimientos'] as $table) {
            DB::table($table)->insert([
                ['core_empresa_id'=>1,'core_tipo_transaccion_id'=>61,'core_tipo_doc_app_id'=>59,'consecutivo'=>211],
                ['core_empresa_id'=>2,'core_tipo_transaccion_id'=>61,'core_tipo_doc_app_id'=>59,'consecutivo'=>211],
                ['core_empresa_id'=>1,'core_tipo_transaccion_id'=>61,'core_tipo_doc_app_id'=>59,'consecutivo'=>212],
            ]);
        }
    }

    public function test_anula_total_de_todas_las_lineas_y_no_afecta_otros_documentos()
    {
        // Un segundo medio de pago debe eliminarse por la misma identidad.
        DB::table('teso_movimientos')->insert(['core_empresa_id'=>1,'core_tipo_transaccion_id'=>61,'core_tipo_doc_app_id'=>59,'consecutivo'=>211,'valor_movimiento'=>100]);
        $controller = new NotaCreditoValorAnulacionControllerStub();
        $controller->anular(2);
        $nota = DB::table('compras_doc_encabezados')->where('id',2)->first();
        $this->assertEquals('Anulado',$nota->estado);
        $this->assertEquals(-510273,$nota->valor_total,'',0.00001);
        $this->assertEquals(1,$nota->compras_doc_relacionado_id);
        foreach (['teso_movimientos','contab_movimientos','compras_movimientos'] as $table) {
            $this->assertEquals(2,DB::table($table)->count());
            $this->assertEquals(1,DB::table($table)->where('core_empresa_id',2)->count());
        }
        $controller->anular(2);
        $this->assertCount(1,$controller->recosteos,'La segunda anulación no restaura costos otra vez');
    }

    public function test_fallo_revierte_toda_la_anulacion()
    {
        $controller = new NotaCreditoValorAnulacionControllerStub(); $controller->fallarRecosteo = true;
        try { $controller->anular(2); $this->fail('Debía fallar'); }
        catch (RuntimeException $e) { $this->assertEquals('Fallo durante recosteo',$e->getMessage()); }
        $this->assertEquals('Activo',DB::table('compras_doc_encabezados')->where('id',2)->value('estado'));
        foreach (['teso_movimientos','contab_movimientos','compras_movimientos'] as $table) $this->assertEquals(3,DB::table($table)->count());
    }

    public function test_eliminar_tesoreria_no_necesita_factura_ni_importe_coincidente()
    {
        $nota = App\Compras\ComprasDocEncabezado::find(2);
        NotaCreditoController::actualizar_movimiento_tesoreria(495.31,null,$nota,'anular');
        $this->assertEquals(2,DB::table('teso_movimientos')->count());
    }

    public function test_credito_restaura_total_y_elimina_solo_su_abono()
    {
        DB::table('compras_doc_encabezados')->update(['forma_pago'=>'credito']);
        DB::table('cxp_movimientos')->insert(['core_empresa_id'=>1,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>1,'consecutivo'=>100,'saldo_pendiente'=>489727,'valor_pagado'=>510273,'estado'=>'Pendiente']);
        $nota = App\Compras\ComprasDocEncabezado::find(2); $factura = App\Compras\ComprasDocEncabezado::find(1);
        // Preparar un abono con la misma estructura que usa el flujo de creación.
        DB::table('cxp_abonos')->insert(['core_empresa_id'=>1,'core_tercero_id'=>235,'core_tipo_transaccion_id'=>61,'core_tipo_doc_app_id'=>59,'consecutivo'=>211,'referencia_tercero_id'=>18,'doc_cxp_transacc_id'=>25,'doc_cxp_tipo_doc_id'=>1,'doc_cxp_consecutivo'=>100,'doc_cruce_transacc_id'=>0,'doc_cruce_tipo_doc_id'=>0,'doc_cruce_consecutivo'=>0,'fecha'=>'2026-10-18','modelo_referencia_tercero_index'=>'App\\Compras\\Proveedor','creado_por'=>'prueba@example.com','abono'=>510273]);
        $controller = new NotaCreditoValorAnulacionControllerStub(); $controller->anular(2);
        $this->assertEquals(1000000,DB::table('cxp_movimientos')->value('saldo_pendiente'),'',0.00001);
        $this->assertEquals(0,DB::table('cxp_movimientos')->value('valor_pagado'),'',0.00001);
        $this->assertEquals(0,DB::table('cxp_abonos')->count());
        $controller->anular(2);
        $this->assertEquals(1000000,DB::table('cxp_movimientos')->value('saldo_pendiente'),'',0.00001);
    }
}
