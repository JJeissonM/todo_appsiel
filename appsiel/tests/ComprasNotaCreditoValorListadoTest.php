<?php

use App\Compras\NotaCreditoValor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComprasNotaCreditoValorListadoTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        config(['database.connections.ncvc_listado'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('ncvc_listado');
        $pdo = DB::connection()->getPdo();
        $pdo->sqliteCreateFunction('CONCAT', function () { return implode('', func_get_args()); });
        $pdo->sqliteCreateFunction('CONCAT_WS', function () {
            $args = func_get_args(); $separator = array_shift($args);
            return implode($separator, array_filter($args, function ($v) { return $v !== null; }));
        });
        Auth::setUser(new App\User(['empresa_id'=>1]));
        Schema::create('compras_doc_encabezados', function ($t) {
            $t->increments('id');
            foreach (['core_empresa_id','core_tipo_transaccion_id','core_tipo_doc_app_id','core_tercero_id','consecutivo'] as $col) $t->integer($col);
            foreach (['fecha','doc_proveedor_prefijo','doc_proveedor_consecutivo','descripcion','estado'] as $col) $t->string($col)->default('');
            $t->double('valor_total')->default(-7); $t->timestamps();
        });
        Schema::create('core_tipos_docs_apps', function ($t) { $t->increments('id'); $t->string('prefijo'); });
        Schema::create('core_terceros', function ($t) {
            $t->increments('id');
            foreach (['nombre1','otros_nombres','apellido1','apellido2','razon_social','descripcion','numero_identificacion'] as $col) $t->string($col)->nullable();
        });
        Schema::create('compras_doc_registros', function ($t) {
            $t->increments('id'); $t->integer('compras_doc_encabezado_id'); $t->double('precio_total');
        });
        DB::table('core_tipos_docs_apps')->insert(['id'=>59,'prefijo'=>'NCVC']);
        DB::table('core_terceros')->insert([
            ['id'=>1,'descripcion'=>"SANTANA O'NEIL?",'nombre1'=>'','apellido1'=>'','razon_social'=>'','numero_identificacion'=>'901234567'],
            ['id'=>2,'descripcion'=>'Descripción secundaria','nombre1'=>null,'apellido1'=>null,'razon_social'=>'RAZON SOCIAL SAS','numero_identificacion'=>'2'],
            ['id'=>3,'descripcion'=>'Descripción persona','nombre1'=>'ANA','apellido1'=>'PEREZ','razon_social'=>'','numero_identificacion'=>'3'],
        ]);
        foreach ([[1,1,61,1,213],[2,2,61,1,999],[3,1,25,1,888],[4,1,61,2,214],[5,1,61,3,215]] as $r) {
            DB::table('compras_doc_encabezados')->insert(['id'=>$r[0],'core_empresa_id'=>$r[1],'core_tipo_transaccion_id'=>$r[2],'core_tipo_doc_app_id'=>59,'core_tercero_id'=>$r[3],'consecutivo'=>$r[4],'fecha'=>'2026-10-23','estado'=>'Activo','descripcion'=>'Descuento','created_at'=>'2026-10-23 10:00:00']);
        }
        DB::table('compras_doc_registros')->insert([
            ['compras_doc_encabezado_id'=>1,'precio_total'=>-5918.18],
            ['compras_doc_encabezado_id'=>1,'precio_total'=>-7101.82],
        ]);
    }

    public function test_nombres_disponibles_y_total_completo()
    {
        $items = NotaCreditoValor::consultar_registros(20,'')->items();
        $this->assertCount(3,$items);
        $this->assertEquals('ANA PEREZ',$items[0]->campo3);
        $this->assertEquals('RAZON SOCIAL SAS',$items[1]->campo3);
        $this->assertEquals("SANTANA O'NEIL?",$items[2]->campo3);
        $this->assertEquals(-13020,$items[2]->campo6,'',0.00001);
    }

    public function test_busqueda_por_nombre_nit_documento_y_detalle_respeta_empresa_y_tipo()
    {
        foreach (['SANTANA','901234567','NCVC 213',"O'NEIL?",'13020'] as $search) {
            $items = NotaCreditoValor::consultar_registros(20,$search)->items();
            $this->assertCount(1,$items,$search);
            $this->assertEquals(1,$items[0]->campo8);
        }
        $this->assertEquals(3,NotaCreditoValor::consultar_registros(20,'Descuento')->total());
        $this->assertEquals(0,NotaCreditoValor::consultar_registros(20,'999')->total());
    }

    public function test_exportacion_coincide_y_escapa_apostrofes_y_signos_de_pregunta()
    {
        foreach (['', 'SANTANA', "O'NEIL?", "' OR 1=1 --"] as $search) {
            $export = DB::select(NotaCreditoValor::sqlString($search));
            $list = NotaCreditoValor::consultar_registros(20,$search)->items();
            $this->assertCount(count($list),$export);
            foreach ($export as $i=>$row) {
                $this->assertEquals($list[$i]->campo2,$row->DOCUMENTO_COMPRA);
                $this->assertEquals($list[$i]->campo3,$row->PROVEEDOR);
            }
        }
    }
}
