<?php

use App\VentasPos\FacturaPos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosInvoiceListingTest extends TestCase
{
    private $previous;
    protected function setUp()
    {
        parent::setUp();
        $this->previous = DB::getDefaultConnection();
        config(['database.connections.pos_listing_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('pos_listing_test');
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', function () { return implode('', func_get_args()); });
        Auth::shouldReceive('user')->andReturn((object)['empresa_id'=>1, 'email'=>'caja@test', 'roles'=>[]]);
        foreach ([
            'vtas_pos_doc_encabezados'=>'id core_empresa_id core_tipo_transaccion_id core_tipo_doc_app_id core_tercero_id pdv_id fecha consecutivo forma_pago descripcion valor_total valor_ajuste_al_peso valor_total_bolsas estado creado_por created_at',
            'core_tipos_docs_apps'=>'id prefijo', 'core_terceros'=>'id descripcion numero_identificacion',
            'vtas_pos_puntos_de_ventas'=>'id descripcion'
        ] as $table=>$columns) {
            DB::statement('CREATE TABLE '.$table.' ('.implode(', ', array_map(function ($c) { return $c.' TEXT'; }, explode(' ', $columns))).')');
        }
        DB::table('core_tipos_docs_apps')->insert(['id'=>1,'prefijo'=>'FE']);
        foreach ([[1,47,1,'caja@test'],[2,52,1,'caja@test'],[3,55,1,'caja@test'],[4,52,2,'caja@test'],[5,52,1,'otro@test'],[6,53,1,'caja@test']] as $row) {
            DB::table('vtas_pos_doc_encabezados')->insert([
                'id'=>$row[0],'core_tipo_transaccion_id'=>$row[1],'core_empresa_id'=>$row[2],
                'creado_por'=>$row[3],'core_tipo_doc_app_id'=>1,'consecutivo'=>$row[0],
                'valor_total'=>100,'created_at'=>'2026-10-07 10:00:00'
            ]);
        }
    }
    protected function tearDown()
    {
        DB::purge('pos_listing_test'); DB::setDefaultConnection($this->previous);
        parent::tearDown();
    }
    public function test_incluye_convertidas_sin_duplicar_y_respeta_empresa_usuario_y_busqueda()
    {
        foreach (['consultar_registros','consultar_registros2'] as $method) {
            $rows = FacturaPos::$method(20, '');
            $ids = array_map(function ($row) { return (int)$row->campo9; }, $rows->items());
            sort($ids);
            $this->assertSame([1,2,3], $ids);
            $filtered = FacturaPos::$method(20, 'FE 2');
            $this->assertSame(1, $filtered->total());
        }
        $this->assertContains('52', FacturaPos::sqlString(''));
    }
}
