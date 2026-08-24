<?php

use App\Http\Controllers\Nomina\RegistrosDocumentosController;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegistrosDocumentosFiltrosTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config(['database.connections.registros_filtros_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]]);
        DB::setDefaultConnection('registros_filtros_testing');

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
            $table->string('descripcion');
            $table->decimal('total_devengos', 15, 2)->default(0);
            $table->decimal('total_deducciones', 15, 2)->default(0);
            $table->string('estado');
            $table->timestamps();
        });
        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->string('numero_identificacion');
            $table->string('descripcion');
        });
        Schema::create('nom_grupos_empleados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_cargos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id');
            $table->integer('grupo_empleado_id');
            $table->integer('cargo_id');
            $table->decimal('sueldo', 15, 2);
            $table->string('estado');
            $table->timestamps();
        });
        Schema::create('nom_empleados_del_documento', function (Blueprint $table) {
            $table->integer('nom_doc_encabezado_id');
            $table->integer('nom_contrato_id');
        });
        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('modo_liquidacion_id');
            $table->integer('cpto_dian_id')->nullable();
            $table->string('naturaleza');
            $table->decimal('porcentaje_sobre_basico', 8, 4)->default(0);
            $table->string('descripcion');
            $table->string('estado');
        });
        Schema::create('nom_elect_cat_cptos_dian', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
        });
        Schema::create('nom_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_doc_encabezado_id');
            $table->integer('core_tercero_id');
            $table->integer('nom_contrato_id');
            $table->date('fecha');
            $table->integer('core_empresa_id');
            $table->integer('nom_concepto_id');
            $table->decimal('cantidad_horas', 10, 2)->default(0);
            $table->decimal('valor_devengo', 15, 2)->default(0);
            $table->decimal('valor_deduccion', 15, 2)->default(0);
            $table->string('estado');
            $table->string('creado_por');
            $table->string('modificado_por')->nullable();
            $table->timestamps();
        });

        DB::table('nom_parametros_legales')->insert([
            'fecha_inicio' => '2026-01-01', 'fecha_fin' => null,
            'horas_laborales' => 210, 'estado' => 'Activo'
        ]);
        DB::table('nom_doc_encabezados')->insert([
            'id' => 1, 'fecha' => '2026-08-30', 'core_empresa_id' => 1,
            'tiempo_a_liquidar' => 210, 'descripcion' => 'NOMINA AGOSTO',
            'estado' => 'Activo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        DB::table('core_terceros')->insert([
            ['id' => 1, 'core_empresa_id' => 1, 'numero_identificacion' => '1001', 'descripcion' => 'ANA'],
            ['id' => 2, 'core_empresa_id' => 1, 'numero_identificacion' => '1002', 'descripcion' => 'BEATRIZ']
        ]);
        DB::table('nom_grupos_empleados')->insert([
            ['id' => 10, 'descripcion' => 'ADMINISTRATIVOS'],
            ['id' => 20, 'descripcion' => 'DOCENTES']
        ]);
        DB::table('nom_cargos')->insert([
            ['id' => 100, 'descripcion' => 'AUXILIAR'],
            ['id' => 200, 'descripcion' => 'PROFESOR']
        ]);
        DB::table('nom_contratos')->insert([
            ['id' => 101, 'core_tercero_id' => 1, 'grupo_empleado_id' => 10, 'cargo_id' => 100, 'sueldo' => 2000000, 'estado' => 'Activo', 'created_at' => null, 'updated_at' => null],
            ['id' => 202, 'core_tercero_id' => 2, 'grupo_empleado_id' => 20, 'cargo_id' => 200, 'sueldo' => 3000000, 'estado' => 'Activo', 'created_at' => null, 'updated_at' => null]
        ]);
        DB::table('nom_empleados_del_documento')->insert([
            ['nom_doc_encabezado_id' => 1, 'nom_contrato_id' => 101],
            ['nom_doc_encabezado_id' => 1, 'nom_contrato_id' => 202]
        ]);
        DB::table('nom_conceptos')->insert([
            'id' => 5, 'modo_liquidacion_id' => 2, 'naturaleza' => 'devengo',
            'porcentaje_sobre_basico' => 0, 'descripcion' => 'BONIFICACION', 'estado' => 'Activo'
        ]);

        $usuario = new User(['empresa_id' => 1, 'email' => 'pruebas@appsiel.test', 'name' => 'Pruebas']);
        $usuario->id = 99;
        Auth::setUser($usuario);
    }

    /** @test */
    public function filtra_los_contratos_asignados_por_grupo_cargo_y_empleado()
    {
        $documento = NomDocEncabezado::find(1);

        $this->assertEquals([101], $documento->contratos_asignados_para_registros(10)->pluck('id')->toArray());
        $this->assertEquals([202], $documento->contratos_asignados_para_registros(null, 200)->pluck('id')->toArray());
        $this->assertEquals([101], $documento->contratos_asignados_para_registros(10, 100, 101)->pluck('id')->toArray());
        $this->assertTrue($documento->contratos_asignados_para_registros(10, 100, 202)->isEmpty());
    }

    /** @test */
    public function el_endpoint_entrega_solo_opciones_asignadas_al_documento()
    {
        $respuesta = (new RegistrosDocumentosController)->filtros_registros(1);
        $datos = json_decode($respuesta->getContent(), true);

        $this->assertSame([101, 202], array_column($datos['empleados'], 'id'));
        $this->assertSame([10, 20], array_column($datos['grupos'], 'id'));
        $this->assertSame([100, 200], array_column($datos['cargos'], 'id'));
    }

    /** @test */
    public function el_filtro_de_empleados_respeta_el_documento_y_el_concepto_seleccionados()
    {
        DB::table('nom_doc_encabezados')->insert([
            'id' => 2, 'fecha' => '2026-07-30', 'core_empresa_id' => 1,
            'tiempo_a_liquidar' => 210, 'descripcion' => 'NOMINA JULIO',
            'estado' => 'Activo', 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->insertarRegistroNomina(50, 1, 1, 101, 5);
        $this->insertarRegistroNomina(51, 2, 2, 202, 5);

        request()->replace(['filtro_documento' => 1, 'filtro_concepto' => 5]);
        $opciones = NomDocRegistro::get_filtros_avanzados_index()['filtro_empleado']['options'];

        $this->assertSame(['' => 'Todos', 1 => 'ANA'], $opciones);
    }

    /** @test */
    public function un_empleado_retirado_no_reaparece_al_volver_a_filtrar()
    {
        $this->insertarRegistroNomina(50, 1, 1, 101, 5);
        request()->replace([
            'filtro_documento' => 1,
            'filtro_empleado' => 1,
            'filtro_concepto' => 5
        ]);

        DB::table('nom_doc_registros')->where('id', 50)->delete();
        $opciones = NomDocRegistro::get_filtros_avanzados_index()['filtro_empleado']['options'];

        $this->assertSame(['' => 'Todos'], $opciones);
        $this->assertSame(0, NomDocRegistro::consultar_registros(10, '')->total());
    }

    /** @test */
    public function guarda_solamente_el_empleado_que_cumple_los_filtros()
    {
        $request = Request::create('/nom_registros_documentos', 'POST', [
            'nom_doc_encabezado_id' => 1,
            'nom_concepto_id' => 5,
            'grupo_empleado_id' => 10,
            'cargo_id' => 100,
            'filtro_nom_contrato_id' => 101,
            'nom_contrato_id' => [101],
            'core_tercero_id' => [1],
            'valor' => [125000],
            'app_id' => 17,
            'modelo_id' => 91
        ]);

        (new RegistrosDocumentosController)->store($request);

        $this->assertSame(1, DB::table('nom_doc_registros')->count());
        $registro = DB::table('nom_doc_registros')->first();
        $this->assertSame(101, (int) $registro->nom_contrato_id);
        $this->assertSame(1, (int) $registro->core_tercero_id);
        $this->assertSame(125000.0, (float) $registro->valor_devengo);
    }

    /** @test */
    public function permite_guardar_un_subconjunto_valido_de_los_empleados_filtrados()
    {
        $request = Request::create('/nom_registros_documentos', 'POST', [
            'nom_doc_encabezado_id' => 1,
            'nom_concepto_id' => 5,
            'nom_contrato_id' => [101],
            'core_tercero_id' => [1],
            'valor' => [90000],
            'app_id' => 17,
            'modelo_id' => 91
        ]);

        (new RegistrosDocumentosController)->store($request);

        $this->assertSame(1, DB::table('nom_doc_registros')->count());
        $this->assertSame(101, (int) DB::table('nom_doc_registros')->first()->nom_contrato_id);
    }

    /** @test */
    public function resuelve_el_contrato_correcto_si_el_id_oculto_quedo_desfasado()
    {
        $request = Request::create('/nom_registros_documentos', 'POST', [
            'nom_doc_encabezado_id' => 1,
            'nom_concepto_id' => 5,
            'grupo_empleado_id' => 10,
            'nom_contrato_id' => [99999],
            'core_tercero_id' => [1],
            'valor' => [80000],
            'app_id' => 17,
            'modelo_id' => 91
        ]);

        (new RegistrosDocumentosController)->store($request);

        $registro = DB::table('nom_doc_registros')->first();
        $this->assertSame(101, (int) $registro->nom_contrato_id);
        $this->assertSame(80000.0, (float) $registro->valor_devengo);
    }

    /** @test */
    public function calcula_las_horas_con_el_contrato_seleccionado_del_documento()
    {
        DB::table('nom_conceptos')->where('id', 5)->update(['porcentaje_sobre_basico' => 0.5]);
        $request = Request::create('/nom_registros_documentos', 'POST', [
            'nom_doc_encabezado_id' => 1,
            'nom_concepto_id' => 5,
            'filtro_nom_contrato_id' => 101,
            'nom_contrato_id' => [101],
            'core_tercero_id' => [1],
            'cantidad_horas' => [10],
            'app_id' => 17,
            'modelo_id' => 91
        ]);

        (new RegistrosDocumentosController)->store($request);

        $registro = DB::table('nom_doc_registros')->first();
        $this->assertSame(101, (int) $registro->nom_contrato_id);
        $this->assertSame(10.0, (float) $registro->cantidad_horas);
        $this->assertSame(47619.0, (float) $registro->valor_devengo);
    }

    private function insertarRegistroNomina($id, $documentoId, $terceroId, $contratoId, $conceptoId)
    {
        DB::table('nom_doc_registros')->insert([
            'id' => $id, 'nom_doc_encabezado_id' => $documentoId, 'core_tercero_id' => $terceroId,
            'nom_contrato_id' => $contratoId, 'fecha' => '2026-08-30', 'core_empresa_id' => 1,
            'nom_concepto_id' => $conceptoId, 'cantidad_horas' => 0, 'valor_devengo' => 0,
            'valor_deduccion' => 10000, 'estado' => 'Activo', 'creado_por' => 'pruebas@appsiel.test',
            'modificado_por' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /** @test */
    public function rechaza_un_empleado_que_no_corresponde_a_los_filtros()
    {
        $request = Request::create('/nom_registros_documentos', 'POST', [
            'nom_doc_encabezado_id' => 1,
            'nom_concepto_id' => 5,
            'grupo_empleado_id' => 10,
            'cargo_id' => 100,
            'filtro_nom_contrato_id' => 101,
            'nom_contrato_id' => [202],
            'core_tercero_id' => [2],
            'valor' => [125000],
            'app_id' => 17,
            'modelo_id' => 91
        ]);

        (new RegistrosDocumentosController)->store($request);

        $this->assertSame(0, DB::table('nom_doc_registros')->count());
    }

    /** @test */
    public function actualiza_el_registro_del_contrato_filtrado()
    {
        DB::table('nom_doc_registros')->insert([
            'id' => 50, 'nom_doc_encabezado_id' => 1, 'core_tercero_id' => 1,
            'nom_contrato_id' => 101, 'fecha' => '2026-08-30', 'core_empresa_id' => 1,
            'nom_concepto_id' => 5, 'cantidad_horas' => 0, 'valor_devengo' => 100000,
            'valor_deduccion' => 0, 'estado' => 'Activo', 'creado_por' => 'anterior@appsiel.test',
            'modificado_por' => null, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        $request = Request::create('/nom_registros_documentos/editar1', 'PUT', [
            'nom_doc_encabezado_id' => 1,
            'nom_concepto_id' => 5,
            'grupo_empleado_id' => 10,
            'cargo_id' => 100,
            'filtro_nom_contrato_id' => 101,
            'nom_contrato_id' => [101],
            'core_tercero_id' => [1],
            'nom_registro_id' => [50],
            'valor' => [225000],
            'app_id' => 17,
            'modelo_id' => 91
        ]);

        (new RegistrosDocumentosController)->update($request, 'editar1');

        $registro = DB::table('nom_doc_registros')->where('id', 50)->first();
        $this->assertSame(225000.0, (float) $registro->valor_devengo);
        $this->assertSame('pruebas@appsiel.test', $registro->modificado_por);
    }
}
