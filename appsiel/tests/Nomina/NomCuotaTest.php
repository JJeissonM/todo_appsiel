<?php

use App\Nomina\NomCuota;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NomCuotaTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config(['database.connections.nom_cuota_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]]);
        DB::setDefaultConnection('nom_cuota_testing');

        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->string('descripcion');
        });
        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_cuotas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id');
            $table->integer('nom_concepto_id');
            $table->date('fecha_inicio');
            $table->string('estado');
        });
    }

    /** @test */
    public function configura_los_filtros_avanzados_del_index()
    {
        DB::table('core_terceros')->insert([
            ['id' => 1, 'core_empresa_id' => 1, 'descripcion' => 'ANA EMPLEADA'],
            ['id' => 2, 'core_empresa_id' => 1, 'descripcion' => 'BEATRIZ EMPLEADA']
        ]);
        DB::table('nom_conceptos')->insert([
            ['id' => 10, 'descripcion' => 'LIBRANZA'],
            ['id' => 11, 'descripcion' => 'EMBARGO']
        ]);
        DB::table('nom_cuotas')->insert([
            ['core_tercero_id' => 1, 'nom_concepto_id' => 10, 'fecha_inicio' => '2026-08-01', 'estado' => 'Activo'],
            ['core_tercero_id' => 2, 'nom_concepto_id' => 11, 'fecha_inicio' => '2026-08-02', 'estado' => 'Inactivo']
        ]);

        $filtros = NomCuota::get_filtros_avanzados_index();

        $this->assertSame(
            ['filtro_empleado', 'filtro_concepto', 'filtro_fecha_inicio', 'filtro_estado'],
            array_keys($filtros)
        );
        $this->assertSame('combobox', $filtros['filtro_empleado']['type']);
        $this->assertSame('ANA EMPLEADA', $filtros['filtro_empleado']['options'][1]);
        $this->assertSame('LIBRANZA', $filtros['filtro_concepto']['options'][10]);
        $this->assertSame('date', $filtros['filtro_fecha_inicio']['type']);
        $this->assertSame('Activo', $filtros['filtro_estado']['options']['Activo']);
    }
}
