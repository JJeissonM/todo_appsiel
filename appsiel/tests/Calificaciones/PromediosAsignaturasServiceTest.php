<?php

use App\Calificaciones\Services\PromediosAsignaturasService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PromediosAsignaturasServiceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''
        ], 'calificaciones.asignatura_id_para_asistencias' => 99]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        Schema::create('sga_periodos', function ($t) {
            $t->increments('id'); $t->integer('periodo_lectivo_id');
        });
        Schema::create('sga_calificaciones', function ($t) {
            $t->increments('id'); $t->integer('id_colegio'); $t->integer('curso_id');
            $t->integer('id_estudiante'); $t->integer('id_asignatura'); $t->integer('id_periodo'); $t->float('calificacion');
        });
        Schema::create('sga_notas_nivelaciones', function ($t) {
            $t->increments('id'); $t->integer('colegio_id'); $t->integer('curso_id');
            $t->integer('estudiante_id'); $t->integer('asignatura_id'); $t->integer('periodo_id'); $t->float('calificacion');
        });
    }

    public function test_carga_notas_y_nivelaciones_en_dos_consultas_sin_mezclar_colegios_cursos_o_anios()
    {
        DB::table('sga_periodos')->insert([['id'=>1,'periodo_lectivo_id'=>2026],['id'=>2,'periodo_lectivo_id'=>2025]]);
        for ($i=1; $i<=80; $i++) {
            DB::table('sga_calificaciones')->insert(['id_colegio'=>1,'curso_id'=>4,'id_estudiante'=>$i,'id_asignatura'=>5,'id_periodo'=>1,'calificacion'=>2]);
            DB::table('sga_notas_nivelaciones')->insert(['colegio_id'=>1,'curso_id'=>4,'estudiante_id'=>$i,'asignatura_id'=>5,'periodo_id'=>1,'calificacion'=>4]);
        }
        foreach ([[2,4,1],[1,5,1],[1,4,2]] as $alcance) {
            DB::table('sga_calificaciones')->insert(['id_colegio'=>$alcance[0],'curso_id'=>$alcance[1],'id_estudiante'=>1,'id_asignatura'=>5,'id_periodo'=>$alcance[2],'calificacion'=>1]);
            DB::table('sga_notas_nivelaciones')->insert(['colegio_id'=>$alcance[0],'curso_id'=>$alcance[1],'estudiante_id'=>1,'asignatura_id'=>5,'periodo_id'=>$alcance[2],'calificacion'=>1]);
        }
        // Un duplicado no debe reemplazar la primera nivelación seleccionada.
        DB::table('sga_notas_nivelaciones')->insert(['colegio_id'=>1,'curso_id'=>4,'estudiante_id'=>1,'asignatura_id'=>5,'periodo_id'=>1,'calificacion'=>5]);
        DB::enableQueryLog(); DB::flushQueryLog();
        $notas = (new PromediosAsignaturasService)->calificaciones(1,4,2026);
        $this->assertCount(2, DB::getQueryLog());
        $this->assertCount(80, $notas);
        foreach ($notas as $nota) {
            $this->assertEquals(4,$nota->calificacion);
            $this->assertSame('n',$nota->lbl_nivelacion);
        }
    }

    public function test_exclusion_de_asistencias_y_notas_sin_nivelacion()
    {
        DB::table('sga_periodos')->insert(['id'=>1,'periodo_lectivo_id'=>2026]);
        foreach ([5,99] as $asignatura) DB::table('sga_calificaciones')->insert([
            'id_colegio'=>1,'curso_id'=>4,'id_estudiante'=>1,'id_asignatura'=>$asignatura,'id_periodo'=>1,'calificacion'=>0
        ]);
        $servicio = new PromediosAsignaturasService;
        $notas = $servicio->calificaciones(1,4,2026,true);
        $this->assertCount(1,$notas);
        $this->assertEquals(0,$notas->first()->calificacion);
        $this->assertSame('',$notas->first()->lbl_nivelacion);
        $this->assertCount(2,$servicio->calificaciones(1,4,2026));
        DB::table('sga_notas_nivelaciones')->insert([
            'colegio_id'=>0,'curso_id'=>4,'estudiante_id'=>1,'asignatura_id'=>5,'periodo_id'=>1,'calificacion'=>4
        ]);
        $nivelada = $servicio->calificaciones(1,4,2026,true)->first();
        $this->assertEquals(4,$nivelada->calificacion);
        $this->assertSame('n',$nivelada->lbl_nivelacion);
        DB::enableQueryLog(); DB::flushQueryLog();
        $this->assertCount(0,$servicio->calificaciones(1,4,2027));
        $this->assertCount(1,DB::getQueryLog());
    }

    public function test_indice_conserva_ceros_primera_nota_y_busqueda_con_ids_numericos()
    {
        $primera = (object)['id_estudiante'=>'01','id_asignatura'=>'5','id_periodo'=>'1','calificacion'=>0];
        $duplicada = (object)['id_estudiante'=>1,'id_asignatura'=>5,'id_periodo'=>1,'calificacion'=>5];
        $indice = (new PromediosAsignaturasService)->indexar(collect([$primera,$duplicada]));
        $this->assertSame($primera,$indice[1][5][1]);
        $this->assertFalse(isset($indice[1][5][2]));
    }

    public function test_nivelaciones_en_bloque_coinciden_con_consulta_anterior_para_cada_estudiante_asignatura_y_periodo()
    {
        DB::table('sga_periodos')->insert([['id'=>17,'periodo_lectivo_id'=>2026],['id'=>42,'periodo_lectivo_id'=>2026]]);
        foreach ([1,2,3] as $estudiante) foreach ([5,6,7] as $asignatura) foreach ([17,42] as $periodo) {
            DB::table('sga_calificaciones')->insert([
                'id_colegio'=>1,'curso_id'=>4,'id_estudiante'=>$estudiante,'id_asignatura'=>$asignatura,
                'id_periodo'=>$periodo,'calificacion'=>($estudiante+$asignatura)%5
            ]);
            if (($estudiante+$asignatura+$periodo)%2) DB::table('sga_notas_nivelaciones')->insert([
                'colegio_id'=>0,'curso_id'=>4,'estudiante_id'=>$estudiante,'asignatura_id'=>$asignatura,
                'periodo_id'=>$periodo,'calificacion'=>($estudiante+$asignatura)%3
            ]);
        }
        DB::table('sga_notas_nivelaciones')->insert([
            'colegio_id'=>1,'curso_id'=>5,'estudiante_id'=>1,'asignatura_id'=>5,'periodo_id'=>17,'calificacion'=>100
        ]);
        $anteriores = \App\Calificaciones\Calificacion::where('id_colegio',1)->where('curso_id',4)->orderBy('id')->get();
        $referencia = [];
        foreach ($anteriores as $nota) {
            $nivelacion = $nota->nota_nivelacion();
            $referencia[] = [(float)($nivelacion ? $nivelacion->calificacion : $nota->calificacion), $nivelacion ? 'n' : ''];
        }
        $actuales = (new PromediosAsignaturasService)->calificaciones(1,4,2026);
        $this->assertCount(count($referencia),$actuales);
        foreach ($actuales as $i=>$nota) {
            $this->assertSame($referencia[$i],[(float)$nota->calificacion,$nota->lbl_nivelacion]);
        }
    }

    public function test_ambas_vistas_conservan_promedios_con_cero_periodos_faltantes_y_nivelaciones()
    {
        config(['calificaciones.cantidad_decimales_mostrar_calificaciones' => 2]);
        $datos = [
            'asignaturas' => [['id'=>5,'abreviatura'=>'MATEMATICAS']],
            'estudiantes' => collect([(object)['id_estudiante'=>1,'nombre_completo'=>'ESTUDIANTE']]),
            'periodos' => collect([(object)['id'=>1,'numero'=>1],(object)['id'=>2,'numero'=>2],(object)['id'=>3,'numero'=>3]]),
            'calificaciones' => collect([
                (object)['id_estudiante'=>'01','id_asignatura'=>'5','id_periodo'=>'1','calificacion'=>0,'lbl_nivelacion'=>''],
                (object)['id_estudiante'=>1,'id_asignatura'=>5,'id_periodo'=>3,'calificacion'=>4,'lbl_nivelacion'=>'n']
            ]),
            'curso' => (object)['descripcion'=>'Cuarto'],
            'periodo_lectivo' => (object)['descripcion'=>'2026'],
            'tope_escala_valoracion_minima' => 2.99,
        ];
        foreach (['proyectado', 'consolidado'] as $tipo) {
            $vista = 'calificaciones.incluir.promedio_'.$tipo.'_asignaturas';
            $html = view($vista, $datos)->render();
            // Renderizar nuevamente en el mismo proceso tampoco debe redeclarar funciones.
            $this->assertSame($html, view($vista, $datos)->render());
            $dom = new DOMDocument;
            $dom->loadHTML('<meta charset="UTF-8">'.$html);
            $xpath = new DOMXPath($dom);
            $promedio = $xpath->query('//table/tbody/tr[1]/td[last()]')->item(0);
            $this->assertSame('2,00', trim($promedio->textContent));
            if ($tipo === 'proyectado') $this->assertContains('n', $xpath->query('//table/tbody/tr[1]/td[5]/sup | //table/tbody/tr[1]/td[5]//sup')->item(0)->textContent);
        }
    }
}
