<?php

use App\Calificaciones\Services\EncabezadosCalificacionService;
use App\Calificaciones\Services\CalificacionesService;
use App\Calificaciones\Services\CalificacionDefinitivaService;
use App\Calificaciones\EncabezadoCalificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class EncabezadosCalificacionServiceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => ''
            ],
            'calificaciones.manejar_encabezados_por_anio_lectivo_en_calificaciones' => 'Si'
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('sga_calificaciones_encabezados', function ($table) {
            $table->increments('id');
            $table->string('columna_calificacion');
            $table->string('label')->nullable();
            $table->string('titulo')->nullable();
            $table->string('descripcion')->nullable();
            $table->decimal('peso', 8, 2)->default(0);
            $table->date('fecha')->nullable();
            $table->integer('anio')->nullable();
            $table->integer('periodo_id')->nullable();
            $table->integer('curso_id')->nullable();
            $table->integer('asignatura_id')->nullable();
            $table->string('creado_por')->nullable();
            $table->string('modificado_por')->nullable();
            $table->timestamps();
        });

        Schema::create('sga_periodos', function ($table) {
            $table->increments('id');
            $table->date('fecha_desde');
        });

        Schema::create('sga_calificaciones_auxiliares', function ($table) {
            $table->increments('id');
			$table->integer('id_colegio')->nullable();
			$table->integer('anio')->nullable();
            $table->integer('id_periodo');
            $table->integer('curso_id');
			$table->integer('id_asignatura')->nullable();
			$table->integer('id_estudiante')->nullable();
			$table->string('codigo_matricula')->nullable();
            for ($i = 1; $i < 16; $i++) {
                $table->decimal('C'.$i, 8, 2)->default(0);
            }
        });

		Schema::create('sga_calificaciones', function ($table) {
			$table->increments('id');
			$table->integer('id_colegio')->nullable();
			$table->integer('anio')->nullable();
			$table->integer('id_periodo');
			$table->integer('curso_id');
			$table->integer('id_asignatura');
			$table->integer('id_estudiante');
			$table->string('codigo_matricula')->nullable();
			$table->decimal('calificacion', 8, 2)->default(0);
			$table->timestamps();
		});
    }

    public function tearDown()
    {
		Schema::dropIfExists('sga_calificaciones');
        Schema::dropIfExists('sga_calificaciones_auxiliares');
        Schema::dropIfExists('sga_periodos');
        Schema::dropIfExists('sga_calificaciones_encabezados');
        DB::purge('sqlite');

        parent::tearDown();
    }

    public function test_usa_solo_los_encabezados_fijos_del_periodo_configurado()
    {
        $this->insertarEncabezado('C1', 'Anterior', 2026, null, null, null);
        $this->insertarEncabezado('C1', 'Periodo 10', 2026, 10, null, null);
        $this->insertarEncabezado('C1', 'Normal periodo 20', 2026, 20, 5, 7);

        $service = new EncabezadosCalificacionService();

        $periodo10 = $service->getEncabezados(2026, 10, 99, 88);
        $periodo20 = $service->getEncabezados(2026, 20, 5, 7);
        $resumenPeriodo10 = $service->getResumenParaCarga(2026, 10, 99, 88);

        $this->assertTrue($service->usarEncabezadosFijosEnPeriodo(10));
        $this->assertSame('Periodo 10', $periodo10->first()->label);
        $this->assertTrue($resumenPeriodo10['columnas'][1]->configurado);
        $this->assertFalse($resumenPeriodo10['columnas'][2]->configurado);
        $this->assertCount(1, $resumenPeriodo10['grupos_titulo']);
        $this->assertSame(1, $resumenPeriodo10['grupos_titulo'][0]->cantidad);
        $this->assertFalse($service->usarEncabezadosFijosEnPeriodo(20));
        $this->assertSame('Normal periodo 20', $periodo20->first()->label);
        $this->assertCount(1, $periodo20);
    }

    public function test_periodo_sin_configuracion_fija_se_maneja_normal()
    {
        $this->insertarEncabezado('C1', 'Otro periodo', 2026, 10, null, null);
        $this->insertarEncabezado('C2', 'Actividad curso', 2026, 30, 4, 9);

        $service = new EncabezadosCalificacionService();
        $encabezados = $service->getEncabezados(2026, 30, 4, 9);
        $atributos = $service->getAtributosDePersistencia(2026, 30, 4, 9);

        $this->assertFalse($service->usarEncabezadosFijosEnPeriodo(30));
        $this->assertSame('Actividad curso', $encabezados->first()->label);
        $this->assertSame(30, $atributos['periodo_id']);
        $this->assertSame(4, $atributos['curso_id']);
        $this->assertSame(9, $atributos['asignatura_id']);
    }

    public function test_persistencia_fija_conserva_el_periodo_y_no_el_curso_o_asignatura()
    {
        $this->insertarEncabezado('C1', 'Periodo 40', 2026, 40, null, null);

        $service = new EncabezadosCalificacionService();
        $atributos = $service->getAtributosDePersistencia(2026, 40, 4, 9);

        $this->assertSame(2026, $atributos['anio']);
        $this->assertSame(40, $atributos['periodo_id']);
        $this->assertNull($atributos['curso_id']);
        $this->assertNull($atributos['asignatura_id']);
    }

    public function test_configuracion_desactivada_ignora_encabezados_fijos()
    {
        $this->insertarEncabezado('C1', 'Fijo', 2026, 50, null, null);
        $this->insertarEncabezado('C1', 'Normal', 2026, 50, 4, 9);
        config(['calificaciones.manejar_encabezados_por_anio_lectivo_en_calificaciones' => 'No']);

        $service = new EncabezadosCalificacionService();
        $encabezados = $service->getEncabezados(2026, 50, 4, 9);

        $this->assertFalse($service->usarEncabezadosFijosEnPeriodo(50));
        $this->assertSame('Normal', $encabezados->first()->label);
    }

    public function test_reportes_muestran_solo_columnas_configuradas_en_periodos_fijos()
    {
        $this->insertarEncabezado('C1', 'Tareas', 2026, 60, null, null);
        DB::table('sga_periodos')->insert([
            'id' => 60,
            'fecha_desde' => '2026-01-15'
        ]);
        DB::table('sga_calificaciones_auxiliares')->insert([
            'id_periodo' => 60,
            'curso_id' => 4,
            'C1' => 0,
            'C2' => 5
        ]);

        $columnas = (new CalificacionesService())
            ->get_object_calificaciones_auxiliares(60, 4);

        $this->assertCount(1, $columnas);
        $this->assertSame('C1', $columnas[0]->columna_calificacion);
        $this->assertSame('Tareas', $columnas[0]->label);
    }

    public function test_columna_es_unica_para_el_mismo_alcance_incluyendo_valores_nulos()
    {
        $id = $this->insertarEncabezado('C2', 'Exposición', 2026, 70, null, null);
        $modelo = new EncabezadoCalificacion();

        $requestDuplicado = Request::create('/', 'POST', [
            'columna_calificacion' => 'c2',
            'anio' => 2026,
            'periodo_id' => 70,
            'curso_id' => '',
            'asignatura_id' => ''
        ]);
        $reglaDuplicado = $modelo->getReglaUnicidadPorAlcance($requestDuplicado);
        $validatorDuplicado = Validator::make(
            $requestDuplicado->all(),
            ['columna_calificacion' => $reglaDuplicado]
        );

        $requestEdicion = Request::create('/', 'POST', $requestDuplicado->all());
        $reglaEdicion = $modelo->getReglaUnicidadPorAlcance($requestEdicion, $id);
        $validatorEdicion = Validator::make(
            $requestEdicion->all(),
            ['columna_calificacion' => $reglaEdicion]
        );

        $requestOtroPeriodo = Request::create('/', 'POST', array_merge(
            $requestDuplicado->all(),
            ['periodo_id' => 71]
        ));
        $reglaOtroPeriodo = $modelo->getReglaUnicidadPorAlcance($requestOtroPeriodo);
        $validatorOtroPeriodo = Validator::make(
            $requestOtroPeriodo->all(),
            ['columna_calificacion' => $reglaOtroPeriodo]
        );

        $this->assertTrue($validatorDuplicado->fails());
        $this->assertFalse($validatorEdicion->fails());
        $this->assertFalse($validatorOtroPeriodo->fails());
        $this->assertNull($requestDuplicado->input('curso_id'));
        $this->assertNull($requestDuplicado->input('asignatura_id'));
        $this->assertSame('C2', $requestDuplicado->input('columna_calificacion'));
    }

    public function test_columna_puede_repetirse_solamente_en_otro_curso_o_asignatura()
    {
        $this->insertarEncabezado('C3', 'Proyecto', 2026, 80, 4, 9);
        $modelo = new EncabezadoCalificacion();

        $requestMismoAlcance = Request::create('/', 'POST', [
            'columna_calificacion' => 'C3',
            'anio' => 2026,
            'periodo_id' => 80,
            'curso_id' => 4,
            'asignatura_id' => 9
        ]);
        $requestOtraAsignatura = Request::create('/', 'POST', [
            'columna_calificacion' => 'C3',
            'anio' => 2026,
            'periodo_id' => 80,
            'curso_id' => 4,
            'asignatura_id' => 10
        ]);

        $validatorMismoAlcance = Validator::make(
            $requestMismoAlcance->all(),
            ['columna_calificacion' => $modelo->getReglaUnicidadPorAlcance($requestMismoAlcance)]
        );
        $validatorOtraAsignatura = Validator::make(
            $requestOtraAsignatura->all(),
            ['columna_calificacion' => $modelo->getReglaUnicidadPorAlcance($requestOtraAsignatura)]
        );

        $this->assertTrue($validatorMismoAlcance->fails());
        $this->assertFalse($validatorOtraAsignatura->fails());
    }

    public function test_edicion_conserva_anio_periodo_curso_y_asignatura_si_no_se_envian()
    {
        $id = $this->insertarEncabezado('C4', 'Final', 2026, 90, 4, 9);
        $modelo = new EncabezadoCalificacion();
        $request = Request::create('/', 'POST', [
            'columna_calificacion' => 'C4',
            'label' => 'Examen final'
        ]);

        $regla = $modelo->getReglaUnicidadPorAlcance($request, $id);
        $validator = Validator::make(
            $request->all(),
            ['columna_calificacion' => $regla]
        );

        $this->assertFalse($validator->fails());
        $this->assertSame(2026, $request->input('anio'));
        $this->assertSame(90, $request->input('periodo_id'));
        $this->assertSame(4, $request->input('curso_id'));
        $this->assertSame(9, $request->input('asignatura_id'));
    }

    public function test_fecha_es_opcional_para_un_encabezado_manejado_solo_por_periodo()
    {
        $modelo = new EncabezadoCalificacion();
        $request = Request::create('/', 'POST', [
            'columna_calificacion' => 'C5',
            'anio' => 2026,
            'periodo_id' => 91,
            'curso_id' => '',
            'asignatura_id' => ''
        ]);
        $controller = new EncabezadoCalificacionValidationController();

        $modelo->validar_datos_creacion($request, $controller);

        $this->assertNull($request->input('fecha'));
        $this->assertFalse($controller->validator->fails());
    }

    public function test_creacion_normal_inicializa_y_valida_la_fecha()
    {
        $modelo = new EncabezadoCalificacion();
        $request = Request::create('/', 'POST', [
            'columna_calificacion' => 'C8',
            'anio' => 2026,
            'periodo_id' => 94,
            'curso_id' => 4,
            'asignatura_id' => 9
        ]);
        $controller = new EncabezadoCalificacionValidationController();

        $modelo->validar_datos_creacion($request, $controller);

        $this->assertSame(date('Y-m-d'), $request->input('fecha'));
        $this->assertFalse($controller->validator->fails());
    }

    public function test_edicion_conserva_la_fecha_si_no_se_envia()
    {
        $id = $this->insertarEncabezado('C6', 'Prueba', 2026, 92, null, null);
        DB::table('sga_calificaciones_encabezados')
            ->where('id', $id)
            ->update(['fecha' => '2026-03-10']);

        $modelo = new EncabezadoCalificacion();
        $request = Request::create('/', 'POST', [
            'columna_calificacion' => 'C6'
        ]);
        $controller = new EncabezadoCalificacionValidationController();

        $modelo->validar_datos_actualizacion($request, $controller, $id);

        $this->assertSame('2026-03-10', $request->input('fecha'));
        $this->assertFalse($controller->validator->fails());
    }

    public function test_rechaza_una_fecha_con_formato_invalido()
    {
        $modelo = new EncabezadoCalificacion();
        $request = Request::create('/', 'POST', [
            'fecha' => '24/08/2026',
            'columna_calificacion' => 'C7',
            'anio' => 2026,
            'periodo_id' => 93,
            'curso_id' => '',
            'asignatura_id' => ''
        ]);
        $controller = new EncabezadoCalificacionValidationController();

        $modelo->validar_datos_creacion($request, $controller);

        $this->assertTrue($controller->validator->fails());
        $this->assertTrue($controller->validator->errors()->has('fecha'));
    }

    public function test_edicion_normal_sigue_exigiendo_fecha_aunque_no_envie_el_alcance()
    {
        $id = $this->insertarEncabezado('C9', 'Actividad normal', 2026, 95, 4, 9);
        $modelo = new EncabezadoCalificacion();
        $request = Request::create('/', 'POST', [
            'fecha' => '',
            'columna_calificacion' => 'C9'
        ]);
        $controller = new EncabezadoCalificacionValidationController();

        $modelo->validar_datos_actualizacion($request, $controller, $id);

        $this->assertTrue($controller->validator->fails());
        $this->assertTrue($controller->validator->errors()->has('fecha'));
    }

    public function test_definitiva_sin_pesos_usa_promedio_simple_de_notas_no_cero()
    {
        $definitiva = (new CalificacionDefinitivaService())->calcular(
            2026,
            100,
            4,
            9,
            ['C1' => 4, 'C2' => 2, 'C3' => 0]
        );

        $this->assertSame(3.0, $definitiva);
    }

    public function test_definitiva_fija_ignora_columnas_no_configuradas()
    {
        $this->insertarEncabezado('C1', 'Tareas', 2026, 101, null, null);
        $this->insertarEncabezado('C3', 'Examen', 2026, 101, null, null);

        $definitiva = (new CalificacionDefinitivaService())->calcular(
            2026,
            101,
            4,
            9,
            ['C1' => 4, 'C2' => 5, 'C3' => 2]
        );

        $this->assertSame(3.0, $definitiva);
    }

    public function test_definitiva_ponderada_usa_los_pesos_configurados()
    {
        $this->insertarEncabezado('C1', 'Tareas', 2026, 102, null, null, 20);
        $this->insertarEncabezado('C2', 'Examen', 2026, 102, null, null, 80);
        $this->insertarEncabezado('C3', 'Sin peso', 2026, 102, null, null, 0);

        $definitiva = (new CalificacionDefinitivaService())->calcular(
            2026,
            102,
            4,
            9,
            ['C1' => 3, 'C2' => 5, 'C3' => 5]
        );

        $this->assertSame(4.6, $definitiva);
    }

	public function test_recalcula_todo_el_periodo_al_eliminar_el_ultimo_encabezado_fijo()
	{
		$idEncabezado = $this->insertarEncabezado('C1', 'Tareas', 2026, 103, null, null, 100);
		DB::table('sga_calificaciones_auxiliares')->insert([
			'id_colegio' => 1,
			'anio' => 2026,
			'id_periodo' => 103,
			'curso_id' => 4,
			'id_asignatura' => 9,
			'id_estudiante' => 7,
			'codigo_matricula' => 'M1',
			'C1' => 4,
			'C2' => 2
		]);
		DB::table('sga_calificaciones')->insert([
			'id_colegio' => 1,
			'anio' => 2026,
			'id_periodo' => 103,
			'curso_id' => 4,
			'id_asignatura' => 9,
			'id_estudiante' => 7,
			'codigo_matricula' => 'M1',
			'calificacion' => 0
		]);

		$encabezado = EncabezadoCalificacion::find($idEncabezado);
		$calculadora = new CalificacionDefinitivaService(new EncabezadosCalificacionService());
		$calculadora->recalcularPorEncabezado($encabezado);
		$this->assertEquals(4, DB::table('sga_calificaciones')->value('calificacion'));

		DB::table('sga_calificaciones_encabezados')->where('id', $idEncabezado)->delete();
		$calculadora->recalcularPorEncabezado($encabezado);

		$this->assertEquals(3, DB::table('sga_calificaciones')->value('calificacion'));
	}

	public function test_crud_no_permite_pesos_negativos_ni_superiores_al_total_disponible()
	{
		$this->insertarEncabezado('C1', 'Tareas', 2026, 104, null, null, 60);
		$modelo = new EncabezadoCalificacion();

		$requestExcedido = Request::create('/', 'POST', [
			'fecha' => '',
			'columna_calificacion' => 'C2',
			'peso' => 50,
			'anio' => 2026,
			'periodo_id' => 104,
			'curso_id' => '',
			'asignatura_id' => ''
		]);
		$controllerExcedido = new EncabezadoCalificacionValidationController();
		$modelo->validar_datos_creacion($requestExcedido, $controllerExcedido);

		$requestNegativo = Request::create('/', 'POST', array_merge(
			$requestExcedido->all(),
			['periodo_id' => 105, 'peso' => -1]
		));
		$controllerNegativo = new EncabezadoCalificacionValidationController();
		$modelo->validar_datos_creacion($requestNegativo, $controllerNegativo);

		$this->assertTrue($controllerExcedido->validator->errors()->has('peso'));
		$this->assertTrue($controllerNegativo->validator->errors()->has('peso'));
	}

    protected function insertarEncabezado($columna, $label, $anio, $periodoId, $cursoId, $asignaturaId, $peso = 0)
    {
        return DB::table('sga_calificaciones_encabezados')->insertGetId([
            'columna_calificacion' => $columna,
            'label' => $label,
			'peso' => $peso,
            'anio' => $anio,
            'periodo_id' => $periodoId,
            'curso_id' => $cursoId,
            'asignatura_id' => $asignaturaId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}

class EncabezadoCalificacionValidationController
{
    public $validator;

    public function validate($request, array $rules, array $messages = [])
    {
        $this->validator = Validator::make($request->all(), $rules, $messages);
    }
}
