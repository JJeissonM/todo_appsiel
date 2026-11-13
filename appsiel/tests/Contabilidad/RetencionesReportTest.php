<?php

use App\Contabilidad\Services\Retenciones\RetencionesReportService;
use App\Contabilidad\Services\Retenciones\ConceptosDeclaracion;
use App\Contabilidad\Services\Retenciones\TrazabilidadRetenciones;
use App\Http\Controllers\Contabilidad\RetencionesReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RetencionesReportTest extends TestCase
{
    private $conexion;

    public function setUp()
    {
        parent::setUp();
        $this->conexion=DB::getDefaultConnection();
        config(['database.connections.retenciones_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('retenciones_test');
        DB::statement('CREATE TABLE core_empresas (id INTEGER, descripcion TEXT, numero_identificacion TEXT, digito_verificacion TEXT)');
        DB::table('core_empresas')->insert(['id'=>1,'descripcion'=>'Empresa prueba','numero_identificacion'=>'900','digito_verificacion'=>'1']);
        DB::statement('CREATE TABLE core_terceros (id INTEGER, core_empresa_id INTEGER, tipo TEXT, descripcion TEXT, numero_identificacion TEXT, digito_verificacion TEXT)');
        foreach ([[1,1,'Persona natural','Natural'],[2,1,'Persona jurídica','Jurídica'],[3,2,'Persona natural','Ajeno']] as $t) {
            DB::table('core_terceros')->insert(['id'=>$t[0],'core_empresa_id'=>$t[1],'tipo'=>$t[2],'descripcion'=>$t[3],'numero_identificacion'=>'100'.$t[0],'digito_verificacion'=>'1']);
        }
        DB::statement('CREATE TABLE compras_doc_encabezados (id INTEGER, core_empresa_id INTEGER, core_tercero_id INTEGER, core_tipo_transaccion_id INTEGER, core_tipo_doc_app_id INTEGER, consecutivo INTEGER, fecha TEXT, estado TEXT)');
        DB::statement('CREATE TABLE compras_doc_registros (id INTEGER, compras_doc_encabezado_id INTEGER)');
        foreach ([[1,1,1,1,'Activo'],[2,1,2,2,'Activo'],[3,1,2,3,'Anulado'],[4,2,3,1,'Activo']] as $d) {
            $this->documento($d[0],$d[1],$d[2],$d[3],$d[4]);
        }
        DB::statement('CREATE TABLE core_tipos_docs_apps (id INTEGER, prefijo TEXT)');
        DB::table('core_tipos_docs_apps')->insert(['id'=>22,'prefijo'=>'FC']);
        DB::statement('CREATE TABLE contab_retenciones (id INTEGER, descripcion TEXT, tasa_retencion REAL, cta_compras_id INTEGER, cta_compras_devol_id INTEGER, cta_ventas_id INTEGER, cta_ventas_devol_id INTEGER)');
        foreach ([[1,'Compras',2.5,101],[2,'ICA',0.414,102],[3,'IVA',15,103]] as $r) {
            DB::table('contab_retenciones')->insert(['id'=>$r[0],'descripcion'=>$r[1],'tasa_retencion'=>$r[2],'cta_compras_id'=>$r[3],'cta_compras_devol_id'=>$r[3],'cta_ventas_id'=>$r[3]+100,'cta_ventas_devol_id'=>$r[3]+100]);
        }
        DB::statement('CREATE TABLE contab_registros_retenciones (id INTEGER PRIMARY KEY AUTOINCREMENT, tipo TEXT, estado TEXT, contab_retencion_id INTEGER, compras_doc_registro_id INTEGER, valor_base_retencion REAL, tasa_retencion REAL, valor REAL, core_tipo_transaccion_id INTEGER, core_tipo_doc_app_id INTEGER, consecutivo INTEGER, numero_certificado TEXT, created_at TEXT, updated_at TEXT)');
        foreach ([[1,1,100000,2500,'practicada'],[2,1,200000,5000,'practicada'],[2,2,100000,414,'practicada'],[2,3,19000,2850,'practicada'],[3,1,999999,999,'practicada'],[4,1,888888,888,'practicada'],[1,1,5000,125,'sufrida']] as $r) {
            $this->registro($r[0],$r[1],$r[2],$r[3],$r[4]);
        }
        require_once __DIR__.'/../../database/migrations/2026_11_13_000003_add_retention_reporting_trace.php';
        (new AddRetentionReportingTrace)->up();
        foreach ([[1,'FUENTE','compras',null,null],[2,'ICA','ica','20001','4711'],[3,'IVA','iva_responsables',null,null]] as $c) {
            DB::table('contab_retenciones_reporte_config')->insert(['core_empresa_id'=>1,'contab_retencion_id'=>$c[0],'anio'=>2026,'tipo_impuesto'=>$c[1],'concepto'=>$c[2],'codigo_municipio'=>$c[3],'actividad_ica'=>$c[4],'modificado_por'=>'test']);
        }
        DB::statement('CREATE TABLE contab_movimientos (id INTEGER PRIMARY KEY AUTOINCREMENT, core_empresa_id INTEGER, core_tipo_transaccion_id INTEGER, core_tipo_doc_app_id INTEGER, consecutivo INTEGER, core_tercero_id INTEGER, contab_cuenta_id INTEGER, fecha TEXT, estado TEXT, valor_debito REAL, valor_credito REAL)');
        DB::table('contab_movimientos')->insert(['core_empresa_id'=>1,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>22,'consecutivo'=>1,'core_tercero_id'=>1,'contab_cuenta_id'=>101,'fecha'=>'2026-11-01','estado'=>'Activo','valor_debito'=>0,'valor_credito'=>-2500]);
        DB::statement('CREATE TABLE sys_reportes (id INTEGER, url_form_action TEXT)');
        DB::table('sys_reportes')->insert(['id'=>77,'url_form_action'=>'contab_reporte_retenciones']);
        $usuario=Mockery::mock(App\User::class.'[hasRole,can]');
        $usuario->id=10; $usuario->empresa_id=1; $usuario->email='test@appsiel.test';
        $usuario->shouldReceive('hasRole')->andReturn(true);
        $usuario->shouldReceive('can')->andReturn(true);
        Auth::setUser($usuario);
    }

    public function tearDown()
    {
        Cache::forget(RetencionesReportController::cacheKey(77));
        DB::purge('retenciones_test'); DB::setDefaultConnection($this->conexion);
        parent::tearDown();
    }

    public function test_reconstruye_trazabilidad_sin_mezclar_empresas_y_suma_una_sola_vez()
    {
        $datos=$this->datos();
        $this->assertCount(4,$datos['detalle']);
        $this->assertEquals(1076400,$datos['retencion_total']);
        $this->assertCount(4,$datos['resumen']);
        $natural=$datos['resumen']->filter(function($r){return $r->tipo_impuesto==='FUENTE' && $r->persona==='natural';})->first();
        $juridica=$datos['resumen']->filter(function($r){return $r->tipo_impuesto==='FUENTE' && $r->persona==='juridica';})->first();
        $this->assertEquals([86,102],[$natural->casilla_base,$natural->casilla_retencion]);
        $this->assertEquals([36,49],[$juridica->casilla_base,$juridica->casilla_retencion]);
        $this->assertEquals(10000000,$natural->base_centavos);
        $this->assertEquals(20000000,$juridica->base_centavos);
        $ica=$datos['detalle']->filter(function($r){return $r->tipo_impuesto==='ICA';})->first();
        $this->assertSame('20001',$ica->codigo_municipio);
        $this->assertNull($ica->casilla_retencion);
        $this->assertCount(0,$datos['pendientes']);
    }

    public function test_filtros_y_retenciones_sufridas_no_se_convierten_en_obligaciones_dian()
    {
        $this->assertCount(1,$this->datos(['tipo_impuesto'=>'IVA'])['detalle']);
        $this->assertCount(1,$this->datos(['tipo_impuesto'=>'ICA','codigo_municipio'=>'20001','tarifa'=>'0.414'])['detalle']);
        $this->assertCount(0,$this->datos(['tercero_id'=>3])['detalle']);
        $this->assertCount(1,$this->datos(['tipo_persona'=>'natural'])['detalle']);
        $this->assertCount(0,$this->datos(['fecha_desde'=>'2026-11-02'])['detalle']);
        $sufridas=$this->datos(['naturaleza'=>'sufrida']);
        $this->assertEquals(12500,$sufridas['retencion_total']);
        $this->assertNull($sufridas['detalle']->first()->casilla_retencion);
    }

    public function test_acumula_la_casilla_iva_antes_de_redondear_y_descuenta_devoluciones()
    {
        $this->registro(1,3,10000,1500,'practicada');
        (new TrazabilidadRetenciones)->reconstruir();
        $datos=$this->datos();
        $casilla=$datos['casillas']->filter(function($c){return $c->casilla===131;})->first();
        $this->assertEquals(435000,$casilla->centavos);
        $this->assertEquals(4000,round($casilla->centavos/100000)*1000);
        DB::table('contab_retenciones')->insert(['id'=>4,'descripcion'=>'Devolución IVA','tasa_retencion'=>15]);
        DB::table('contab_retenciones_reporte_config')->insert(['core_empresa_id'=>1,'contab_retencion_id'=>4,'anio'=>2026,'tipo_impuesto'=>'IVA','concepto'=>'devolucion_iva','modificado_por'=>'test']);
        $this->registro(1,4,1000,150,'practicada');
        (new TrazabilidadRetenciones)->reconstruir();
        $datos=$this->datos(['tipo_impuesto'=>'IVA']);
        $this->assertEquals(420000,$datos['retencion_total']);
        $total=$datos['casillas']->filter(function($c){return $c->casilla===134;})->first();
        $this->assertEquals(420000,$total->centavos);
    }

    public function test_no_inventa_conceptos_ni_persona_y_conserva_importes_pendientes()
    {
        DB::table('contab_retenciones_reporte_config')->where('contab_retencion_id',1)->delete();
        DB::table('contab_registros_retenciones')->where('id',1)->update(['tipo_persona_retenida'=>'sin_clasificar']);
        $datos=$this->datos();
        $this->assertSame(2,$datos['sin_clasificar']);
        $this->assertCount(2,$datos['pendientes']);
        $this->assertEquals(1076400,$datos['retencion_total']);
        $this->assertNull($datos['detalle']->first()->casilla_retencion);
    }

    public function test_asiento_manual_no_duplica_auxiliar_y_exige_conciliar_base()
    {
        $this->documento(5,1,2,5,'Activo');
        DB::table('contab_movimientos')->insert(['core_empresa_id'=>1,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>22,'consecutivo'=>5,'core_tercero_id'=>3,'contab_cuenta_id'=>101,'fecha'=>'2026-11-01','estado'=>'Activo','valor_debito'=>0,'valor_credito'=>-100]);
        $datos=$this->datos();
        $this->assertCount(5,$datos['detalle']);
        $this->assertEquals(1086400,$datos['retencion_total']);
        $this->assertCount(1,$datos['pendientes']);
        $pendiente=$datos['pendientes']->first();
        $this->assertSame('Jurídica',$pendiente->tercero);
        $this->assertEquals(0,$pendiente->base_centavos);
        $this->assertContains('Asiento sin auxiliar',implode(';',$pendiente->observaciones));
    }

    public function test_no_resuelve_registro_historico_con_documentos_ambiguos()
    {
        $id=$this->registro(1,1,100,2.5,'practicada');
        DB::table('contab_registros_retenciones')->where('id',$id)->update(['compras_doc_registro_id'=>0]);
        $resultado=(new TrazabilidadRetenciones)->reconstruir();
        $this->assertEquals(1,$resultado['pendientes']);
        $this->assertNull(DB::table('contab_registros_retenciones')->where('id',$id)->value('core_empresa_id'));
        (new AddRetentionReportingTrace)->up(); // Se puede reanudar una migración incompleta.
        $this->assertNull(DB::table('contab_registros_retenciones')->where('id',$id)->value('core_empresa_id'));
    }

    public function test_reporte_estandar_exportable_y_cache_pdf_aislado()
    {
        $req=new Request(['fecha_desde'=>'2026-11-01','fecha_hasta'=>'2026-11-01','naturaleza'=>'practicada','vista'=>'completo','reporte_instancia'=>json_encode(['id'=>77])]);
        $html=(new RetencionesReportController)->generar($req);
        $this->assertContains('Resumen fiscal',$html);
        $this->assertEquals(4,substr_count($html,'data-excel-export='));
        $this->assertContains('enlace_show_documento/',$html);
        $this->assertContains('10.764,00',$html);
        $this->assertContains('Detalla tercero: Sí',html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
        $this->assertSame($html,Cache::get(RetencionesReportController::cacheKey(77)));
        $this->assertNull(Cache::get('pdf_reporte_77'));
        $req->merge(['detalla_terceros'=>'No']);
        $consolidado=(new RetencionesReportController)->generar($req);
        $this->assertContains('Detalla tercero: No',$consolidado);
        $this->assertNotContains('data-excel-export="terceros"',$consolidado);
        $this->assertNotContains('data-excel-export="detalle"',$consolidado);
        $this->assertNotContains('1001',$consolidado);
        $this->assertContains('10.764,00',$consolidado);
        $this->assertSame($consolidado,Cache::get(RetencionesReportController::cacheKey(77)));
        $req->merge(['detalla_terceros'=>'Si']);
        $conDetalle=(new RetencionesReportController)->generar($req);
        $this->assertContains('data-excel-export="terceros"',$conDetalle);
        $this->assertContains('data-excel-export="detalle"',$conDetalle);
        $this->assertContains('1001',$conDetalle);
        $this->assertContains('10.764,00',$conDetalle);
        Auth::user()->empresa_id=2;
        $this->assertNull(Cache::get(RetencionesReportController::cacheKey(77)));
        Auth::user()->empresa_id=1;
        $error=(new RetencionesReportController)->generar(new Request(['fecha_desde'=>'2026-11-02','fecha_hasta'=>'2026-11-01']));
        $this->assertContains('Revise las fechas',$error);
        $this->assertNull(Cache::get(RetencionesReportController::cacheKey(77)));
        $error=(new RetencionesReportController)->generar(new Request(['fecha_desde'=>'2026-11-01','fecha_hasta'=>'2026-11-01','tercero_id'=>3]));
        $this->assertContains('no pertenece',$error);
    }

    public function test_casillas_de_otras_vigencias_no_se_asignan_automaticamente()
    {
        $this->assertEquals([null,null],ConceptosDeclaracion::casillas('compras','juridica',2027));
        $this->assertEquals([null,132],ConceptosDeclaracion::casillas('iva_no_residentes','juridica',2026));
        $this->assertEquals([null,null],ConceptosDeclaracion::casillas('trabajo','juridica',2026));
    }

    public function test_registros_nuevos_guardan_empresa_y_naturaleza_del_beneficiario()
    {
        $registro=\App\Contabilidad\RegistroRetencion::create([
            'core_empresa_id'=>1,'core_tercero_id'=>1,'fecha'=>'2026-11-01',
            'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>22,'consecutivo'=>1,
            'tipo'=>'practicada','contab_retencion_id'=>1,'estado'=>'Activo',
            'valor_base_retencion'=>100,'valor'=>2.5,'tasa_retencion'=>2.5
        ]);
        $this->assertEquals(1,$registro->core_empresa_id);
        $this->assertEquals(1,$registro->documento_id);
        $this->assertSame('natural',$registro->tipo_persona_retenida);
        DB::table('core_terceros')->where('id',1)->update(['tipo'=>'Persona jurídica']);
        $this->assertSame('natural',DB::table('contab_registros_retenciones')->where('id',$registro->id)->value('tipo_persona_retenida'));
    }

    public function test_configuracion_es_anual_y_por_empresa()
    {
        $respuesta=(new RetencionesReportController)->guardarConfiguracion(new Request([
            'anio'=>2027,'contab_retencion_id'=>1,'tipo_impuesto'=>'FUENTE','concepto'=>'honorarios','id'=>14
        ]));
        $this->assertContains('anio=2027',$respuesta->getTargetUrl());
        $this->assertSame('honorarios',DB::table('contab_retenciones_reporte_config')->where('core_empresa_id',1)->where('anio',2027)->value('concepto'));
        $this->assertSame('compras',DB::table('contab_retenciones_reporte_config')->where('core_empresa_id',1)->where('anio',2026)->where('contab_retencion_id',1)->value('concepto'));
    }

    public function test_pdf_estandar_utiliza_solo_el_cache_de_la_empresa_y_el_usuario()
    {
        Cache::put(RetencionesReportController::cacheKey(77),'<p>Retenciones de esta empresa</p>',10);
        $pdf=Mockery::mock();
        $pdf->shouldReceive('loadHTML')->once()->with(Mockery::on(function($vista){
            return strpos($vista->getData()['contenido'],'esta empresa')!==false;
        }))->andReturnSelf();
        $pdf->shouldReceive('setPaper')->once()->with('letter','Landscape')->andReturnSelf();
        $pdf->shouldReceive('stream')->once()->with('retenciones.pdf')->andReturn('PDF-OK');
        app()->instance('dompdf.wrapper',$pdf);
        $this->assertSame('PDF-OK',(new \App\Http\Controllers\Sistema\VistaController)->generar_pdf(77));
    }

    public function test_no_permite_consultar_sin_permiso()
    {
        $usuario=Mockery::mock(App\User::class.'[hasRole,can]');
        $usuario->id=99; $usuario->empresa_id=1;
        $usuario->shouldReceive('hasRole')->andReturn(false);
        $usuario->shouldReceive('can')->with('contab_reporte_retenciones')->andReturn(false);
        Auth::setUser($usuario);
        try {
            (new RetencionesReportController)->generar(new Request);
            $this->fail('El reporte requiere permiso.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $error) {
            $this->assertSame(403,$error->getStatusCode());
        }
    }

    private function datos(array $extra=[])
    {
        return (new RetencionesReportService)->generar(1,array_merge(['fecha_desde'=>'2026-11-01','fecha_hasta'=>'2026-11-30','naturaleza'=>'practicada'], $extra));
    }
    private function documento($id,$empresa,$tercero,$consecutivo,$estado)
    {
        DB::table('compras_doc_encabezados')->insert(['id'=>$id,'core_empresa_id'=>$empresa,'core_tercero_id'=>$tercero,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>22,'consecutivo'=>$consecutivo,'fecha'=>'2026-11-01','estado'=>$estado]);
        DB::table('compras_doc_registros')->insert(['id'=>$id,'compras_doc_encabezado_id'=>$id]);
    }
    private function registro($linea,$retencion,$base,$valor,$naturaleza)
    {
        $doc=DB::table('compras_doc_encabezados')->where('id',$linea)->first();
        return DB::table('contab_registros_retenciones')->insertGetId(['tipo'=>$naturaleza,'estado'=>'Activo','contab_retencion_id'=>$retencion,'compras_doc_registro_id'=>$linea,'valor_base_retencion'=>$base,'tasa_retencion'=>$base ? $valor*100/$base:0,'valor'=>$valor,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>22,'consecutivo'=>$doc->consecutivo,'numero_certificado'=>'CERT']);
    }
}
