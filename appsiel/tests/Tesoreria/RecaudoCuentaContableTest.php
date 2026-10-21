<?php

use App\Http\Controllers\Tesoreria\RecaudoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecaudoCuentaContableTest extends TestCase
{
    private $conexionOriginal;

    protected function setUp()
    {
        parent::setUp();
        $this->conexionOriginal = DB::getDefaultConnection();
        config(['database.connections.recaudo_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('recaudo_test');
        DB::statement('CREATE TABLE teso_motivos (id INTEGER, descripcion TEXT, contab_cuenta_id INTEGER)');
        DB::statement('CREATE TABLE contab_cuentas (id INTEGER)');
        DB::statement('CREATE TABLE recaudos_prueba (id INTEGER)');
        DB::statement("INSERT INTO teso_motivos VALUES (23, 'CONSIGNACIONES', 0)");
    }

    protected function tearDown()
    {
        DB::purge('recaudo_test');
        DB::setDefaultConnection($this->conexionOriginal);
        parent::tearDown();
    }

    private function controlador()
    {
        return new class extends RecaudoController {
            protected function validar_tercero(Request $request) {}
            protected function validar_punto_venta(Request $request) {}
            protected function guardar_recaudo(Request $request)
            {
                DB::table('recaudos_prueba')->insert(['id' => 1]);
                throw new RuntimeException('Fallo al contabilizar');
            }
        };
    }

    private function solicitud()
    {
        return new Request(['lineas_registros_medios_recaudo' => json_encode([
            ['teso_motivo_id' => '23-CONSIGNACIONES'],
            ['valor' => '$100']
        ])]);
    }

    public function test_motivo_sin_cuenta_regresa_sin_guardar()
    {
        $response = $this->controlador()->store($this->solicitud());
        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        $this->assertContains('CONSIGNACIONES', session('mensaje_error'));
        $this->assertSame(0, DB::table('recaudos_prueba')->count());
    }

    public function test_error_de_contabilizacion_revierte_lo_guardado()
    {
        DB::table('contab_cuentas')->insert(['id' => 10]);
        DB::table('teso_motivos')->where('id', 23)->update(['contab_cuenta_id' => 10]);
        try {
            $this->controlador()->store($this->solicitud());
            $this->fail('Se esperaba un error de contabilización.');
        } catch (RuntimeException $e) {
            $this->assertSame('Fallo al contabilizar', $e->getMessage());
        }
        $this->assertSame(0, DB::table('recaudos_prueba')->count());
    }
}
