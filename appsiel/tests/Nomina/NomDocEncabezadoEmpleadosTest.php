<?php

use App\Nomina\NomDocEncabezado;
use App\Http\Controllers\Nomina\NominaController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NomDocEncabezadoEmpleadosTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config(['database.connections.nomina_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]]);
        DB::setDefaultConnection('nomina_testing');

        Schema::create('nom_parametros_legales', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('horas_laborales', 8, 3);
            $table->string('estado');
        });
        Schema::create('nom_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->integer('core_empresa_id');
            $table->decimal('tiempo_a_liquidar', 8, 3);
        });
        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->string('descripcion');
        });
        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id');
            $table->date('fecha_ingreso');
            $table->date('contrato_hasta')->nullable();
            $table->string('estado');
        });
        Schema::create('nom_empleados_del_documento', function (Blueprint $table) {
            $table->integer('orden');
            $table->integer('nom_doc_encabezado_id');
            $table->integer('nom_contrato_id');
        });
    }

    /** @test */
    public function solo_ofrece_contratos_vigentes_de_la_empresa_y_no_asignados()
    {
        DB::table('nom_parametros_legales')->insert([
            'fecha_inicio' => '2026-07-15',
            'fecha_fin' => '2026-12-31',
            'horas_laborales' => 210,
            'estado' => 'Activo'
        ]);
        DB::table('nom_doc_encabezados')->insert([
            'id' => 1,
            'fecha' => '2026-07-30',
            'core_empresa_id' => 1,
            'tiempo_a_liquidar' => 210
        ]);
        DB::table('core_terceros')->insert([
            ['id' => 1, 'core_empresa_id' => 1, 'descripcion' => 'Empleado empresa'],
            ['id' => 2, 'core_empresa_id' => 2, 'descripcion' => 'Empleado otra empresa']
        ]);
        DB::table('nom_contratos')->insert([
            ['id' => 1, 'core_tercero_id' => 1, 'fecha_ingreso' => '2026-07-01', 'contrato_hasta' => '2999-12-31', 'estado' => 'Activo'],
            ['id' => 2, 'core_tercero_id' => 1, 'fecha_ingreso' => '2026-06-01', 'contrato_hasta' => '2026-07-10', 'estado' => 'Activo'],
            ['id' => 3, 'core_tercero_id' => 1, 'fecha_ingreso' => '2026-08-01', 'contrato_hasta' => '2999-12-31', 'estado' => 'Activo'],
            ['id' => 4, 'core_tercero_id' => 2, 'fecha_ingreso' => '2026-07-01', 'contrato_hasta' => '2999-12-31', 'estado' => 'Activo'],
            ['id' => 5, 'core_tercero_id' => 1, 'fecha_ingreso' => '2026-07-01', 'contrato_hasta' => '2999-12-31', 'estado' => 'Retirado'],
            ['id' => 6, 'core_tercero_id' => 1, 'fecha_ingreso' => '2026-07-01', 'contrato_hasta' => '2999-12-31', 'estado' => 'Activo']
        ]);
        DB::table('nom_empleados_del_documento')->insert([
            'orden' => 1,
            'nom_doc_encabezado_id' => 1,
            'nom_contrato_id' => 6
        ]);

        $documento = NomDocEncabezado::find(1);
        $lapso = $documento->lapso();
        $ids = $documento->query_contratos_disponibles_para_asignar()->lists('nom_contratos.id');
        $ids = $ids instanceof \Illuminate\Support\Collection ? $ids->toArray() : (array)$ids;

        $this->assertSame('2026-07-01', $lapso->fecha_inicial);
        $this->assertSame('2026-07-30', $lapso->fecha_final);
        $this->assertEquals([1, 2], array_values($ids));
    }

    /** @test */
    public function el_endpoint_existente_despacha_el_retiro_masivo()
    {
        $request = Request::create('/nom_guardar_asignacion', 'POST', [
            'accion_masiva' => 'retirar_empleados',
            'nom_doc_encabezado_id' => 2296
        ]);
        $controller = new NominaControllerEmpleadosTestDouble;

        $this->assertSame('retirar:2296', $controller->guardar_asignacion($request));
    }
}

class NominaControllerEmpleadosTestDouble extends NominaController
{
    public function retirar_empleados_documento(Request $request, $nom_doc_encabezado_id)
    {
        return 'retirar:' . $nom_doc_encabezado_id;
    }
}
