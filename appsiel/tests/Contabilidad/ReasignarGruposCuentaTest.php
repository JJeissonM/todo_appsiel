<?php

use App\Http\Controllers\Contabilidad\ContabReportesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReasignarGruposCuentaTest extends TestCase
{
    private $conexion;

    public function setUp()
    {
        parent::setUp();
        $this->conexion = DB::getDefaultConnection();
        config(['database.connections.grupos_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('grupos_test');
        DB::statement('CREATE TABLE contab_cuentas (id INTEGER PRIMARY KEY, core_empresa_id INTEGER, contab_cuenta_clase_id INTEGER, contab_cuenta_grupo_id INTEGER, codigo TEXT, descripcion TEXT, modificado_por TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE contab_cuenta_grupos (id INTEGER PRIMARY KEY, core_empresa_id INTEGER, contab_cuenta_clase_id INTEGER, grupo_padre_id INTEGER, codigo TEXT, descripcion TEXT)');
        DB::table('contab_cuentas')->insert([
            ['id'=>1,'core_empresa_id'=>1,'contab_cuenta_clase_id'=>1,'contab_cuenta_grupo_id'=>10,'codigo'=>'150405','descripcion'=>'Terrenos'],
            ['id'=>2,'core_empresa_id'=>2,'contab_cuenta_clase_id'=>1,'contab_cuenta_grupo_id'=>30,'codigo'=>'150405','descripcion'=>'Ajena']
        ]);
        DB::table('contab_cuenta_grupos')->insert([
            ['id'=>10,'core_empresa_id'=>1,'contab_cuenta_clase_id'=>1,'grupo_padre_id'=>0,'codigo'=>'15','descripcion'=>'PPE'],
            ['id'=>20,'core_empresa_id'=>1,'contab_cuenta_clase_id'=>1,'grupo_padre_id'=>10,'codigo'=>'1504','descripcion'=>'Terrenos'],
            ['id'=>30,'core_empresa_id'=>2,'contab_cuenta_clase_id'=>1,'grupo_padre_id'=>0,'codigo'=>'15','descripcion'=>'Ajeno'],
            ['id'=>40,'core_empresa_id'=>1,'contab_cuenta_clase_id'=>2,'grupo_padre_id'=>0,'codigo'=>'21','descripcion'=>'Pasivos']
        ]);
        $user = new App\User();$user->empresa_id=1;$user->email='contador@example.test';Auth::setUser($user);
    }

    public function tearDown()
    {
        DB::disconnect('grupos_test');DB::setDefaultConnection($this->conexion);
        parent::tearDown();
    }

    private function guardar($cuenta=1, $grupo=20, $anterior=10)
    {
        return (new ContabReportesController())->reasignar_grupos_cuentas_save(Request::create('/', 'POST', ['grupo_id'=>$grupo,'grupo_anterior_id'=>$anterior]),$cuenta);
    }

    public function testActualizaSoloLaCuentaConfirmadaYRegistraElUsuario()
    {
        $response=$this->guardar();
        $this->assertEquals(200,$response->getStatusCode());
        $cuenta=DB::table('contab_cuentas')->where('id',1)->first();
        $this->assertEquals(20,$cuenta->contab_cuenta_grupo_id);
        $this->assertEquals('contador@example.test',$cuenta->modificado_por);
        $this->assertEquals(30,DB::table('contab_cuentas')->where('id',2)->value('contab_cuenta_grupo_id'));
    }

    public function testRechazaGruposDeOtraEmpresaOClase()
    {
        $this->assertEquals(422,$this->guardar(1,30)->getStatusCode());
        $this->assertEquals(422,$this->guardar(1,40)->getStatusCode());
        $this->assertEquals(10,DB::table('contab_cuentas')->where('id',1)->value('contab_cuenta_grupo_id'));
    }

    public function testNoActualizaCuentasDeOtraEmpresa()
    {
        $this->assertEquals(404,$this->guardar(2)->getStatusCode());
        $this->assertEquals(30,DB::table('contab_cuentas')->where('id',2)->value('contab_cuenta_grupo_id'));
    }

    public function testNoSobrescribeCambiosDeOtroUsuario()
    {
        DB::table('contab_cuentas')->where('id',1)->update(['contab_cuenta_grupo_id'=>20]);
        $this->assertEquals(409,$this->guardar(1,10,10)->getStatusCode());
        $this->assertEquals(20,DB::table('contab_cuentas')->where('id',1)->value('contab_cuenta_grupo_id'));
    }

    public function testRechazaUnGrupoVacio()
    {
        $this->assertEquals(422,$this->guardar(1,'')->getStatusCode());
    }

    public function testCargaGruposUnaSolaVezYPresentaSuJerarquia()
    {
        DB::enableQueryLog();DB::flushQueryLog();
        $data=(new ContabReportesController())->reasignar_grupos_cuentas_form()->getData();
        $this->assertCount(2,DB::getQueryLog());DB::disableQueryLog();
        $this->assertCount(1,$data['cuentas']);
        $this->assertEquals('PPE > Terrenos',$data['grupos_por_clase'][1][20]);
        $this->assertArrayNotHasKey(30,$data['grupos_por_clase'][1]);
        $this->assertArrayNotHasKey(40,$data['grupos_por_clase'][1]);
    }
}
