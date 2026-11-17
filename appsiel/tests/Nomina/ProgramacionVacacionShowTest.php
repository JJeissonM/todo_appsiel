<?php

use App\Nomina\ProgramacionVacacion;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProgramacionVacacionShowTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.vacaciones_show_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'vacaciones_show_testing');
        DB::purge('vacaciones_show_testing');

        Schema::create('nom_libro_vacaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('novedad_tnl_id');
            $table->decimal('dias_pagados', 10, 2)->default(0);
            $table->decimal('dias_compensados', 10, 2)->default(0);
            $table->decimal('dias_no_habiles', 10, 2)->default(0);
        });
    }

    public function tearDown()
    {
        DB::purge('vacaciones_show_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function muestra_los_dias_del_libro_de_vacaciones_y_oculta_los_separadores()
    {
        DB::table('nom_libro_vacaciones')->insert([
            'novedad_tnl_id' => 1,
            'dias_pagados' => 15,
            'dias_compensados' => 3,
            'dias_no_habiles' => 2,
        ]);

        $campos = [
            ['name' => 'cantidad_dias_tomados', 'value' => null],
            ['name' => 'separador1', 'value' => null],
            ['name' => 'dias_compensados', 'value' => null],
            ['name' => 'separador2', 'value' => null],
            ['name' => 'dias_no_habiles', 'value' => null],
            ['name' => 'separador3', 'value' => null],
        ];

        $resultado = (new ProgramacionVacacion())->show_adicional($campos, (object)['id' => 1]);
        $porNombre = collect($resultado)->keyBy('name');

        $this->assertEquals(15, $porNombre['cantidad_dias_tomados']['value']);
        $this->assertEquals(3, $porNombre['dias_compensados']['value']);
        $this->assertEquals(2, $porNombre['dias_no_habiles']['value']);
        $this->assertFalse($porNombre->has('separador1'));
        $this->assertFalse($porNombre->has('separador2'));
        $this->assertFalse($porNombre->has('separador3'));
    }
}
