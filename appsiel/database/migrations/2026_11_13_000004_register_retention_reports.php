<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Contabilidad\Services\Retenciones\ConceptosDeclaracion;

class RegisterRetentionReports extends Migration
{
    private $marker = 'contab_retenciones_reportes_20261113';

    public function up()
    {
        $appId = DB::table('sys_aplicaciones')->where('app', 'contabilidad')->value('id');
        if (!$appId) { throw new RuntimeException('Debe existir la aplicación Contabilidad para registrar el reporte de retenciones.'); }
        $now = date('Y-m-d H:i:s');
        $reportId = DB::table('sys_reportes')->where('url_form_action', 'contab_reporte_retenciones')->value('id');
        if (!$reportId) {
            $reportId = DB::table('sys_reportes')->insertGetId([
                'core_app_id' => $appId, 'descripcion' => 'Retenciones: Fuente, ICA e IVA',
                'url_form_action' => 'contab_reporte_retenciones', 'estado' => 'Activo', 'created_at' => $now, 'updated_at' => $now
            ]);
        }
        $fields = [
            ['Fecha desde', 'date', 'fecha_desde', '', 1], ['Fecha hasta', 'date', 'fecha_hasta', '', 1],
            ['Impuesto', 'select', 'tipo_impuesto', [''=>'Todos', 'FUENTE'=>'Fuente', 'ICA'=>'ICA', 'IVA'=>'IVA', 'SIN_CLASIFICAR'=>'Sin clasificar'], 0],
            ['Naturaleza', 'select', 'naturaleza', ['practicada'=>'Practicadas', 'sufrida'=>'Sufridas'], 1],
            ['Presentación', 'select', 'vista', ['completo'=>'Completo', 'resumen'=>'Resumen para declaración', 'terceros'=>'Por tercero', 'detalle'=>'Por documento'], 1],
            ['Persona', 'select', 'tipo_persona', [''=>'Todas', 'natural'=>'Natural', 'juridica'=>'Jurídica', 'sin_clasificar'=>'Sin clasificar'], 0],
            ['Tercero', 'select', 'tercero_id', 'model_App\\Core\\Tercero', 0],
            ['Retención', 'select', 'retencion_id', 'model_App\\Contabilidad\\Retencion', 0],
            ['Concepto fiscal', 'select', 'concepto', ConceptosDeclaracion::opciones(), 0],
            ['Municipio ICA (código DANE)', 'text', 'codigo_municipio', '', 0],
            ['Tarifa en porcentaje', 'text', 'tarifa', '', 0]
        ];
        foreach ($fields as $index => $field) {
            $fieldId = DB::table('sys_campos')->where('name', $field[2])->where('definicion', $this->marker)->value('id');
            if (!$fieldId) {
                $fieldId = DB::table('sys_campos')->insertGetId([
                    'descripcion'=>$field[0], 'tipo'=>$field[1], 'name'=>$field[2],
                    'opciones'=>is_array($field[3]) ? json_encode($field[3], JSON_UNESCAPED_UNICODE) : $field[3],
                    'value'=>'null', 'atributos'=>json_encode(['class'=>'form-control', 'id'=>$field[2]]),
                    'definicion'=>$this->marker, 'requerido'=>$field[4], 'editable'=>1, 'unico'=>0,
                    'created_at'=>$now, 'updated_at'=>$now
                ]);
            }
            if (!DB::table('sys_reporte_tiene_campos')->where('core_reporte_id', $reportId)->where('core_campo_id', $fieldId)->exists()) {
                DB::table('sys_reporte_tiene_campos')->insert(['core_reporte_id'=>$reportId,'core_campo_id'=>$fieldId,'orden'=>$index+1]);
            }
        }
        foreach (['contab_reporte_retenciones'=>'Consultar reportes de retenciones', 'contab_configurar_retenciones'=>'Clasificar retenciones para declaraciones'] as $nombre => $descripcion) {
            $this->permiso($appId, $nombre, $descripcion, $now);
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function permiso($appId, $nombre, $descripcion, $now)
    {
        if (!Schema::hasTable('permissions')) { return; }
        $permissionId = DB::table('permissions')->where('name', $nombre)->value('id');
        if (!$permissionId) {
            $data = ['name'=>$nombre,'descripcion'=>$descripcion,'core_app_id'=>$appId,'modelo_id'=>0,
                'url'=>'web','parent'=>0,'orden'=>99,'enabled'=>0,'fa_icon'=>'file-text-o','created_at'=>$now,'updated_at'=>$now];
            if (Schema::hasColumn('permissions','guard_name')) { $data['guard_name']='web'; }
            $permissionId = DB::table('permissions')->insertGetId($data);
        }
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) { return; }
        foreach (DB::table('roles')->whereIn('name',['SuperAdmin','Administrador'])->get() as $role) {
            $clave=['role_id'=>$role->id,'permission_id'=>$permissionId];
            if (!DB::table('role_has_permissions')->where($clave)->exists()) {
                if (Schema::hasColumn('role_has_permissions','orden')) { $clave['orden']=0; }
                DB::table('role_has_permissions')->insert($clave);
            }
        }
    }

    public function down()
    {
        $reportId=DB::table('sys_reportes')->where('url_form_action','contab_reporte_retenciones')->value('id');
        if ($reportId) {
            DB::table('sys_reporte_tiene_campos')->where('core_reporte_id',$reportId)->delete();
            DB::table('sys_reportes')->where('id',$reportId)->delete();
        }
        foreach (DB::table('sys_campos')->where('definicion',$this->marker)->get() as $campo) {
            if (!DB::table('sys_reporte_tiene_campos')->where('core_campo_id',$campo->id)->exists()) {
                DB::table('sys_campos')->where('id',$campo->id)->delete();
            }
        }
        foreach (['contab_reporte_retenciones','contab_configurar_retenciones'] as $nombre) {
            if (!Schema::hasTable('permissions')) { continue; }
            $id=DB::table('permissions')->where('name',$nombre)->value('id');
            if ($id && Schema::hasTable('role_has_permissions')) { DB::table('role_has_permissions')->where('permission_id',$id)->delete(); }
            if ($id) { DB::table('permissions')->where('id',$id)->delete(); }
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
