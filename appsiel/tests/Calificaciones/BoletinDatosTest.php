<?php

use App\Http\Controllers\Calificaciones\BoletinController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BoletinDatosTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
            'calificaciones.colegio_maneja_metas' => 'No',
            'calificaciones.manejar_preinformes_academicos' => 'No'
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $tablas = [
            App\Calificaciones\ObservacionesBoletin::class => 'id_periodo curso_id id_estudiante observacion puesto',
            App\Calificaciones\Calificacion::class => 'id_periodo curso_id id_estudiante id_asignatura calificacion logros',
            App\Calificaciones\NotaNivelacion::class => 'periodo_id curso_id estudiante_id asignatura_id calificacion',
            App\Calificaciones\EscalaValoracion::class => 'periodo_lectivo_id calificacion_minima calificacion_maxima nombre_escala imagen',
            App\Calificaciones\Logro::class => 'codigo asignatura_id descripcion escala_valoracion_id curso_id periodo_id',
            App\Calificaciones\CursoTieneAsignatura::class => 'curso_id periodo_lectivo_id asignatura_id orden_boletin maneja_calificacion intensidad_horaria peso',
            App\Calificaciones\Asignatura::class => 'descripcion area_id',
            App\Calificaciones\Area::class => 'descripcion'
        ];
        foreach ($tablas as $modelo => $columnas) {
            Schema::create((new $modelo)->getTable(), function ($table) use ($columnas) {
                $table->increments('id');
                foreach (explode(' ', $columnas) as $columna) {
                    if (strpos($columna, 'id_') === 0 || substr($columna, -3) === '_id') {
                        $table->integer($columna)->nullable();
                    } else {
                        $table->string($columna)->nullable();
                    }
                }
            });
        }
        DB::table((new App\Calificaciones\Area)->getTable())->insert(['id' => 1, 'descripcion' => 'Área']);
        DB::table((new App\Calificaciones\Asignatura)->getTable())->insert(['id' => 1, 'area_id' => 1, 'descripcion' => 'Asignatura']);
        DB::table((new App\Calificaciones\CursoTieneAsignatura)->getTable())->insert([
            'curso_id' => 7, 'periodo_lectivo_id' => 7, 'asignatura_id' => 1, 'orden_boletin' => 1
        ]);
        DB::table((new App\Calificaciones\EscalaValoracion)->getTable())->insert([
            'periodo_lectivo_id' => 7, 'calificacion_minima' => 0, 'calificacion_maxima' => 5, 'nombre_escala' => 'Desempeño'
        ]);
        foreach ([105 => 4, 106 => 2] as $id => $nota) {
            DB::table((new App\Calificaciones\Calificacion)->getTable())->insert([
                'id_periodo' => 29, 'curso_id' => 7, 'id_estudiante' => $id, 'id_asignatura' => 1, 'calificacion' => $nota, 'logros' => ''
            ]);
        }
    }

    private function matricula($id)
    {
        $tercero = new App\Core\Tercero(['email' => 'estudiante' . $id . '@example.test']);
        $estudiante = new App\Matriculas\Estudiante;
        // PDO SQLite de PHP 7 devuelve claves foráneas como strings.
        $estudiante->incrementing = false;
        $estudiante->id = App\Calificaciones\Calificacion::where('id_estudiante', $id)->first()->id_estudiante;
        $estudiante->setRelation('tercero', $tercero);
        $matricula = new App\Matriculas\Matricula;
        $matricula->id_estudiante = $id;
        $matricula->setRelation('estudiante', $estudiante);
        return $matricula;
    }

    private function preparar($matriculas)
    {
        return (new BoletinController)->preparar_datos_boletin(
            (object)['id' => 29, 'periodo_lectivo_id' => 7],
            (object)['id' => 7], $matriculas, '0', 'No', 'No', false, false
        );
    }

    public function testBoletinIndividualConservaNotaYNoConsultaUsuariosDesactivados()
    {
        // No existen tablas de contraseñas ni docentes: "No" debe omitir esas consultas.
        $matricula = $this->matricula(105);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $datos = $this->preparar([$matricula]);
        $consultas = DB::getQueryLog();
        DB::disableQueryLog();
        $consultaNotas = array_values(array_filter($consultas, function ($consulta) {
            return strpos($consulta['query'], 'from "sga_calificaciones"') !== false;
        }));
        $this->assertCount(1, $consultaNotas);
        $this->assertContains('"id_estudiante" in (?)', $consultaNotas[0]['query']);
        $this->assertEquals([105, 29, 7], $consultaNotas[0]['bindings']);
        $this->assertCount(1, $datos);
        $this->assertEquals(4, $datos[0]->cuerpo_boletin->lineas[0]->valor_calificacion);
        $this->assertNull($datos[0]->password_estudiante);
        $this->assertNull($datos[0]->cuerpo_boletin->lineas[0]->profesor_asignatura);
        $this->assertTrue($datos[0]->cuerpo_boletin->lineas[0]->asignacion_asignatura->asignatura->relationLoaded('area'));
    }

    public function testSigueGenerandoDatosDeVariosEstudiantes()
    {
        $datos = $this->preparar(collect([$this->matricula(105), $this->matricula(106)]));
        $this->assertCount(2, $datos);
        $this->assertEquals(4, $datos[0]->cuerpo_boletin->lineas[0]->valor_calificacion);
        $this->assertEquals(2, $datos[1]->cuerpo_boletin->lineas[0]->valor_calificacion);
    }
}
