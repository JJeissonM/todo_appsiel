<?php

use App\Compras\Proveedor;
use App\Core\Tercero;
use App\Ventas\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TerceroClienteProveedorTest extends TestCase
{
    private $conexionOriginal;

    public function setUp()
    {
        parent::setUp();
        $this->conexionOriginal = DB::getDefaultConnection();
        config(['database.connections.tercero_roles_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('tercero_roles_test');
        config(['contabilidad.crear_cliente_y_proveedor_al_crear_tercero'=>'1']);
        foreach ([new Cliente, new Proveedor] as $modelo) {
            $campos = $modelo->getFillable();
            Schema::create($modelo->getTable(), function ($tabla) use ($campos) {
                $tabla->increments('id');
                foreach ($campos as $campo) {
                    $tabla->string($campo)->nullable();
                }
                $tabla->timestamps();
            });
        }
    }

    public function tearDown()
    {
        DB::purge('tercero_roles_test');
        DB::setDefaultConnection($this->conexionOriginal);
        parent::tearDown();
    }

    private function guardarAdicional($id=123)
    {
        $tercero = new Tercero;
        $tercero->id = $id;
        $tercero->store_adicional([], $tercero);
    }

    public function testCreaClienteYProveedorCuandoLaConfiguracionEstaActiva()
    {
        $this->guardarAdicional();
        $this->assertEquals(1, Cliente::where('core_tercero_id',123)->count());
        $this->assertEquals(1, Proveedor::where('core_tercero_id',123)->count());
        $this->assertEquals(config('ventas.lista_precios_id'), Cliente::first()->lista_precios_id);
        $this->assertSame('Activo', Proveedor::first()->estado);
    }

    public function testNoCreaRolesSiLaConfiguracionEstaDesactivada()
    {
        config(['contabilidad.crear_cliente_y_proveedor_al_crear_tercero'=>'0']);
        $this->guardarAdicional();
        $this->assertEquals(0, Cliente::count());
        $this->assertEquals(0, Proveedor::count());
    }

    public function testActualizarNoDuplicaNiReemplazaLaConfiguracionExistente()
    {
        $this->guardarAdicional();
        Cliente::where('core_tercero_id',123)->update(['cupo_credito'=>5000]);
        Proveedor::where('core_tercero_id',123)->update(['codigo'=>'EXISTENTE']);
        (new Tercero)->update_adicional([],123);
        $this->assertEquals(1, Cliente::count());
        $this->assertEquals(1, Proveedor::count());
        $this->assertEquals(5000, Cliente::first()->cupo_credito);
        $this->assertSame('EXISTENTE', Proveedor::first()->codigo);
    }

    public function testSiSoloExisteLaTablaDeClientesCreaSoloElCliente()
    {
        Schema::drop('compras_proveedores');
        $this->guardarAdicional();
        $this->assertEquals(1, Cliente::count());
    }

    public function testSiSoloExisteLaTablaDeProveedoresCreaSoloElProveedor()
    {
        Schema::drop('vtas_clientes');
        $this->guardarAdicional();
        $this->assertEquals(1, Proveedor::count());
    }

    public function testPuedeCrearUnTerceroSinLosModulosDeVentasYCompras()
    {
        Schema::drop('vtas_clientes');
        Schema::drop('compras_proveedores');
        $this->guardarAdicional();
        $this->assertFalse(Schema::hasTable('vtas_clientes'));
        $this->assertFalse(Schema::hasTable('compras_proveedores'));
    }
}
