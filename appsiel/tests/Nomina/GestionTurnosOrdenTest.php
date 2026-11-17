<?php

use App\Nomina\NomContrato;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GestionTurnosOrdenTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.turnos_orden_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'turnos_orden_testing');
        DB::purge('turnos_orden_testing');

        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });

        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('core_tercero_id');
            $table->string('estado');
            $table->string('clase_contrato');
        });
    }

    public function tearDown()
    {
        DB::purge('turnos_orden_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function ordena_alfabeticamente_los_empleados_activos_con_contrato_por_turnos()
    {
        DB::table('core_terceros')->insert([
            ['id' => 1, 'descripcion' => 'SINDY RAMOS'],
            ['id' => 2, 'descripcion' => 'JOELVIS GARCES'],
            ['id' => 3, 'descripcion' => 'MANUEL GASPAR'],
            ['id' => 4, 'descripcion' => 'ALVARO RETIRADO'],
            ['id' => 5, 'descripcion' => 'CARLOS FIJO'],
        ]);

        DB::table('nom_contratos')->insert([
            ['id' => 11, 'core_tercero_id' => 1, 'estado' => 'Activo', 'clase_contrato' => 'por_turnos'],
            ['id' => 12, 'core_tercero_id' => 2, 'estado' => 'Activo', 'clase_contrato' => 'por_turnos'],
            ['id' => 13, 'core_tercero_id' => 3, 'estado' => 'Activo', 'clase_contrato' => 'por_turnos'],
            ['id' => 14, 'core_tercero_id' => 4, 'estado' => 'Retirado', 'clase_contrato' => 'por_turnos'],
            ['id' => 15, 'core_tercero_id' => 5, 'estado' => 'Activo', 'clase_contrato' => 'termino_fijo'],
        ]);

        $empleados = NomContrato::activosPorTurnosOrdenadosPorEmpleado();

        $this->assertSame(
            ['JOELVIS GARCES', 'MANUEL GASPAR', 'SINDY RAMOS'],
            $empleados->map(function ($contrato) {
                return $contrato->tercero->descripcion;
            })->all()
        );
    }
}
