<?php

use App\Nomina\NomContrato;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalarioHistoricoContratoTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.salario_historico_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'salario_historico_testing');
        DB::purge('salario_historico_testing');

        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('sueldo', 15, 2);
        });
        Schema::create('nom_cambios_salarios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_contrato_id');
            $table->decimal('salario_anterior', 15, 2);
            $table->decimal('nuevo_salario', 15, 2);
            $table->date('fecha_modificacion');
        });
    }

    public function tearDown()
    {
        DB::purge('salario_historico_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function obtiene_el_salario_que_estaba_vigente_en_la_fecha_de_compensacion()
    {
        DB::table('nom_contratos')->insert(['id' => 1, 'sueldo' => 3000000]);
        DB::table('nom_cambios_salarios')->insert([
            [
                'nom_contrato_id' => 1,
                'salario_anterior' => 2000000,
                'nuevo_salario' => 2500000,
                'fecha_modificacion' => '2026-01-01',
            ],
            [
                'nom_contrato_id' => 1,
                'salario_anterior' => 2500000,
                'nuevo_salario' => 3000000,
                'fecha_modificacion' => '2026-07-01',
            ],
        ]);

        $contrato = NomContrato::find(1);

        $this->assertSame(2000000.0, $contrato->salario_para_fecha('2025-12-31'));
        $this->assertSame(2500000.0, $contrato->salario_para_fecha('2026-06-30'));
        $this->assertSame(3000000.0, $contrato->salario_para_fecha('2026-07-01'));
    }
}
