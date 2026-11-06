<?php
require_once __DIR__.'/EncabezadosCalificacionServiceTest.php';

use App\Calificaciones\AgrupacionCalificacion;
use App\Calificaciones\ColumnaAgrupacionCalificacion;
use App\Calificaciones\Services\AgrupacionesColumnasService;
use App\Calificaciones\Services\EncabezadosCalificacionService;
use App\Calificaciones\Services\CalificacionDefinitivaService;
use App\Calificaciones\EncabezadoCalificacion;
use App\Http\Controllers\Calificaciones\EncabezadoCalificacionController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class AgrupacionesColumnasServiceTest extends EncabezadosCalificacionServiceTest
{
    public function setUp()
    {
        parent::setUp();
        config(['calificaciones.usar_agrupaciones_columnas_calificaciones' => 'No']);
        require_once database_path('migrations/2026_11_06_000001_create_agrupaciones_columnas_calificaciones.php');
        (new CreateAgrupacionesColumnasCalificaciones)->up();
        require_once database_path('migrations/2026_11_06_000002_add_porcentaje_to_agrupacion_columnas.php');
        (new AddPorcentajeToAgrupacionColumnas)->up();
    }
    private function activar()
    {
        $user = new App\User; $user->id = 1; $user->empresa_id = 1; $user->email = 'docente@test.local';
        $role = new Spatie\Permission\Models\Role; $role->name = 'SuperAdmin';
        $user->setRelation('roles', new Illuminate\Database\Eloquent\Collection([$role]));
        Auth::setUser($user);
        config(['calificaciones.usar_agrupaciones_columnas_calificaciones' => 'Si']);
        Schema::create('sga_colegios', function($t){$t->increments('id');$t->integer('empresa_id');});
        Schema::create('sga_cursos', function($t){$t->increments('id');$t->integer('id_colegio');});
        Schema::create('sga_asignaturas', function($t){$t->increments('id');$t->integer('id_colegio');});
        DB::table('sga_colegios')->insert(['id'=>1,'empresa_id'=>1]);
        DB::table('sga_cursos')->insert([['id'=>4,'id_colegio'=>1],['id'=>5,'id_colegio'=>1]]);
        DB::table('sga_asignaturas')->insert(['id'=>9,'id_colegio'=>1]);
        DB::table('sga_periodos')->insert([['id'=>201, 'fecha_desde'=>'2026-01-01'], ['id'=>202, 'fecha_desde'=>'2026-04-01']]);
    }
    private function grupo($titulo, $periodo = null, $empresa = 1)
    {
        $id = DB::table('sga_agrupaciones_calificacion')->insertGetId(['empresa_id'=>$empresa, 'anio'=>2026, 'periodo_id'=>$periodo, 'titulo'=>$titulo, 'peso'=>100, 'estado'=>'Activo', 'bloqueado'=>0]);
        return AgrupacionCalificacion::find($id);
    }
    private function columna($grupo, $columna, $pesoGrupo, $label = 'Actividad')
    {
        if (!$grupo->fresh()->enUso()) DB::table('sga_agrupaciones_calificacion')->where('id',$grupo->id)->update(['peso'=>$pesoGrupo]);
        return app(AgrupacionesColumnasService::class)->guardarColumna($grupo, ['columna_calificacion'=>$columna, 'label'=>$label, 'porcentaje'=>1]);
    }
    private function requestActividad($extra = [])
    {
        return Request::create('/calificaciones_encabezados', 'POST', array_merge(['anio'=>2026, 'periodo_id'=>201, 'curso_id'=>4, 'asignatura_id'=>9,
            'columna_calificacion'=>'C1', 'fecha'=>'2026-03-01', 'descripcion'=>'Evaluación escrita', 'id_encabezado_calificacion'=>0, 'peso'=>99], $extra));
    }
    public function test_agrupaciones_definen_labels_y_pesos_antes_de_crear_actividades()
    {
        $this->activar();
        $this->columna($this->grupo('CONOCER'), 'C1', 30, 'EVAL 1');
        $this->columna($this->grupo('HACER'), 'C2', 70, 'TALLER');
        $s = new EncabezadosCalificacionService;
        $r = $s->getResumenParaCarga(2026, 201, 4, 9);
        $this->assertSame('agrupaciones', $r['modo_encabezados']);
        $this->assertTrue($r['usar_encabezados_fijos']);
        $this->assertSame('EVAL 1', $r['columnas'][1]->label);
        $this->assertEquals(30, $r['array_pesos'][1]);
        $this->assertSame('HACER', $r['grupos_titulo'][1]->titulo);
        $this->assertNull($s->getEncabezado(2026,201,4,9,'C1'));
        $this->assertEquals(4.4, (new CalificacionDefinitivaService($s))->calcular(2026,201,4,9,['C1'=>3,'C2'=>5,'C3'=>100]));
    }
    public function test_modal_guarda_actividad_estandar_e_ignora_porcentaje_manipulado()
    {
        $this->activar(); $g=$this->grupo('CONOCER'); $c=$this->columna($g,'C1',100,'EVALUACIÓN');
        $controller = new EncabezadoCalificacionController;
        $this->assertSame('CREADO', $controller->store($this->requestActividad(['titulo'=>'MANIPULADO','label'=>'OTRO'])));
        $registro=EncabezadoCalificacion::first();
        $this->assertEquals(100,$registro->peso); $this->assertSame('CONOCER',$registro->titulo); $this->assertSame('EVALUACIÓN',$registro->label);
        $this->assertEquals(201,$registro->periodo_id);$this->assertEquals(4,$registro->curso_id);$this->assertEquals(9,$registro->asignatura_id);
        $this->assertEquals($c->id,$registro->agrupacion_columna_id);
        $this->assertTrue((bool)$g->fresh()->bloqueado);
        $this->assertSame('MODIFICADO', $controller->store($this->requestActividad(['id_encabezado_calificacion'=>$registro->id,'descripcion'=>'Nueva actividad'])));
        $this->assertSame('Nueva actividad',$registro->fresh()->descripcion);
        $this->assertSame('ELIMINADO',$controller->store($this->requestActividad(['id_encabezado_calificacion'=>$registro->id,'descripcion'=>''])));
        $r=(new EncabezadosCalificacionService)->getResumenParaCarga(2026,201,4,9);
        $this->assertEquals(100,$r['array_pesos'][1]);$this->assertTrue($g->fresh()->enUso());
    }
    public function test_no_puede_editar_actividad_de_otro_curso_por_id()
    {
        $this->activar();$this->columna($this->grupo('CONOCER'),'C1',100);
        $c=new EncabezadoCalificacionController;$c->store($this->requestActividad());
        $id=EncabezadoCalificacion::first()->id;
        $this->setExpectedException(Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $c->store($this->requestActividad(['id_encabezado_calificacion'=>$id,'curso_id'=>5]));
    }
    public function test_periodo_propio_reemplaza_conjunto_anual_y_empresas_no_se_mezclan()
    {
        $this->activar();$this->columna($this->grupo('ANUAL'),'C1',100);
        $this->columna($this->grupo('PROPIO',202),'C2',100);
        $id=DB::table('sga_agrupaciones_calificacion')->insertGetId(['empresa_id'=>2,'titulo'=>'OTRA EMPRESA','anio'=>2026,'peso'=>100,'estado'=>'Activo']);
        DB::table('sga_agrupacion_columnas')->insert(['agrupacion_id'=>$id,'columna_calificacion'=>'C1','label'=>'AJENO','orden'=>1]);
        $s=new EncabezadosCalificacionService;
        $this->assertSame('ANUAL',$s->getResumenParaCarga(2026,201,4,9)['columnas'][1]->titulo);
        $r=$s->getResumenParaCarga(2026,202,4,9);
        $this->assertFalse($r['columnas'][1]->configurado);$this->assertTrue($r['columnas'][2]->configurado);
    }
    public function test_no_admite_columnas_repetidas_entre_agrupaciones_del_mismo_alcance()
    {
        $this->activar();$this->columna($this->grupo('A'),'C1',50);
        $this->setExpectedException(Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->columna($this->grupo('B'),'C1',50);
    }
    public function test_conjunto_incompleto_no_permite_calcular_una_nota()
    {
        $this->activar();$this->columna($this->grupo('A'),'C1',30);
        $this->setExpectedException(Symfony\Component\HttpKernel\Exception\HttpException::class);
        (new CalificacionDefinitivaService)->calcular(2026,201,4,9,['C1'=>5]);
    }
    public function test_columnas_usadas_no_se_modifican_aunque_se_borre_la_actividad()
    {
        $this->activar();$g=$this->grupo('A');$this->columna($g,'C1',100);
        $c=new EncabezadoCalificacionController;$c->store($this->requestActividad());
        $this->setExpectedException(Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->columna($g->fresh(),'C1',50);
    }
    public function test_modal_muestra_peso_informativo_sin_input_editable()
    {
        $this->activar();$this->columna($this->grupo('A'),'C1',100,'LABEL');
        $r=$this->requestActividad();$this->app->instance('request',$r);Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
        $html=(new EncabezadoCalificacionController)->create();
        $this->assertContains('Porcentaje calculado: 100', $html);
        $this->assertContains('id="actividad_agrupada"', $html);
        $this->assertNotContains('name="titulo"', $html);
        $this->assertNotContains('name="peso" type="text"', $html);
        $this->assertContains('type="hidden" id="peso"', $html);
        $this->assertContains('required="required"', $html);
    }
    public function test_rechaza_cambiar_el_titulo_de_agrupacion_ya_usada()
    {
        $this->activar();$g=$this->grupo('A');$this->columna($g,'C1',100);
        (new EncabezadoCalificacionController)->store($this->requestActividad());
        $g=$g->fresh();$g->titulo='MODIFICADO';
        $this->setExpectedException(Symfony\Component\HttpKernel\Exception\HttpException::class);$g->save();
    }
    public function test_guardar_notas_bloquea_las_agrupaciones_aunque_no_haya_actividad()
    {
        $this->activar();$g=$this->grupo('A');$this->columna($g,'C1',100);
        $aux=new App\Calificaciones\CalificacionAuxiliar;
        $aux->timestamps=false;$aux->fill(['anio'=>2026,'id_periodo'=>201,'curso_id'=>4,'id_asignatura'=>9,'C1'=>4]);$aux->save();
        $this->assertTrue($g->fresh()->enUso());
    }
    public function test_porcentaje_mayor_a_cien_y_columnas_fuera_de_rango_se_rechazan()
    {
        $this->activar();$g=$this->grupo('A');
        try { $this->columna($g,'C16',100);$this->fail('Aceptó C16'); }
        catch (Illuminate\Foundation\Validation\ValidationException $e) { $this->assertTrue($e->validator->fails()); }
        foreach ([101,-1] as $peso) {
            try { AgrupacionCalificacion::create(['titulo'=>'Inválido','anio'=>2027,'estado'=>'Borrador','peso'=>$peso]);$this->fail('Aceptó porcentaje inválido'); }
            catch (Illuminate\Foundation\Validation\ValidationException $e) { $this->assertTrue($e->validator->fails()); }
        }
    }
    public function test_migracion_registra_catalogo_y_relacion_show_sin_duplicar_metadatos()
    {
        $this->activar();
        Schema::create('sys_aplicaciones',function($t){$t->increments('id');$t->string('app');});
        Schema::create('sys_modelos',function($t){$t->increments('id');foreach(['descripcion','modelo','name_space','modelo_relacionado','url_crear','url_edit','url_ver','url_print','enlaces','url_estado','url_eliminar','controller_complementario','url_form_create','home_miga_pan','ruta_storage_imagen','ruta_storage_archivo_adjunto'] as $c)$t->string($c);$t->timestamps();});
        Schema::create('sys_campos',function($t){$t->increments('id');foreach(['name','descripcion','tipo','opciones','value','atributos','definicion'] as $c)$t->string($c);$t->integer('requerido');$t->integer('editable');$t->integer('unico');$t->timestamps();});
        Schema::create('sys_modelo_tiene_campos',function($t){$t->integer('core_modelo_id');$t->integer('core_campo_id');$t->integer('orden');});
        Schema::create('permissions',function($t){$t->increments('id');$t->string('name');$t->string('descripcion');$t->integer('core_app_id');$t->integer('modelo_id');$t->string('url');$t->integer('parent');$t->integer('orden');$t->integer('enabled');$t->string('fa_icon');$t->timestamps();});
        Schema::create('roles',function($t){$t->increments('id');$t->string('name');});
        Schema::create('role_has_permissions',function($t){$t->integer('role_id');$t->integer('permission_id');});
        DB::table('sys_aplicaciones')->insert(['app'=>'calificaciones']);DB::table('roles')->insert(['name'=>'SuperAdmin']);
        $migration=new CreateAgrupacionesColumnasCalificaciones;$migration->up();$migration->up();
        $model=App\Sistema\Modelo::where('name_space','App\\Calificaciones\\AgrupacionCalificacion')->firstOrFail();
        $this->assertSame('columnas',$model->modelo_relacionado);$this->assertEquals(5,$model->campos()->count());
        $this->assertEquals(1,DB::table('permissions')->count());$this->assertEquals(1,DB::table('role_has_permissions')->count());
        $g=$this->grupo('A');$this->columna($g,'C1',100,'PRUEBA');
        $r=(new App\Http\Controllers\Sistema\ModeloController)->get_tabla_relacionada($model,$g);
        $this->assertContains('PRUEBA',$r['tabla']);$this->assertContains('Asociar o actualizar columna',$r['tabla']);
        $this->assertSame('ColumnasCalificacion',$r['opciones']['']);
    }

    public function test_recalcular_actividad_no_modifica_notas_de_otro_curso()
    {
        $this->activar();$this->columna($this->grupo('A'),'C1',100);
        foreach ([4,5] as $curso) {
            $base=['anio'=>2026,'id_periodo'=>201,'curso_id'=>$curso,'id_asignatura'=>9,'id_estudiante'=>1,'codigo_matricula'=>'M'.$curso];
            DB::table('sga_calificaciones')->insert(array_merge($base,['calificacion'=>0]));
            DB::table('sga_calificaciones_auxiliares')->insert(array_merge($base,['C1'=>4]));
        }
        (new EncabezadoCalificacionController)->store($this->requestActividad());
        $this->assertEquals(4,DB::table('sga_calificaciones')->where('curso_id',4)->value('calificacion'));
        $this->assertEquals(0,DB::table('sga_calificaciones')->where('curso_id',5)->value('calificacion'));
    }
    public function test_una_modal_no_puede_leer_el_encabezado_de_otro_colegio()
    {
        $this->activar();$this->columna($this->grupo('A'),'C1',100);
        DB::table('sga_cursos')->insert(['id'=>10,'id_colegio'=>2]);
        $r=$this->requestActividad(['curso_id'=>10]);$this->app->instance('request',$r);Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
        $this->setExpectedException(Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        (new EncabezadoCalificacionController)->create();
    }
    public function test_no_admite_cambiar_el_alcance_a_otro_que_ya_tiene_la_columna()
    {
        $this->activar();$a=$this->grupo('A');$b=$this->grupo('B',202);
        $this->columna($a,'C1',100);$this->columna($b,'C1',100);
        $b->periodo_id=null;
        $this->setExpectedException(Symfony\Component\HttpKernel\Exception\HttpException::class);$b->save();
    }

    public function test_porcentaje_del_grupo_se_reparte_por_igual_y_se_redistribuye_al_asociar()
    {
        $this->activar();$g=$this->grupo('CONOCER');
        $this->columna($g,'C1',30,'EVAL 1');$this->columna($g,'C2',30,'EVAL 2');
        $service=app(AgrupacionesColumnasService::class);
        foreach($g->columnas()->get() as $c)$this->assertEquals(15,$c->peso);
        $this->columna($g,'C3',30,'AUTO');$this->columna($g,'C4',30,'COEVAL');
        foreach($g->columnas()->get() as $c)$this->assertEquals(7.5,$c->peso);
        $last=$g->columnas()->where('columna_calificacion','C4')->first();$service->eliminarColumna($g,$last->id);
        foreach($g->columnas()->get() as $c)$this->assertEquals(10,$c->peso);
    }
    public function test_division_entre_tres_no_pierde_porcentaje_ni_redondea_la_definitiva_antes_de_tiempo()
    {
        $this->activar();$g=$this->grupo('A');
        foreach(['C1','C2','C3'] as $c)$this->columna($g,$c,100,$c);
        $resumen=(new EncabezadosCalificacionService)->getResumenParaCarga(2026,201,4,9);
        $this->assertEquals(100/3,$resumen['array_pesos'][1]);
        $this->assertEquals($resumen['array_pesos'][1],$resumen['array_pesos'][2]);
        $this->assertEquals(100,$resumen['suma_porcentajes']);
        $this->assertEquals(4,(new CalificacionDefinitivaService)->calcular(2026,201,4,9,['C1'=>4,'C2'=>4,'C3'=>4]));
        $this->assertEquals(3.33,(new CalificacionDefinitivaService)->calcular(2026,201,4,9,['C1'=>1,'C2'=>4,'C3'=>5]));
    }

    public function test_columnas_vacias_no_reducen_la_definitiva_y_se_conserva_el_peso_del_grupo()
    {
        $this->activar();$a=$this->grupo('A');$b=$this->grupo('B');
        $this->columna($a,'C1',30);$this->columna($a,'C2',30);
        $this->columna($b,'C3',70);$this->columna($b,'C4',70);
        $calc=new CalificacionDefinitivaService;
        $this->assertEquals(4,$calc->calcular(2026,201,4,9,['C1'=>4]));
        $this->assertEquals(3.3,$calc->calcular(2026,201,4,9,['C1'=>4,'C3'=>2,'C4'=>4]));
        $this->assertEquals(0,$calc->calcular(2026,201,4,9,['C1'=>0,'C2'=>0,'C3'=>0,'C4'=>0]));
    }
    public function test_columnas_se_ordenan_por_agrupacion_y_numero_sin_cambiar_sus_identificadores()
    {
        $this->activar(); $a=$this->grupo('Actividades'); $b=$this->grupo('Evaluaciones');
        foreach (['C10','C1','C3'] as $columna) $this->columna($a,$columna,40);
        $this->columna($b,'C2',60);
        $resumen=(new EncabezadosCalificacionService)->getResumenParaCarga(2026,201,4,9);
        $visibles=array_filter($resumen['columnas'],function($c){return $c->configurado;});
        $this->assertSame([1,3,10,2],array_keys($visibles));
        $this->assertCount(2,$resumen['grupos_titulo']);
        $this->assertSame(3,$resumen['grupos_titulo'][0]->cantidad);
        $this->assertSame(1,$resumen['grupos_titulo'][0]->inicio);
        $this->assertSame('C2',$resumen['columnas'][2]->columna_calificacion);
        $this->assertEquals(60,$resumen['array_pesos'][2]);
        $this->assertEquals(3.2,(new CalificacionDefinitivaService)->calcular(2026,201,4,9,['C1'=>5,'C2'=>2]));
    }

    public function test_porcentajes_individuales_se_redistribuyen_proporcionalmente_solo_entre_columnas_con_notas()
    {
        $this->activar(); $a=$this->grupo('A'); $b=$this->grupo('B');
        foreach (['C1'=>20,'C2'=>30,'C3'=>50] as $columna=>$porcentaje) {
            $this->columna($a,$columna,30);
            app(AgrupacionesColumnasService::class)->guardarColumna($a, ['columna_calificacion'=>$columna,'label'=>'Actividad','porcentaje'=>$porcentaje]);
        }
        $this->columna($b,'C4',70);
        $resumen=(new EncabezadosCalificacionService)->getResumenParaCarga(2026,201,4,9);
        $this->assertEquals(6,$resumen['array_pesos'][1]);
        $this->assertEquals(9,$resumen['array_pesos'][2]);
        $this->assertEquals(15,$resumen['array_pesos'][3]);
        $calc=new CalificacionDefinitivaService;
        $this->assertEquals(2.94,$calc->calcular(2026,201,4,9,['C1'=>4,'C2'=>2,'C4'=>3]));
        $this->assertEquals(2.8,$calc->calcular(2026,201,4,9,['C1'=>4,'C2'=>2]));
        $this->assertEquals(4,$calc->calcular(2026,201,4,9,['C1'=>4]));
        $actividad=new EncabezadoCalificacion;
        $actividad->fill(['anio'=>2026,'periodo_id'=>201,'curso_id'=>4,'asignatura_id'=>9,'columna_calificacion'=>'C2']);
        app(AgrupacionesColumnasService::class)->aplicarActividad($actividad);
        $this->assertEquals(9,$actividad->peso);
    }

    public function test_porcentaje_individual_invalido_no_se_guarda()
    {
        $this->activar(); $g=$this->grupo('A');
        foreach ([0,-1,101,'texto',null] as $porcentaje) {
            try {
                app(AgrupacionesColumnasService::class)->guardarColumna($g,['columna_calificacion'=>'C1','label'=>'Actividad','porcentaje'=>$porcentaje]);
                $this->fail('Aceptó porcentaje individual inválido');
            } catch (\Illuminate\Foundation\Validation\ValidationException $e) { $this->assertTrue(true); }
        }
        $this->assertEquals(0,$g->columnas()->count());
    }

    public function test_primera_y_ultima_nota_de_una_columna_recalculan_toda_la_clase()
    {
        $this->activar();$g=$this->grupo('A');$this->columna($g,'C1',100);$this->columna($g,'C2',100);
        foreach([1,2] as $id)DB::table('sga_calificaciones')->insert(['id_colegio'=>1,'anio'=>2026,'id_periodo'=>201,'curso_id'=>4,'id_asignatura'=>9,'id_estudiante'=>$id,'codigo_matricula'=>'M'.$id,'calificacion'=>0]);
        $uno=new App\Calificaciones\CalificacionAuxiliar;$uno->timestamps=false;
        $uno->fill(['id_colegio'=>1,'anio'=>2026,'id_periodo'=>201,'curso_id'=>4,'id_asignatura'=>9,'id_estudiante'=>1,'codigo_matricula'=>'M1','C1'=>4]);$uno->save();
        $this->assertEquals(4,DB::table('sga_calificaciones')->where('id_estudiante',1)->value('calificacion'));
        $dos=new App\Calificaciones\CalificacionAuxiliar;$dos->timestamps=false;
        $dos->fill(['id_colegio'=>1,'anio'=>2026,'id_periodo'=>201,'curso_id'=>4,'id_asignatura'=>9,'id_estudiante'=>2,'codigo_matricula'=>'M2','C2'=>2]);$dos->save();
        $this->assertEquals(2,DB::table('sga_calificaciones')->where('id_estudiante',1)->value('calificacion'));
        $this->assertEquals(1,DB::table('sga_calificaciones')->where('id_estudiante',2)->value('calificacion'));
        $dos->C2=0;$dos->save();
        $this->assertEquals(4,DB::table('sga_calificaciones')->where('id_estudiante',1)->value('calificacion'));
        $this->assertEquals(0,DB::table('sga_calificaciones')->where('id_estudiante',2)->value('calificacion'));
        $dos->C2=2;$dos->save();$dos->delete();
        $this->assertEquals(4,DB::table('sga_calificaciones')->where('id_estudiante',1)->value('calificacion'));
    }
    public function test_columna_con_nota_en_otro_alcance_no_activa_la_columna_de_esta_clase()
    {
        $this->activar();$g=$this->grupo('A');$this->columna($g,'C1',100);$this->columna($g,'C2',100);
        foreach([[2026,202,4,9,1],[2026,201,5,9,1],[2026,201,4,10,1],[2027,201,4,9,1],[2026,201,4,9,2]] as $v) {
            DB::table('sga_calificaciones_auxiliares')->insert(['anio'=>$v[0],'id_periodo'=>$v[1],'curso_id'=>$v[2],'id_asignatura'=>$v[3],'id_colegio'=>$v[4],'id_estudiante'=>2,'C2'=>5]);
        }
        $this->assertEquals(4,(new CalificacionDefinitivaService)->calcular(2026,201,4,9,['id_colegio'=>1,'id_estudiante'=>1,'C1'=>4]));
    }
    public function test_borrado_pendiente_reemplaza_la_nota_anterior_y_considera_estudiantes_no_visibles()
    {
        $this->activar();$g=$this->grupo('A');$this->columna($g,'C1',100);$this->columna($g,'C2',100);
        DB::table('sga_calificaciones_auxiliares')->insert(['id_colegio'=>1,'anio'=>2026,'id_periodo'=>201,'curso_id'=>4,'id_asignatura'=>9,'id_estudiante'=>1,'C1'=>4,'C2'=>3]);
        $s=new App\Calificaciones\Services\ColumnasCalificadasService;
        $this->assertSame([1],$s->activas(2026,201,4,9,['id_colegio'=>1,'id_estudiante'=>1,'C1'=>4,'C2'=>0]));
        $this->assertSame([],$s->activasFueraDeEstudiantes(2026,201,4,9,[1],1));
        $this->assertSame([1,2],$s->activasFueraDeEstudiantes(2026,201,4,9,[2],1));
    }

    public function test_guardado_real_de_filas_devuelve_las_definitivas_actualizadas_de_toda_la_clase()
    {
        $this->activar();$g=$this->grupo('A');$this->columna($g,'C1',100);$this->columna($g,'C2',100);
        Schema::table('sga_calificaciones',function($t){$t->text('logros')->nullable();$t->string('creado_por')->nullable();$t->string('modificado_por')->nullable();});
        Schema::table('sga_calificaciones_auxiliares',function($t){$t->string('creado_por')->nullable();$t->string('modificado_por')->nullable();$t->timestamps();});
        $reflection=new ReflectionClass(App\Http\Controllers\Calificaciones\CalificacionController::class);
        $controller=$reflection->newInstanceWithoutConstructor();$prop=$reflection->getProperty('colegio');$prop->setAccessible(true);$prop->setValue($controller,(object)['id'=>1]);
        $base=['anio'=>2026,'id_periodo'=>201,'curso_id'=>4,'id_asignatura'=>9,'cantidad_calificaciones'=>16];
        $uno=$controller->store_calificacion(array_merge($base,['id_estudiante'=>1,'codigo_matricula'=>'M1','fila_id'=>'fila_1','C1'=>4]));
        $this->assertEquals(4,$uno[0]->calificacion_texto);
        $dos=$controller->store_calificacion(array_merge($base,['id_estudiante'=>2,'codigo_matricula'=>'M2','fila_id'=>'fila_2','C2'=>2]));
        $this->assertEquals(1,$dos[0]->calificacion_texto);
        $map=array_column($dos[0]->definitivas_alcance,'calificacion','id_estudiante');$this->assertEquals(2,$map[1]);
        $borrada=$controller->store_calificacion(array_merge($base,['id_estudiante'=>2,'codigo_matricula'=>'M2','fila_id'=>'fila_2','C2'=>0,
            'id_calificacion'=>$dos[0]->id_calificacion,'id_calificacion_aux'=>$dos[0]->id_calificacion_aux]));
        $this->assertSame('no',$borrada[0]->id_calificacion);
        $map=array_column($borrada[0]->definitivas_alcance,'calificacion','id_estudiante');$this->assertEquals(4,$map[1]);
    }

}
