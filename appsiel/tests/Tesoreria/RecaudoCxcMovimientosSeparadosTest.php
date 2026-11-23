<?php

use App\Contabilidad\ContabMovimiento;
use App\CxC\CxcAbono;
use App\CxC\CxcMovimiento;
use App\CxC\Services\CxcAccountingAccountResolver;
use App\Tesoreria\TesoDocEncabezadoRecaudoCxc;
use App\Tesoreria\Services\AccountsReceivableServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecaudoCxcMovimientosSeparadosTest extends TestCase
{
    private $conexion;

    public function setUp()
    {
        parent::setUp();
        $this->conexion = DB::getDefaultConnection();
        config(['database.connections.cxc_separados'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('cxc_separados');
        $user = new App\User;$user->id=1;$user->empresa_id=1;$user->email='prueba@example.test';Auth::setUser($user);
        foreach ([new CxcMovimiento, new CxcAbono, new ContabMovimiento] as $modelo) {
            $campos = $modelo->getFillable();
            Schema::create($modelo->getTable(), function ($tabla) use ($campos) {
                $tabla->increments('id');
                foreach ($campos as $campo) {
                    if (in_array($campo,['valor_documento','valor_pagado','saldo_pendiente','valor_debito','valor_credito','valor_saldo','abono'])) {
                        $tabla->double($campo)->nullable();
                    } else {
                        $tabla->string($campo)->nullable();
                    }
                }
                $tabla->timestamps();
            });
        }
        foreach ([[1,9900000,101],[2,100000,102]] as $datos) {
            DB::table('cxc_movimientos')->insert(['id'=>$datos[0],'core_empresa_id'=>1,'core_tercero_id'=>10,
                'core_tipo_transaccion_id'=>9,'core_tipo_doc_app_id'=>15,'consecutivo'=>3,'contab_cuenta_id'=>$datos[2],
                'valor_documento'=>$datos[1],'valor_pagado'=>0,'saldo_pendiente'=>$datos[1],'estado'=>'Pendiente']);
            DB::table('contab_movimientos')->insert(['core_empresa_id'=>1,'core_tercero_id'=>10,
                'core_tipo_transaccion_id'=>9,'core_tipo_doc_app_id'=>15,'consecutivo'=>3,'contab_cuenta_id'=>$datos[2],
                'valor_debito'=>$datos[1],'valor_credito'=>0,'valor_saldo'=>$datos[1],'tipo_transaccion'=>'crear_cxc']);
        }
    }

    public function tearDown()
    {
        DB::purge('cxc_separados');DB::setDefaultConnection($this->conexion);
        parent::tearDown();
    }

    private function recibo()
    {
        $doc = new TesoDocEncabezadoRecaudoCxc;
        $doc->core_empresa_id=1;$doc->core_tercero_id=10;$doc->core_tipo_transaccion_id=32;
        $doc->core_tipo_doc_app_id=3;$doc->consecutivo=4;$doc->fecha='2026-11-23';$doc->creado_por='prueba@example.test';
        $doc->setRelation('tipo_transaccion',(object)['descripcion'=>'Recaudo']);
        $doc->setRelation('tipo_documento_app',(object)['prefijo'=>'RC']);
        return $doc;
    }

    private function lineas($segunda=4950000)
    {
        return json_encode([['id_doc'=>2,'abono'=>50000],['id_doc'=>1,'abono'=>$segunda],['abono'=>50000+$segunda]]);
    }

    private function comprobarAbonos()
    {
        $this->assertEquals(50000,CxcMovimiento::find(2)->saldo_pendiente);
        $this->assertEquals(4950000,CxcMovimiento::find(1)->saldo_pendiente);
        $this->assertSame('Pendiente',CxcMovimiento::find(2)->estado);
        $this->assertEquals(50000,CxcMovimiento::find(2)->valor_pagado);
        $this->assertEquals(4950000,CxcMovimiento::find(1)->valor_pagado);
        $creditos=ContabMovimiento::where('core_tipo_transaccion_id',32)->pluck('valor_credito','contab_cuenta_id')->all();
        $this->assertEquals(-50000,$creditos[102]);
        $this->assertEquals(-4950000,$creditos[101]);
        foreach(CxcAbono::all() as $abono) {
            $this->assertEquals($abono->cxc_movimiento_id,$abono->getMovimientoCxc()->id);
        }
    }

    public function testRecaudoAplicaCadaAbonoALaCuentaYMovimientoSeleccionados()
    {
        $doc=$this->recibo();
        $total=$doc->almacenar_y_contabilizar_abonos_cxc(new Request(array_merge($doc->toArray(),['lineas_registros'=>$this->lineas(), 'contab_cuenta_id'=>999])));
        $this->assertEquals(5000000,$total);
        $this->comprobarAbonos();
    }

    public function testMigracionAgregaReferenciasSinModificarMovimientosHistoricos()
    {
        Schema::drop('cxc_abonos');
        Schema::drop('cxc_movimientos');
        Schema::create('cxc_abonos',function ($tabla) { $tabla->increments('id'); });
        Schema::create('cxc_movimientos',function ($tabla) { $tabla->increments('id'); });
        DB::table('cxc_abonos')->insert(['id'=>1]);
        DB::table('cxc_movimientos')->insert(['id'=>1]);
        require_once database_path('migrations/2026_11_23_000001_link_cxc_payments_to_source_movement.php');
        $migracion = new LinkCxcPaymentsToSourceMovement;
        $migracion->up();
        $migracion->up();
        $this->assertTrue(Schema::hasColumn('cxc_abonos','cxc_movimiento_id'));
        $this->assertTrue(Schema::hasColumn('cxc_movimientos','contab_cuenta_id'));
        $this->assertNull(DB::table('cxc_abonos')->where('id',1)->value('cxc_movimiento_id'));
        $this->assertNull(DB::table('cxc_movimientos')->where('id',1)->value('contab_cuenta_id'));
    }

    public function testRechazaUnaCuentaHistoricaAmbiguaEnLugarDeElegirLaPrimera()
    {
        $movimiento = CxcMovimiento::find(2);
        $movimiento->contab_cuenta_id = null;
        ContabMovimiento::where('contab_cuenta_id',101)->update(['valor_debito'=>100000]);
        $this->assertNull((new CxcAccountingAccountResolver)->getReceivableAccountId($movimiento));
    }

    public function testRechazaSeleccionDuplicadaSinDejarAbonosParciales()
    {
        $doc = $this->recibo();
        $lineas = json_encode([['id_doc'=>2,'abono'=>10000],['id_doc'=>2,'abono'=>10000],['abono'=>20000]]);
        try {
            $doc->almacenar_y_contabilizar_abonos_cxc(new Request(['lineas_registros'=>$lineas]));
            $this->fail('Se acepto un movimiento repetido.');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals(0,CxcAbono::count());
            $this->assertEquals(100000,CxcMovimiento::find(2)->saldo_pendiente);
        }
    }

    public function testFlujoAlternativoTambienConservaLaIdentidadDelMovimiento()
    {
        (new AccountsReceivableServices)->create_record_payment_accounts_receivable($this->recibo(),$this->lineas());
        $this->comprobarAbonos();
    }

    public function testUnSobrepagoRevierteTambienElPrimerAbonoDelRecaudo()
    {
        try {
            $this->recibo()->almacenar_y_contabilizar_abonos_cxc(new Request(['lineas_registros'=>$this->lineas(10000000)]));
            $this->fail('Se esperaba rechazar el sobrepago.');
        } catch (InvalidArgumentException $e) {
            $this->assertContains('supera el saldo',$e->getMessage());
        }
        $this->assertEquals(0,CxcAbono::count());
        $this->assertEquals(0,CxcMovimiento::find(2)->valor_pagado);
        $this->assertEquals(0,ContabMovimiento::where('core_tipo_transaccion_id',32)->count());
    }

    public function testNoAceptaMovimientosDeOtraEmpresaNiTercero()
    {
        foreach (['core_empresa_id'=>2,'core_tercero_id'=>20] as $campo=>$valor) {
            $original=CxcMovimiento::find(2)->$campo;
            DB::table('cxc_movimientos')->where('id',2)->update([$campo=>$valor]);
            try {
                $this->recibo()->almacenar_y_contabilizar_abonos_cxc(new Request(['lineas_registros'=>$this->lineas()]));
                $this->fail('Debio rechazar el movimiento ajeno.');
            } catch (InvalidArgumentException $e) {
                $this->assertEquals(0,CxcAbono::count());
            }
            DB::table('cxc_movimientos')->where('id',2)->update([$campo=>$original]);
        }
    }

    public function testResolverHistoricoDistingueLasCuentasPorElImporteDeOrigen()
    {
        DB::table('cxc_movimientos')->update(['contab_cuenta_id'=>null]);
        $resolver=new CxcAccountingAccountResolver;
        $this->assertSame(101,$resolver->getReceivableAccountId(CxcMovimiento::find(1)));
        $this->assertSame(102,$resolver->getReceivableAccountId(CxcMovimiento::find(2)));
    }

    public function testReversionRestituyeLosDosSaldosIndividuales()
    {
        $this->recibo()->almacenar_y_contabilizar_abonos_cxc(new Request(['lineas_registros'=>$this->lineas()]));
        foreach(CxcAbono::all() as $abono) {
            $abono->getMovimientoCxc(true)->actualizar_saldos(-$abono->abono);
        }
        $this->assertEquals(9900000,CxcMovimiento::find(1)->saldo_pendiente);
        $this->assertEquals(100000,CxcMovimiento::find(2)->saldo_pendiente);
    }

    public function testNoAdivinaElMovimientoDeUnAbonoHistoricoAmbiguo()
    {
        $abono=new CxcAbono(['core_empresa_id'=>1,'core_tercero_id'=>10,
            'doc_cxc_transacc_id'=>9,'doc_cxc_tipo_doc_id'=>15,'doc_cxc_consecutivo'=>3]);
        $this->assertNull($abono->getMovimientoCxc());
        $abono->cxc_movimiento_id=2;
        $this->assertEquals(2,$abono->getMovimientoCxc()->id);
    }
}
