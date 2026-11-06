<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAgrupacionesColumnasCalificaciones extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sga_calificaciones_encabezados')) return;
        if (!Schema::hasTable('sga_agrupaciones_calificacion')) Schema::create('sga_agrupaciones_calificacion', function (Blueprint $t) {
            $t->increments('id'); $t->unsignedInteger('empresa_id'); $t->string('titulo', 150);
            $t->decimal('peso', 6, 2); $t->unsignedInteger('anio'); $t->unsignedInteger('periodo_id')->nullable();
            $t->string('estado', 15)->default('Borrador'); $t->boolean('bloqueado')->default(false); $t->timestamps();
            $t->index(['empresa_id', 'anio', 'periodo_id'], 'sga_agrupaciones_alcance');
        });
        if (!Schema::hasTable('sga_agrupacion_columnas')) Schema::create('sga_agrupacion_columnas', function (Blueprint $t) {
            $t->increments('id'); $t->unsignedInteger('agrupacion_id'); $t->string('columna_calificacion', 3);
            $t->string('label', 60); $t->unsignedInteger('orden'); $t->timestamps();
            $t->unique(['agrupacion_id', 'columna_calificacion'], 'sga_agrupacion_columna_unica');
            $t->foreign('agrupacion_id')->references('id')->on('sga_agrupaciones_calificacion');
        });
        if (!Schema::hasColumn('sga_calificaciones_encabezados', 'agrupacion_columna_id')) Schema::table('sga_calificaciones_encabezados', function (Blueprint $t) {
            $t->unsignedInteger('agrupacion_columna_id')->nullable()->index();
        });
        $this->registrarCatalogo();
    }

    private function registrarCatalogo()
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos')) return;
        $now = date('Y-m-d H:i:s');
        $namespace = 'App\\Calificaciones\\AgrupacionCalificacion';
        $id = DB::table('sys_modelos')->where('name_space', $namespace)->value('id');
        if (!$id) {
            $datos = ['descripcion' => 'Agrupaciones de columnas de calificaciones', 'modelo' => 'sga_agrupaciones_calificacion',
                'name_space' => $namespace, 'modelo_relacionado' => 'columnas', 'url_crear' => 'web/create',
                'url_edit' => 'web/id_fila/edit', 'url_ver' => 'web/id_fila', 'url_print' => '', 'enlaces' => '',
                'url_estado' => '', 'url_eliminar' => '', 'controller_complementario' => '', 'url_form_create' => '',
                'home_miga_pan' => 'calificaciones, Calificaciones', 'ruta_storage_imagen' => '',
                'ruta_storage_archivo_adjunto' => '', 'created_at' => $now, 'updated_at' => $now];
            $id = DB::table('sys_modelos')->insertGetId($this->existentes('sys_modelos', $datos));
        }
        $campos = [
            ['titulo', 'Título agrupador', 'bsText', '', 1], ['peso', 'Porcentaje total del grupo', 'bsNumber', '', 1], ['anio', 'Año', 'bsNumber', '', 1],
            ['periodo_id', 'Periodo (vacío: todos los periodos del año)', 'select', 'model_App\\Calificaciones\\Periodo', 0],
            ['estado', 'Estado', 'select', '{"Borrador":"Borrador","Activo":"Activo"}', 1]
        ];
        foreach ($campos as $i => $c) {
            $field = DB::table('sys_campos')->where('definicion', 'sga_agrupaciones_calificacion')->where('name', $c[0])->value('id');
            if (!$field) $field = DB::table('sys_campos')->insertGetId([
                'name' => $c[0], 'descripcion' => $c[1], 'tipo' => $c[2], 'opciones' => $c[3],
                'value' => $c[0] === 'estado' ? 'Borrador' : 'null', 'atributos' => '',
                'definicion' => 'sga_agrupaciones_calificacion', 'requerido' => $c[4], 'editable' => 1, 'unico' => 0,
                'created_at' => $now, 'updated_at' => $now]);
            if (!DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', $id)->where('core_campo_id', $field)->exists()) {
                // Relación del catálogo con los campos del CRUD genérico.
                $pivot = ['core_modelo_id' => $id, 'core_campo_id' => $field, 'orden' => $i + 1];
                DB::table('sys_modelo_tiene_campos')->insert($this->existentes('sys_modelo_tiene_campos', $pivot));
            }
        }
        if (!Schema::hasTable('permissions')) return;
        $appId = DB::table('sys_aplicaciones')->where('app', 'calificaciones')->value('id');
        $permission = DB::table('permissions')->where('name', 'cali_agrupaciones_columnas')->value('id');
        if (!$permission) {
            $parent = DB::table('permissions')->where('core_app_id', $appId)->where('descripcion', 'Catálogos')->value('id');
            $permission = DB::table('permissions')->insertGetId($this->existentes('permissions', [
                'name' => 'cali_agrupaciones_columnas', 'descripcion' => 'Agrupaciones de columnas', 'core_app_id' => $appId,
                'modelo_id' => $id, 'url' => 'web', 'parent' => $parent ?: 0, 'orden' => 35, 'enabled' => 1,
                'fa_icon' => '', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now]));
        }
        foreach (DB::table('roles')->whereIn('name', ['SuperAdmin', 'Administrador'])->get() as $role) {
            if (!DB::table('role_has_permissions')->where('role_id', $role->id)->where('permission_id', $permission)->exists()) {
                DB::table('role_has_permissions')->insert($this->existentes('role_has_permissions', ['role_id' => $role->id, 'permission_id' => $permission, 'orden' => 0]));
            }
        }
        app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
    private function existentes($table, $data) { return array_intersect_key($data, array_flip(Schema::getColumnListing($table))); }

    public function down()
    {
        if (Schema::hasTable('sga_agrupaciones_calificacion') && DB::table('sga_agrupaciones_calificacion')->where('bloqueado', true)->exists()) {
            throw new RuntimeException('No se puede revertir: hay agrupaciones utilizadas en actividades o notas.');
        }
        if (Schema::hasColumn('sga_calificaciones_encabezados', 'agrupacion_columna_id')) {
            if (DB::table('sga_calificaciones_encabezados')->whereNotNull('agrupacion_columna_id')->exists()) throw new RuntimeException('No se puede revertir: hay actividades vinculadas a agrupaciones.');
            Schema::table('sga_calificaciones_encabezados', function (Blueprint $t) { $t->dropColumn('agrupacion_columna_id'); });
        }
        $id = Schema::hasTable('sys_modelos') ? DB::table('sys_modelos')->where('name_space', 'App\\Calificaciones\\AgrupacionCalificacion')->value('id') : null;
        if ($id) {
            DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', $id)->delete();
            $permission = DB::table('permissions')->where('name', 'cali_agrupaciones_columnas')->value('id');
            foreach (['role_has_permissions', 'user_has_permissions'] as $table) if (Schema::hasTable($table)) DB::table($table)->where('permission_id', $permission)->delete();
            DB::table('permissions')->where('name', 'cali_agrupaciones_columnas')->delete();
            DB::table('sys_modelos')->where('id', $id)->delete();
            DB::table('sys_campos')->where('definicion', 'sga_agrupaciones_calificacion')->delete();
        }
        Schema::dropIfExists('sga_agrupacion_columnas'); Schema::dropIfExists('sga_agrupaciones_calificacion');
    }
}
