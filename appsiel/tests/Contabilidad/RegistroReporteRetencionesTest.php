<?php

use Illuminate\Support\Facades\DB;

class RegistroReporteRetencionesTest extends TestCase
{
    public function test_migracion_registra_el_formulario_estandar_sin_duplicar_campos_ni_borrar_campos_compartidos()
    {
        $anterior=DB::getDefaultConnection();
        config(['database.connections.registro_retenciones_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('registro_retenciones_test');
        try {
            DB::statement('CREATE TABLE sys_aplicaciones (id INTEGER, app TEXT)');
            DB::statement('CREATE TABLE sys_reportes (id INTEGER PRIMARY KEY AUTOINCREMENT, core_app_id INTEGER, descripcion TEXT, url_form_action TEXT, estado TEXT, created_at TEXT, updated_at TEXT)');
            DB::statement('CREATE TABLE sys_campos (id INTEGER PRIMARY KEY AUTOINCREMENT, descripcion TEXT, tipo TEXT, name TEXT, opciones TEXT, value TEXT, atributos TEXT, definicion TEXT, requerido INTEGER, editable INTEGER, unico INTEGER, created_at TEXT, updated_at TEXT)');
            DB::statement('CREATE TABLE sys_reporte_tiene_campos (core_reporte_id INTEGER, core_campo_id INTEGER, orden INTEGER)');
            DB::table('sys_aplicaciones')->insert(['id'=>14,'app'=>'contabilidad']);
            DB::table('sys_campos')->insert(['name'=>'fecha_desde','definicion'=>'compartido']);
            require_once __DIR__.'/../../database/migrations/2026_11_13_000004_register_retention_reports.php';
            $migracion=new RegisterRetentionReports;
            $migracion->up();
            $migracion->up();
            $this->assertSame(1,DB::table('sys_reportes')->count());
            $this->assertSame(11,DB::table('sys_reporte_tiene_campos')->count());
            $this->assertSame(12,DB::table('sys_campos')->count());
            $impuesto=DB::table('sys_campos')->where('name','tipo_impuesto')->first();
            $this->assertSame([''=>'Todos','FUENTE'=>'Fuente','ICA'=>'ICA','IVA'=>'IVA','SIN_CLASIFICAR'=>'Sin clasificar'],json_decode($impuesto->opciones,true));
            require_once __DIR__.'/../../database/migrations/2026_11_13_000005_add_retention_report_third_party_detail_filter.php';
            $filtro=new AddRetentionReportThirdPartyDetailFilter;
            $filtro->up();
            $filtro->up();
            $this->assertSame(12,DB::table('sys_reporte_tiene_campos')->count());
            $campo=DB::table('sys_campos')->where('name','detalla_terceros')->first();
            $this->assertSame(['Si'=>'Sí','No'=>'No'],json_decode($campo->opciones,true));
            $filtro->down();
            $this->assertSame(11,DB::table('sys_reporte_tiene_campos')->count());
            $migracion->down();
            $this->assertSame(0,DB::table('sys_reportes')->count());
            $this->assertSame(0,DB::table('sys_reporte_tiene_campos')->count());
            $this->assertSame(1,DB::table('sys_campos')->count());
            $this->assertSame('compartido',DB::table('sys_campos')->value('definicion'));
        } finally {
            DB::purge('registro_retenciones_test'); DB::setDefaultConnection($anterior);
        }
    }
}
