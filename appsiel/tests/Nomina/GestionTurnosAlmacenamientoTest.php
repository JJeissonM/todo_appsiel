<?php

use App\Http\Controllers\Nomina\GestionTurnosController;
use App\Nomina\NomContrato;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GestionTurnosAlmacenamientoTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.turnos_almacenamiento_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'turnos_almacenamiento_testing');
        DB::purge('turnos_almacenamiento_testing');

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

        Schema::create('nom_turnos_tipos', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('valor', 15, 2)->default(0);
        });

        Schema::create('nom_turnos_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('contrato_id');
            $table->unsignedInteger('tipo_turno_id');
            $table->date('fecha');
            $table->decimal('valor', 15, 2)->default(0);
            $table->text('anotacion')->nullable();
            $table->string('estado');
            $table->timestamps();
        });

        DB::table('core_terceros')->insert([
            ['id' => 1, 'descripcion' => 'ZULMA EMPLEADA'],
            ['id' => 2, 'descripcion' => 'ANA EMPLEADA'],
        ]);
        DB::table('nom_contratos')->insert([
            ['id' => 10, 'core_tercero_id' => 1, 'estado' => 'Activo', 'clase_contrato' => 'por_turnos'],
            ['id' => 20, 'core_tercero_id' => 2, 'estado' => 'Activo', 'clase_contrato' => 'por_turnos'],
        ]);
        DB::table('nom_turnos_tipos')->insert([
            ['id' => 101, 'valor' => 40000],
            ['id' => 102, 'valor' => 50000],
        ]);
    }

    public function tearDown()
    {
        DB::purge('turnos_almacenamiento_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function conserva_la_asociacion_de_cada_fila_al_crear_y_actualizar_turnos_ordenados()
    {
        $contratosOrdenados = NomContrato::activosPorTurnosOrdenadosPorEmpleado();
        $idsOrdenados = $contratosOrdenados->map(function ($contrato) {
            return $contrato->id;
        })->all();

        $this->assertSame([20, 10], $idsOrdenados);

        $this->guardar([
            'contrato_id' => $idsOrdenados,
            'tipo_turno_id' => [101, 102],
            'anotacion' => ['Turno de Ana', 'Turno de Zulma'],
        ]);

        $ana = DB::table('nom_turnos_registros')->where('contrato_id', 20)->first();
        $zulma = DB::table('nom_turnos_registros')->where('contrato_id', 10)->first();

        $this->assertEquals(101, $ana->tipo_turno_id);
        $this->assertEquals(40000, $ana->valor);
        $this->assertSame('Turno de Ana', $ana->anotacion);
        $this->assertEquals(102, $zulma->tipo_turno_id);
        $this->assertEquals(50000, $zulma->valor);
        $this->assertSame('Turno de Zulma', $zulma->anotacion);

        DB::table('nom_turnos_registros')->where('contrato_id', 20)->update(['estado' => 'Liquidado']);

        $this->guardar([
            'contrato_id' => $idsOrdenados,
            'tipo_turno_id' => [102, 101],
            'anotacion' => ['No debe cambiar', 'Zulma actualizada'],
        ]);

        $ana = DB::table('nom_turnos_registros')->where('contrato_id', 20)->first();
        $zulma = DB::table('nom_turnos_registros')->where('contrato_id', 10)->first();

        $this->assertEquals(101, $ana->tipo_turno_id);
        $this->assertEquals(40000, $ana->valor);
        $this->assertSame('Turno de Ana', $ana->anotacion);
        $this->assertSame('Liquidado', $ana->estado);
        $this->assertEquals(101, $zulma->tipo_turno_id);
        $this->assertEquals(40000, $zulma->valor);
        $this->assertSame('Zulma actualizada', $zulma->anotacion);
        $this->assertSame('Pendiente', $zulma->estado);
    }

    protected function guardar(array $filas)
    {
        $request = Request::create('/nom_turnos_registros', 'POST', $filas + [
            'fecha' => '2026-11-17',
            'app_id' => 17,
            'modelo_id' => 337,
        ]);

        (new GestionTurnosController())->store($request);
    }
}
