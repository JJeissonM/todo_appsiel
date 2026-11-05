<?php

use App\Nomina\Services\CalculoFechaVacaciones;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalculoFechaVacacionesTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.vacaciones_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'vacaciones_testing');
        DB::purge('vacaciones_testing');

        $this->crearEsquema();
    }

    public function tearDown()
    {
        DB::purge('vacaciones_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function usa_el_parametro_del_grupo_y_cuenta_el_sabado_como_habil()
    {
        $this->crearParametro(1);

        $resultado = (new CalculoFechaVacaciones())->calcular(1, '2026-11-17', 12, 0);

        $this->assertSame('2026-11-30', $resultado['fecha_fin']);
        $this->assertSame(2, $resultado['dias_no_habiles']);
        $this->assertSame(1, $resultado['sabado_es_dia_habil']);
    }

    /** @test */
    public function excluye_los_sabados_cuando_el_grupo_los_configura_como_no_habiles()
    {
        $this->crearParametro(0);

        $resultado = (new CalculoFechaVacaciones())->calcular(1, '2026-11-17', 12, 0);

        $this->assertSame('2026-12-02', $resultado['fecha_fin']);
        $this->assertSame(4, $resultado['dias_no_habiles']);
        $this->assertSame(0, $resultado['sabado_es_dia_habil']);
    }

    /** @test */
    public function excluye_festivos_sin_contar_dos_veces_un_festivo_en_domingo()
    {
        $this->crearParametro(1);
        DB::table('nom_dias_festivos')->insert([
            ['fecha' => '2026-11-21', 'observacion' => 'Festivo en sábado'],
            ['fecha' => '2026-11-22', 'observacion' => 'Festivo en domingo'],
        ]);

        $resultado = (new CalculoFechaVacaciones())->calcular(1, '2026-11-17', 12, 0);

        $this->assertSame('2026-12-01', $resultado['fecha_fin']);
        $this->assertSame(3, $resultado['dias_no_habiles']);
    }

    /** @test */
    public function descuenta_los_dias_compensados_del_periodo_disfrutado()
    {
        $this->crearParametro(1);

        $resultado = (new CalculoFechaVacaciones())->calcular(1, '2026-11-17', 12, 2);

        $this->assertSame('2026-11-27', $resultado['fecha_fin']);
        $this->assertSame(1, $resultado['dias_no_habiles']);
    }

    /** @test */
    public function exige_parametrizacion_de_vacaciones_para_el_grupo()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'No existe parametrización de vacaciones para el grupo de empleados seleccionado.'
        );

        (new CalculoFechaVacaciones())->calcular(99, '2026-11-17', 12, 0);
    }

    protected function crearParametro($sabadoEsDiaHabil)
    {
        DB::table('nom_parametros_liquidacion_prestaciones_sociales')->insert([
            'concepto_prestacion' => 'vacaciones',
            'grupo_empleado_id' => 1,
            'sabado_es_dia_habil' => $sabadoEsDiaHabil,
        ]);
    }

    protected function crearEsquema()
    {
        Schema::create('nom_parametros_liquidacion_prestaciones_sociales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('concepto_prestacion');
            $table->integer('grupo_empleado_id');
            $table->boolean('sabado_es_dia_habil')->default(0);
        });

        Schema::create('nom_dias_festivos', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->string('observacion')->nullable();
        });
    }
}
