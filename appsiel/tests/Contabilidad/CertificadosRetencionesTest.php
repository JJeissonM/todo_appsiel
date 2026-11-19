<?php

use App\Contabilidad\Services\Retenciones\FiltrosCertificados;
use App\Contabilidad\Services\Retenciones\DatosCertificadosService;
use App\Contabilidad\Services\Retenciones\CertificadosRetencionesService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CertificadosRetencionesTest extends TestCase
{
    private $conexion;
    private $archivos=[];

    public function setUp()
    {
        parent::setUp();
        $this->conexion=DB::getDefaultConnection();
        config(['database.connections.certificados_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('certificados_test');
        DB::statement('CREATE TABLE core_empresas (id INTEGER, tipo TEXT, descripcion TEXT, numero_identificacion TEXT, digito_verificacion TEXT, direccion1 TEXT)');
        DB::table('core_empresas')->insert(['id'=>1,'tipo'=>'Persona jurídica','descripcion'=>'Empresa prueba','numero_identificacion'=>'900123','digito_verificacion'=>'1','direccion1'=>'Calle 1']);
        DB::statement('CREATE TABLE core_terceros (id INTEGER, core_empresa_id INTEGER, tipo TEXT, descripcion TEXT, numero_identificacion TEXT, digito_verificacion TEXT, email TEXT)');
        foreach ([[10,1,'Proveedor A'],[11,1,'Proveedor B'],[20,2,'Ajeno']] as $t) {
            DB::table('core_terceros')->insert(['id'=>$t[0],'core_empresa_id'=>$t[1],'tipo'=>'Persona jurídica','descripcion'=>$t[2],'numero_identificacion'=>'123'.$t[0],'digito_verificacion'=>'1','email'=>'proveedor'.$t[0].'@example.com']);
        }
        DB::statement('CREATE TABLE core_firmas_autorizadas (id INTEGER, core_empresa_id INTEGER, core_tercero_id INTEGER, titulo_tercero TEXT, estado TEXT, imagen TEXT)');
        DB::table('core_firmas_autorizadas')->insert([
            ['id'=>1,'core_empresa_id'=>1,'core_tercero_id'=>10,'titulo_tercero'=>'Contador','estado'=>'Activo','imagen'=>'certificados_prueba.png'],
            ['id'=>2,'core_empresa_id'=>1,'core_tercero_id'=>11,'titulo_tercero'=>'Responsable sin imagen','estado'=>'Activo','imagen'=>''],
            ['id'=>3,'core_empresa_id'=>1,'core_tercero_id'=>10,'titulo_tercero'=>'Inactivo','estado'=>'Inactivo','imagen'=>''],
            ['id'=>4,'core_empresa_id'=>2,'core_tercero_id'=>20,'titulo_tercero'=>'Ajeno','estado'=>'Activo','imagen'=>''],
            ['id'=>5,'core_empresa_id'=>1,'core_tercero_id'=>20,'titulo_tercero'=>'Tercero ajeno','estado'=>'Activo','imagen'=>'']
        ]);
        $imagen=imagecreatetruecolor(16,8);imagefill($imagen,0,0,imagecolorallocate($imagen,255,255,255));
        ob_start();imagepng($imagen);$bytes=ob_get_clean();imagedestroy($imagen);
        Storage::disk('local')->put('firmas_autorizadas/certificados_prueba.png',$bytes);
        DB::statement('CREATE TABLE compras_proveedores (id INTEGER,core_tercero_id INTEGER,clase_proveedor_id INTEGER)');
        DB::table('compras_proveedores')->insert([['id'=>1,'core_tercero_id'=>10,'clase_proveedor_id'=>1],['id'=>2,'core_tercero_id'=>11,'clase_proveedor_id'=>2],['id'=>3,'core_tercero_id'=>20,'clase_proveedor_id'=>3]]);
        DB::statement('CREATE TABLE compras_clases_proveedores (id INTEGER,descripcion TEXT)');
        DB::table('compras_clases_proveedores')->insert([['id'=>1,'descripcion'=>'Grupo A'],['id'=>2,'descripcion'=>'Grupo B'],['id'=>3,'descripcion'=>'Ajeno']]);
        DB::statement('CREATE TABLE core_tipos_docs_apps (id INTEGER,prefijo TEXT)');
        DB::table('core_tipos_docs_apps')->insert(['id'=>17,'prefijo'=>'CC']);
        DB::statement('CREATE TABLE compras_doc_encabezados (id INTEGER,core_empresa_id INTEGER,core_tercero_id INTEGER,core_tipo_transaccion_id INTEGER,core_tipo_doc_app_id INTEGER,consecutivo INTEGER,fecha TEXT,estado TEXT,valor_total REAL,doc_proveedor_prefijo TEXT,doc_proveedor_consecutivo TEXT,descripcion TEXT)');
        DB::statement('CREATE TABLE contab_cuentas (id INTEGER,core_empresa_id INTEGER,codigo TEXT,descripcion TEXT)');
        DB::table('contab_cuentas')->insert([['id'=>101,'core_empresa_id'=>1,'codigo'=>'236540','descripcion'=>'Compras'],['id'=>102,'core_empresa_id'=>1,'codigo'=>'236525','descripcion'=>'Servicios'],['id'=>103,'core_empresa_id'=>1,'codigo'=>'236808','descripcion'=>'ICA']]);
        DB::statement('CREATE TABLE contab_movimientos (id INTEGER PRIMARY KEY AUTOINCREMENT,core_empresa_id INTEGER,core_tipo_transaccion_id INTEGER,core_tipo_doc_app_id INTEGER,consecutivo INTEGER,core_tercero_id INTEGER,contab_cuenta_id INTEGER,fecha TEXT,estado TEXT,valor_debito REAL,valor_credito REAL)');
        DB::statement('CREATE TABLE contab_retenciones (id INTEGER,descripcion TEXT,tasa_retencion REAL,cta_compras_id INTEGER,cta_compras_devol_id INTEGER,cta_ventas_id INTEGER,cta_ventas_devol_id INTEGER)');
        foreach ([[1,'Compras',101,2.5],[2,'Servicios',102,5],[3,'ICA servicios',103,0.414],[4,'Devolución compras',101,2.5]] as $r) {
            DB::table('contab_retenciones')->insert(['id'=>$r[0],'descripcion'=>$r[1],'cta_compras_id'=>$r[2],'cta_compras_devol_id'=>$r[2],'cta_ventas_id'=>0,'cta_ventas_devol_id'=>0,'tasa_retencion'=>$r[3]]);
        }
        DB::statement('CREATE TABLE contab_retenciones_reporte_config (core_empresa_id INTEGER,contab_retencion_id INTEGER,anio INTEGER,tipo_impuesto TEXT,concepto TEXT,codigo_municipio TEXT,actividad_ica TEXT)');
        foreach ([[1,'FUENTE','compras'],[2,'FUENTE','servicios'],[3,'ICA','ica'],[4,'FUENTE','devolucion_fuente']] as $c) {
            foreach ([2025,2026] as $anio) {
                DB::table('contab_retenciones_reporte_config')->insert(['core_empresa_id'=>1,'contab_retencion_id'=>$c[0],'anio'=>$anio,'tipo_impuesto'=>$c[1],'concepto'=>$c[2],'codigo_municipio'=>$c[1]==='ICA'?'20001':null,'actividad_ica'=>$c[1]==='ICA'?'4711':null]);
            }
        }
        DB::statement('CREATE TABLE contab_registros_retenciones (id INTEGER PRIMARY KEY AUTOINCREMENT,core_empresa_id INTEGER,core_tercero_id INTEGER,core_tipo_transaccion_id INTEGER,core_tipo_doc_app_id INTEGER,consecutivo INTEGER,fecha TEXT,tipo TEXT,estado TEXT,contab_retencion_id INTEGER,valor_base_retencion REAL,tasa_retencion REAL,valor REAL,tipo_persona_retenida TEXT,documento_tabla TEXT,documento_id INTEGER,numero_certificado TEXT)');
        $this->compra(1,10,1416); $this->compra(2,10,600); $this->compra(3,11,2380);
        $this->compra(4,10,999,25,'Anulado'); $this->compra(5,20,999,25,'Activo',2);
        $this->compra(6,10,999,25,'Activo',1,'2026-10-01',false);
        $this->compra(7,10,236,36); $this->compra(8,10,119,25,'Activo',1,'2025-12-01');
        $this->retencion(1,10,1,1000,2.5,25); $this->retencion(1,10,2,200,5,10);
        $this->retencion(3,11,1,2000,2.5,50); $this->retencion(3,11,3,2000,0.414,8.28);
        $this->retencion(4,10,1,999,2.5,24.975); $this->retencion(5,20,1,999,2.5,24.975,25,2);
        $this->retencion(7,10,4,200,2.5,5,36); $this->retencion(8,10,1,100,2.5,2.5,25,1,'2025-12-01');
        require_once __DIR__.'/../../database/migrations/2026_11_19_000001_create_retention_certificates.php';
        (new CreateRetentionCertificates)->up();
        $usuario=Mockery::mock(App\User::class.'[hasRole,can]');
        $usuario->id=5; $usuario->empresa_id=1;
        $usuario->shouldReceive('hasRole')->andReturn(true);$usuario->shouldReceive('can')->andReturn(true);
        Auth::setUser($usuario);
    }

    private function compra($id,$tercero,$valor,$trans=25,$estado='Activo',$empresa=1,$fecha='2026-10-01',$contabilizada=true)
    {
        DB::table('compras_doc_encabezados')->insert(['id'=>$id,'core_empresa_id'=>$empresa,'core_tercero_id'=>$tercero,'core_tipo_transaccion_id'=>$trans,'core_tipo_doc_app_id'=>17,'consecutivo'=>$id,'fecha'=>$fecha,'estado'=>$estado,'valor_total'=>$valor,'doc_proveedor_prefijo'=>'FC','doc_proveedor_consecutivo'=>(string)$id,'descripcion'=>'Compra '.$id]);
        if ($contabilizada) { DB::table('contab_movimientos')->insert(['core_empresa_id'=>$empresa,'core_tipo_transaccion_id'=>$trans,'core_tipo_doc_app_id'=>17,'consecutivo'=>$id,'core_tercero_id'=>$tercero,'contab_cuenta_id'=>0,'fecha'=>$fecha,'estado'=>$estado,'valor_debito'=>$valor,'valor_credito'=>0]); }
    }

    private function retencion($doc,$tercero,$ret,$base,$tasa,$valor,$trans=25,$empresa=1,$fecha='2026-10-01')
    {
        DB::table('contab_registros_retenciones')->insert(['core_empresa_id'=>$empresa,'core_tercero_id'=>$tercero,'core_tipo_transaccion_id'=>$trans,'core_tipo_doc_app_id'=>17,'consecutivo'=>$doc,'fecha'=>$fecha,'tipo'=>'practicada','estado'=>'Activo','contab_retencion_id'=>$ret,'valor_base_retencion'=>$base,'tasa_retencion'=>$tasa,'valor'=>$valor,'tipo_persona_retenida'=>'juridica','documento_tabla'=>'compras_doc_encabezados','documento_id'=>$doc,'numero_certificado'=>'']);
    }

    private function filtros(array $extra=[])
    {
        return FiltrosCertificados::normalizar(array_merge(['periodo'=>'bimestral','anio'=>2026,'bimestre'=>5,
            'ciudad_consignacion'=>'Valledupar','firma_autorizada_id'=>1,
            'firma_fuente'=>'computador','tercero_id'=>10],$extra));
    }

    public function tearDown()
    {
        foreach (DB::table('contab_certificados_retenciones')->whereNotNull('pdf_path')->lists('pdf_path') as $path) { Storage::disk('local')->delete($path); }
        Storage::disk('local')->delete('firmas_autorizadas/certificados_prueba.png');
        DB::purge('certificados_test');DB::setDefaultConnection($this->conexion);
        parent::tearDown();
    }

    public function test_suma_compras_una_vez_por_documento_con_tarifas_y_devoluciones()
    {
        $datos=(new DatosCertificadosService)->generar(1,$this->filtros());
        $this->assertCount(1,$datos);$c=$datos[0];
        $this->assertCount(2,$c['documentos']);$this->assertCount(3,$c['tasas']);
        $this->assertEquals(118000,$c['compra_total']);$this->assertEquals(100000,$c['base_total']);
        $this->assertEquals(3000,$c['retencion_total']);$this->assertSame([],$c['bloqueos']);
        $todas=(new DatosCertificadosService)->generar(1,$this->filtros(['compras'=>'todas']));
        $this->assertCount(3,$todas[0]['documentos']);$this->assertEquals(178000,$todas[0]['compra_total']);
        $this->assertEquals(3000,$todas[0]['retencion_total']);
    }

    public function test_filtra_grupos_terceros_y_empresa_y_separa_anios()
    {
        $s=new DatosCertificadosService;
        $this->assertCount(2,$s->generar(1,$this->filtros(['tercero_id'=>''])));
        $grupo=$s->generar(1,$this->filtros(['tercero_id'=>'','grupo_id'=>2]));
        $this->assertCount(1,$grupo);$this->assertEquals(11,$grupo[0]['tercero']['id']);
        $this->assertCount(0,$s->generar(1,$this->filtros(['tercero_id'=>20])));
        $this->assertCount(0,$s->generar(1,$this->filtros(['tercero_id'=>'','grupo_id'=>3])));
        $anios=$s->generar(1,$this->filtros(['periodo'=>'fechas','fecha_desde'=>'2025-12-01','fecha_hasta'=>'2026-10-31']));
        $this->assertCount(2,$anios);$this->assertSame('2025',$anios[0]['anio']);$this->assertSame('2025-12-31',$anios[0]['hasta']);
    }

    public function test_ica_por_municipio_y_conversion_tarifa_a_por_mil()
    {
        $f=$this->filtros(['tipo_impuesto'=>'ICA','tercero_id'=>11,'codigo_municipio'=>'20001','municipio'=>'Valledupar','norma_ica'=>'Acuerdo municipal']);
        $c=(new DatosCertificadosService)->generar(1,$f);
        $this->assertCount(1,$c);$this->assertEquals(828,$c[0]['retencion_total']);
        $this->assertEquals(0.414,$c[0]['tasas'][0]['tarifa']);
        $s=new CertificadosRetencionesService;$r=$s->previsualizar(1,5,$f)[0];
        $html=$s->html($s->obtener($r['token'],1,5));
        $this->assertContains('4,1400',$html);$this->assertContains('4711',$html);
        DB::table('contab_retenciones_reporte_config')->where('contab_retencion_id',3)->update(['codigo_municipio'=>null]);
        $c=(new DatosCertificadosService)->generar(1,$f);$this->assertNotEmpty($c[0]['bloqueos']);
        $f['confirmar_municipio']=1;$c=(new DatosCertificadosService)->generar(1,$f);$this->assertSame([],$c[0]['bloqueos']);
        $f['codigo_municipio']='11001';$f['confirmar_municipio']=0;
        DB::table('contab_retenciones_reporte_config')->where('contab_retencion_id',3)->update(['codigo_municipio'=>'20001']);
        $this->assertCount(0,(new DatosCertificadosService)->generar(1,$f));
    }

    public function test_periodos_validan_bimestre_fechas_y_requisitos()
    {
        $f=$this->filtros();$this->assertSame('2026-09-01',$f['fecha_desde']);$this->assertSame('2026-10-31',$f['fecha_hasta']);
        $f=$this->filtros(['periodo'=>'anual']);$this->assertSame('2026-01-01',$f['fecha_desde']);$this->assertSame('2026-12-31',$f['fecha_hasta']);
        $f=$this->filtros(['anio'=>2024,'bimestre'=>1]);$this->assertSame('2024-02-29',$f['fecha_hasta']);
        foreach ([['bimestre'=>7],['periodo'=>'fechas','fecha_desde'=>'2026-10-31','fecha_hasta'=>'2026-10-01'],['tipo_impuesto'=>'IVA'],['grupo_id'=>2],['firma_autorizada_id'=>''],['tipo_impuesto'=>'ICA']] as $invalido) {
            try {$this->filtros($invalido);$this->fail('Se aceptó un filtro inválido');}catch(InvalidArgumentException $e){$this->assertNotEmpty($e->getMessage());}
        }
    }

    public function test_pdf_real_inmutable_y_pertenencia_a_empresa_usuario()
    {
        $s=new CertificadosRetencionesService;$r=$s->previsualizar(1,5,$this->filtros())[0];
        $this->assertContains('PREVISUALIZACIÓN',$s->html($s->obtener($r['token'],1,5)));
        $pdf=$s->emitir($r['token'],1,5);$this->assertSame('%PDF',substr($pdf['bytes'],0,4));
        $this->assertContains('12310',$pdf['registro']->html);$this->assertNotContains('NO EMITIDO',$pdf['registro']->html);
        DB::table('core_terceros')->where('id',10)->update(['descripcion'=>'CAMBIADO']);
        $otro=$s->emitir($r['token'],1,5);$this->assertSame($pdf['bytes'],$otro['bytes']);
        $this->assertNotContains('CAMBIADO',$s->html($s->obtener($r['token'],1,5)));
        foreach ([[2,5],[1,6]] as $contexto) {
            try {$s->obtener($r['token'],$contexto[0],$contexto[1]);$this->fail('Permitió leer otro contexto');}
            catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(404,$e->getStatusCode());}
        }
    }

    public function test_envio_individual_sin_correo_real_registra_fallos_reintenta_y_evitar_duplicados()
    {
        $s=new CertificadosCorreoPrueba;$r=$s->previsualizar(1,5,$this->filtros())[0];
        $s->fallar=true;
        try{$s->enviar($r['token'],1,5);$this->fail('Debió fallar');}catch(RuntimeException $e){$this->assertSame('Fallido',DB::table('contab_certificados_envios')->value('estado'));}
        $s->fallar=false;$s->enviar($r['token'],1,5);
        $this->assertSame('proveedor10@example.com',$s->email);$this->assertSame('%PDF',substr($s->adjunto,0,4));
        $this->assertSame(1,DB::table('contab_certificados_envios')->where('estado','Enviado')->count());
        try{$s->enviar($r['token'],1,5);$this->fail('Duplicó envío');}catch(InvalidArgumentException $e){$this->assertContains('ya fue enviado',$e->getMessage());}
        $this->assertSame(2,DB::table('contab_certificados_envios')->count());
    }

    public function test_impide_envio_sin_firma_o_email_y_emision_sin_concepto()
    {
        $s=new CertificadosCorreoPrueba;
        try{$s->previsualizar(1,5,$this->filtros(['firma_fuente'=>'manual','firma_autorizada_id'=>2]));$this->fail('Aceptó firma manual sin imagen');}
        catch(InvalidArgumentException $e){$this->assertContains('imagen de firma adjunta',$e->getMessage());}
        DB::table('core_terceros')->where('id',10)->update(['email'=>'invalido']);
        $r=$s->previsualizar(1,5,$this->filtros())[0];
        try{$s->enviar($r['token'],1,5);$this->fail('Envío sin email');}catch(InvalidArgumentException $e){$this->assertContains('correo válido',$e->getMessage());}
        DB::table('contab_retenciones_reporte_config')->where('contab_retencion_id',1)->update(['concepto'=>null]);
        DB::table('contab_retenciones')->where('id',1)->update(['descripcion'=>'']);
        $r=$s->previsualizar(1,5,$this->filtros())[0];
        try{$s->emitir($r['token'],1,5);$this->fail('Emitió sin concepto');}catch(InvalidArgumentException $e){$this->assertContains('concepto de la retención',$e->getMessage());}
        $this->assertSame(0,DB::table('contab_certificados_envios')->count());
    }

    public function test_controlador_muestra_previsualizacion_y_rechaza_filtros_ajenos()
    {
        $controller=new App\Http\Controllers\Contabilidad\CertificadosRetencionesController;
        $vista=$controller->index();$this->assertSame('contabilidad.certificados.index',$vista->name());
        $this->assertCount(2,$vista->getData()['terceros']);$this->assertCount(2,$vista->getData()['grupos']);
        $this->assertCount(2,$vista->getData()['firmas']);
        $vista=$controller->previsualizar(new Request($this->filtros()))->render();
        $this->assertContains('Proveedor A',$vista);$this->assertContains('Emitir / descargar PDF',$vista);
        try{$controller->previsualizar(new Request($this->filtros(['tercero_id'=>20])));$this->fail('Acceso ajeno');}
        catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
    }

    public function test_descarga_lote_contiene_pdf_individual_y_rechaza_tokens_ajenos()
    {
        $s=new CertificadosRetencionesService;$registros=$s->previsualizar(1,5,$this->filtros(['tercero_id'=>'']));
        $tokens=array_column($registros,'token');
        $path=$s->descargarLote($tokens,1,5);
        try {
            $zip=new ZipArchive;$this->assertTrue($zip->open($path));$this->assertEquals(2,$zip->numFiles);
            for($i=0;$i<$zip->numFiles;$i++){$this->assertSame('%PDF',substr($zip->getFromIndex($i),0,4));}
            $zip->close();
        } finally {unlink($path);}
        $this->assertSame(2,DB::table('contab_certificados_retenciones')->where('estado','Emitido')->count());
        try{$s->descargarLote($tokens,2,5);$this->fail('Permitió descarga ajena');}
        catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(404,$e->getStatusCode());}
    }

    public function test_concepto_descriptivo_no_exige_casilla_de_declaracion()
    {
        DB::table('contab_retenciones_reporte_config')->where('contab_retencion_id',1)->update(['concepto'=>null]);
        $datos=(new DatosCertificadosService)->generar(1,$this->filtros());
        $this->assertSame([],$datos[0]['bloqueos']);
        $this->assertSame('Compras',$datos[0]['tasas'][0]['concepto']);
    }

    public function test_no_duplica_retencion_por_cambios_historicos_del_catalogo_contable()
    {
        DB::table('contab_retenciones')->update(['cta_compras_id'=>0,'cta_compras_devol_id'=>0]);
        DB::table('contab_movimientos')->insert(['core_empresa_id'=>1,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>17,
            'consecutivo'=>1,'core_tercero_id'=>10,'contab_cuenta_id'=>101,'fecha'=>'2026-10-01','estado'=>'Activo','valor_debito'=>0,'valor_credito'=>-25]);
        $datos=(new DatosCertificadosService)->generar(1,$this->filtros());
        $this->assertEquals(3000,$datos[0]['retencion_total']);$this->assertEquals(100000,$datos[0]['base_total']);
        $this->assertSame([],$datos[0]['bloqueos']);
    }

    public function test_no_emite_retencion_sin_base_verificable()
    {
        DB::table('contab_registros_retenciones')->where('id',1)->update(['valor_base_retencion'=>null]);
        $s=new CertificadosRetencionesService;$r=$s->previsualizar(1,5,$this->filtros())[0];
        try{$s->emitir($r['token'],1,5);$this->fail('Emitió sin base');}
        catch(InvalidArgumentException $e){$this->assertContains('sin base o tarifa verificable',$e->getMessage());}
    }

    public function test_firma_del_catalogo_sustituye_datos_enviados_y_firma_pdf_manual()
    {
        $s=new CertificadosCorreoPrueba;
        $f=$this->filtros(['firma_fuente'=>'manual','responsable'=>'Nombre falso','identificacion_responsable'=>'999','firma_imagen'=>'imagen falsa']);
        $r=$s->previsualizar(1,5,$f)[0];$datos=$r['datos']['filtros'];
        $this->assertSame('Proveedor A',$datos['responsable']);$this->assertEquals('12310',$datos['identificacion_responsable']);
        $this->assertSame('Contador',$datos['titulo_responsable']);$this->assertStringStartsWith('data:image/png;base64,',$datos['firma_imagen']);
        $pdf=$s->emitir($r['token'],1,5);$this->assertSame('%PDF',substr($pdf['bytes'],0,4));
        $this->assertContains('Contador',$pdf['registro']->html);$this->assertNotContains('Nombre falso',$pdf['registro']->html);
        $s->enviar($r['token'],1,5);$this->assertSame('Enviado',DB::table('contab_certificados_envios')->value('estado'));
    }

    public function test_rechaza_firmas_ajenas_inactivas_y_archivos_ausentes()
    {
        $s=new CertificadosRetencionesService;
        foreach([3,4,5,999] as $id){
            try{$s->previsualizar(1,5,$this->filtros(['firma_autorizada_id'=>$id]));$this->fail('Aceptó firma no autorizada');}
            catch(InvalidArgumentException $e){$this->assertContains('firma autorizada activa',$e->getMessage());}
        }
        DB::table('core_firmas_autorizadas')->where('id',1)->update(['imagen'=>'no_existe.png']);
        foreach(['manual','ica'] as $modo){
            $extra=['firma_fuente'=>'manual'];
            if($modo==='ica'){$extra=['firma_fuente'=>'computador','tipo_impuesto'=>'ICA','codigo_municipio'=>'20001','municipio'=>'Valledupar','norma_ica'=>'Acuerdo municipal'];}
            try{$s->previsualizar(1,5,$this->filtros($extra));$this->fail('Aceptó archivo inexistente');}
            catch(InvalidArgumentException $e){$this->assertContains('imagen de firma adjunta',$e->getMessage());}
        }
        $r=$s->previsualizar(1,5,$this->filtros(['firma_autorizada_id'=>2]))[0];
        $this->assertSame('',$r['datos']['filtros']['firma_imagen']);
        $this->assertSame(1,DB::table('contab_certificados_retenciones')->count());
    }

    public function test_cambio_de_firma_impide_emision_y_conserva_pdf_emitido()
    {
        $s=new CertificadosRetencionesService;$r=$s->previsualizar(1,5,$this->filtros())[0];
        DB::table('core_firmas_autorizadas')->where('id',1)->update(['titulo_tercero'=>'Otro cargo']);
        try{$s->emitir($r['token'],1,5);$this->fail('Emitió con firma desactualizada');}
        catch(InvalidArgumentException $e){$this->assertContains('firma autorizada cambió',$e->getMessage());}
        $r=$s->previsualizar(1,5,$this->filtros())[0];$pdf=$s->emitir($r['token'],1,5);
        DB::table('core_firmas_autorizadas')->where('id',1)->update(['estado'=>'Inactivo']);
        $this->assertSame($pdf['bytes'],$s->emitir($r['token'],1,5)['bytes']);
    }

    public function test_anulacion_posterior_exige_nueva_previsualizacion_y_no_emite()
    {
        $s=new CertificadosRetencionesService;$r=$s->previsualizar(1,5,$this->filtros())[0];
        DB::table('compras_doc_encabezados')->where('id',1)->update(['estado'=>'Anulado']);
        try{$s->emitir($r['token'],1,5);$this->fail('Emitió una operación anulada');}
        catch(InvalidArgumentException $e){$this->assertContains('datos cambiaron',$e->getMessage());}
        $this->assertSame(0,DB::table('contab_certificados_retenciones')->where('estado','Emitido')->count());
    }

    public function test_previsualizacion_vencida_y_sin_permiso_no_permiten_operar()
    {
        $s=new CertificadosRetencionesService;$r=$s->previsualizar(1,5,$this->filtros())[0];
        DB::table('contab_certificados_retenciones')->where('id',$r['id'])->update(['created_at'=>date('Y-m-d H:i:s',time()-90000)]);
        try{$s->obtener($r['token'],1,5);$this->fail('Aceptó consulta vencida');}
        catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(410,$e->getStatusCode());}
        $usuario=Mockery::mock(App\User::class.'[hasRole,can]');$usuario->id=5;$usuario->empresa_id=1;
        $usuario->shouldReceive('hasRole')->andReturn(false);$usuario->shouldReceive('can')->andReturn(false);Auth::setUser($usuario);
        try{(new App\Http\Controllers\Contabilidad\CertificadosRetencionesController)->index();$this->fail('Aceptó usuario sin permiso');}
        catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(403,$e->getStatusCode());}
    }

    public function test_migracion_registra_menu_permisos_y_revierte_sin_duplicados()
    {
        DB::statement('CREATE TABLE sys_aplicaciones (id INTEGER, app TEXT, descripcion TEXT)');
        DB::table('sys_aplicaciones')->insert(['id'=>14,'app'=>'contabilidad','descripcion'=>'Contabilidad']);
        DB::statement('CREATE TABLE permissions (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,descripcion TEXT,core_app_id INTEGER,modelo_id INTEGER,url TEXT,parent INTEGER,orden INTEGER,enabled INTEGER,fa_icon TEXT,created_at TEXT,updated_at TEXT)');
        DB::table('permissions')->insert(['name'=>'procesos','descripcion'=>'Procesos','core_app_id'=>14,'enabled'=>1,'parent'=>0]);
        DB::statement('CREATE TABLE roles (id INTEGER,name TEXT)');
        DB::table('roles')->insert(['id'=>1,'name'=>'Administrador']);
        DB::statement('CREATE TABLE role_has_permissions (role_id INTEGER,permission_id INTEGER,orden INTEGER)');
        require_once __DIR__.'/../../database/migrations/2026_11_19_000002_register_retention_certificates_process.php';
        $m=new RegisterRetentionCertificatesProcess;$m->up();$m->up();
        $this->assertSame(3,DB::table('permissions')->count());$this->assertSame(2,DB::table('role_has_permissions')->count());
        $menu=DB::table('permissions')->where('name','contab_certificados_retenciones')->first();
        $this->assertEquals(1,$menu->enabled);$this->assertEquals(1,$menu->parent);
        DB::statement('CREATE TABLE sys_modelos (id INTEGER,descripcion TEXT)');
        DB::statement('CREATE TABLE sys_reportes (id INTEGER,core_app_id INTEGER,estado TEXT)');
        config(['configuracion.usar_mensajes_internos'=>false,'configuracion.usuario_en_mora'=>'false']);
        $usuario=Mockery::mock(App\User::class.'[hasRole,can,hasPermissionTo]');$usuario->id=5;$usuario->empresa_id=1;$usuario->name='Administrador';
        $usuario->shouldReceive('hasRole')->andReturn(true);$usuario->shouldReceive('can')->andReturn(true);$usuario->shouldReceive('hasPermissionTo')->andReturn(true);Auth::setUser($usuario);
        request()->merge(['id'=>14]);
        $html=(new App\Http\Controllers\Contabilidad\CertificadosRetencionesController)->index()->render();
        $this->assertContains('cert-filtros',$html);$this->assertContains('certificados_retenciones.js',$html);
        $m->down();$this->assertSame(1,DB::table('permissions')->count());$this->assertSame(0,DB::table('role_has_permissions')->count());
    }
}

class CertificadosCorreoPrueba extends CertificadosRetencionesService
{
    public $fallar=false;
    public $email;
    public $adjunto;
    protected function entregar($email,array $c,array $pdf)
    {
        if($this->fallar){throw new RuntimeException('Fallo simulado');}
        $this->email=$email;$this->adjunto=$pdf['bytes'];
    }
}
