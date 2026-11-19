<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegisterRetentionCertificatesProcess extends Migration
{
    public function up()
    {
        $appId=DB::table('sys_aplicaciones')->where('app','contabilidad')->value('id');
        if (!$appId) { throw new RuntimeException('Debe existir la aplicación Contabilidad.'); }
        $parent=DB::table('permissions')->where('core_app_id',$appId)->where('descripcion','Procesos')->where('enabled',1)->value('id');
        foreach (['contab_certificados_retenciones'=>'Certificados de Retefuente e ICA',
            'contab_enviar_certificados_retenciones'=>'Enviar certificados de retención por correo'] as $nombre=>$descripcion) {
            $id=DB::table('permissions')->where('name',$nombre)->value('id');
            if (!$id) {
                $menu=$nombre==='contab_certificados_retenciones';
                $data=['name'=>$nombre,'descripcion'=>$descripcion,'core_app_id'=>$appId,'modelo_id'=>0,
                    'url'=>'contab_certificados_retenciones','parent'=>$parent ?: 0,'orden'=>95,
                    'enabled'=>$menu ? 1 : 0,'fa_icon'=>'file-pdf-o',
                    'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')];
                if (Schema::hasColumn('permissions','guard_name')) { $data['guard_name']='web'; }
                $id=DB::table('permissions')->insertGetId($data);
            }
            foreach (DB::table('roles')->whereIn('name',['SuperAdmin','Administrador'])->get() as $rol) {
                if (!DB::table('role_has_permissions')->where('role_id',$rol->id)->where('permission_id',$id)->exists()) {
                    $p=['role_id'=>$rol->id,'permission_id'=>$id];
                    if (Schema::hasColumn('role_has_permissions','orden')) { $p['orden']=0; }
                    DB::table('role_has_permissions')->insert($p);
                }
            }
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        foreach (['contab_certificados_retenciones','contab_enviar_certificados_retenciones'] as $nombre) {
            $id=DB::table('permissions')->where('name',$nombre)->where('url','contab_certificados_retenciones')->value('id');
            if (!$id) { continue; }
            DB::table('role_has_permissions')->where('permission_id',$id)->delete();
            if (Schema::hasTable('user_has_permissions')) { DB::table('user_has_permissions')->where('permission_id',$id)->delete(); }
            DB::table('permissions')->where('id',$id)->delete();
        }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
