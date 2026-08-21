<?php

use App\Nomina\NomContrato;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NomContratoFiltrosTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config(['database.connections.nom_contrato_filtros_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]]);
        DB::setDefaultConnection('nom_contrato_filtros_testing');

        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->string('numero_identificacion');
            $table->string('descripcion');
        });
        Schema::create('nom_cargos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_grupos_empleados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_entidades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id');
            $table->integer('cargo_id')->nullable();
            $table->integer('grupo_empleado_id')->nullable();
            $table->integer('entidad_salud_id')->nullable();
            $table->integer('entidad_pension_id')->nullable();
            $table->integer('entidad_cesantias_id')->nullable();
            $table->string('clase_contrato');
            $table->decimal('sueldo', 15, 2);
            $table->date('fecha_ingreso');
            $table->date('contrato_hasta');
            $table->string('estado');
            $table->timestamps();
        });

        DB::table('core_terceros')->insert([
            ['id' => 10, 'core_empresa_id' => 1, 'numero_identificacion' => '1001', 'descripcion' => 'ANA EMPLEADA'],
            ['id' => 20, 'core_empresa_id' => 1, 'numero_identificacion' => '2002', 'descripcion' => 'BEATRIZ EMPLEADA']
        ]);
        DB::table('nom_contratos')->insert([
            $this->contrato(101, 10, 'Activo'),
            $this->contrato(102, 10, 'Inactivo'),
            $this->contrato(201, 20, 'Activo')
        ]);
    }

    /** @test */
    public function el_combobox_lista_una_opcion_por_tercero()
    {
        $filtros = NomContrato::get_filtros_avanzados_index();

        $this->assertArrayHasKey('filtro_tercero', $filtros);
        $this->assertSame('Tercero', $filtros['filtro_tercero']['label']);
        $this->assertSame('combobox', $filtros['filtro_tercero']['type']);
        $this->assertSame('ANA EMPLEADA (1001)', $filtros['filtro_tercero']['options'][10]);
        $this->assertCount(3, $filtros['filtro_tercero']['options']);
        $this->assertArrayNotHasKey(101, $filtros['filtro_tercero']['options']);
    }

    /** @test */
    public function al_filtrar_un_tercero_devuelve_todos_sus_contratos()
    {
        request()->replace(['filtro_tercero' => 10]);

        $registros = NomContrato::consultar_registros(10, '');

        $this->assertSame(2, $registros->total());
        $this->assertEquals([101, 102], $registros->pluck('campo9')->sort()->values()->all());
    }

    private function contrato($id, $terceroId, $estado)
    {
        return [
            'id' => $id,
            'core_tercero_id' => $terceroId,
            'clase_contrato' => 'normal',
            'sueldo' => 1000000,
            'fecha_ingreso' => '2026-01-01',
            'contrato_hasta' => '2026-12-31',
            'estado' => $estado,
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00'
        ];
    }
}
