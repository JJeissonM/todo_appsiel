<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateNotaAjusteDocSoporte extends Migration
{
    const TRANSACCION_ID = 62;
    const DESCRIPCION = 'Nota de ajuste documento soporte';
    const NAMESPACE_MODELO = 'App\\FacturacionElectronica\\NotaAjusteDocSoporte';
    const PERMISO = 'compras_notas_ajuste_doc_soporte';

    public function up()
    {
        if (Schema::hasTable('compras_doc_encabezados') && !Schema::hasColumn('compras_doc_encabezados', 'nota_ajuste_concepto')) Schema::table('compras_doc_encabezados', function (Blueprint $t) {
            $t->unsignedTinyInteger('nota_ajuste_concepto')->nullable();
        });
        // Línea del DS que la línea de la nota ajusta.
        if (Schema::hasTable('compras_doc_registros') && !Schema::hasColumn('compras_doc_registros', 'compras_doc_registro_origen_id')) Schema::table('compras_doc_registros', function (Blueprint $t) {
            $t->unsignedInteger('compras_doc_registro_origen_id')->nullable()->index();
        });
        $this->registrarCatalogo();
    }

    private function registrarCatalogo()
    {
        if (!Schema::hasTable('sys_tipos_transacciones') || !Schema::hasTable('sys_modelos')) return;
        $now = date('Y-m-d H:i:s');

        $transaccion = DB::table('sys_tipos_transacciones')->where('id', self::TRANSACCION_ID)->first();
        if ($transaccion && $transaccion->descripcion !== self::DESCRIPCION) {
            throw new RuntimeException('El id ' . self::TRANSACCION_ID . ' de sys_tipos_transacciones ya existe con otra descripción: "' . $transaccion->descripcion . '". Resuelva el conflicto antes de migrar.');
        }

        $id = DB::table('sys_modelos')->where('name_space', self::NAMESPACE_MODELO)->value('id');
        if (!$id) {
            $datos = ['descripcion' => 'Notas de ajuste documento soporte', 'modelo' => 'nota.ajuste.doc.soporte',
                'name_space' => self::NAMESPACE_MODELO, 'modelo_relacionado' => '', 'url_crear' => 'compras_notas_ajuste_doc_soporte/create',
                'url_edit' => '', 'url_ver' => 'compras_notas_ajuste_doc_soporte/id_fila', 'url_print' => '', 'enlaces' => '',
                'url_estado' => '', 'url_eliminar' => '', 'controller_complementario' => '',
                'url_form_create' => 'compras_notas_ajuste_doc_soporte', 'home_miga_pan' => 'compras, Compras',
                'ruta_storage_imagen' => '', 'ruta_storage_archivo_adjunto' => '', 'created_at' => $now, 'updated_at' => $now];
            $id = DB::table('sys_modelos')->insertGetId($this->existentes('sys_modelos', $datos));
        }

        if (!$transaccion) {
            // Mismos modelos de encabezado/registro/movimiento y app (9 = compras) que la transacción 61.
            $base = DB::table('sys_tipos_transacciones')->where('id', 61)->first();
            DB::table('sys_tipos_transacciones')->insert($this->existentes('sys_tipos_transacciones', [
                'id' => self::TRANSACCION_ID, 'core_app_id' => $base ? $base->core_app_id : 9, 'core_modelo_id' => $id,
                'descripcion' => self::DESCRIPCION,
                'modelo_encabezados_documentos' => $base ? $base->modelo_encabezados_documentos : 'App\\Compras\\ComprasDocEncabezado',
                'modelo_registros_documentos' => $base ? $base->modelo_registros_documentos : 'App\\Compras\\ComprasDocRegistro',
                'modelo_movimientos' => $base ? $base->modelo_movimientos : 'App\\Compras\\ComprasMovimiento',
                'orden' => $base ? $base->orden : 3, 'estado' => 'Activo', 'created_at' => $now, 'updated_at' => $now]));
        }

        // Tipo de documento NAS, sin resolución DIAN; la empresa puede cambiar el prefijo.
        if (Schema::hasTable('core_tipos_docs_apps')) {
            $tipoDoc = DB::table('core_tipos_docs_apps')->where('descripcion', self::DESCRIPCION)->value('id');
            if (!$tipoDoc) {
                $tipoDoc = DB::table('core_tipos_docs_apps')->insertGetId($this->existentes('core_tipos_docs_apps', [
                    'prefijo' => 'NAS', 'descripcion' => self::DESCRIPCION, 'estado' => 'Activo', 'created_at' => $now, 'updated_at' => $now]));
            }
            if (Schema::hasTable('core_transaccion_tiene_documento')
                && !DB::table('core_transaccion_tiene_documento')->where('core_tipo_transaccion_id', self::TRANSACCION_ID)->where('core_tipo_doc_id', $tipoDoc)->exists()) {
                DB::table('core_transaccion_tiene_documento')->insert($this->existentes('core_transaccion_tiene_documento', [
                    'orden' => 0, 'core_tipo_transaccion_id' => self::TRANSACCION_ID, 'core_tipo_doc_id' => $tipoDoc]));
            }
        }

        // Campos del formulario: los mismos del modelo 332 (nota crédito por valor), salvo valor_total.
        if (Schema::hasTable('sys_modelo_tiene_campos') && Schema::hasTable('sys_campos')
            && !DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', $id)->exists()) {
            $campos = DB::table('sys_modelo_tiene_campos')->join('sys_campos', 'sys_campos.id', '=', 'sys_modelo_tiene_campos.core_campo_id')
                ->where('sys_modelo_tiene_campos.core_modelo_id', 332)->where('sys_campos.name', '<>', 'valor_total')
                ->select('sys_modelo_tiene_campos.core_campo_id', 'sys_modelo_tiene_campos.orden')->get();
            foreach ($campos as $c) {
                DB::table('sys_modelo_tiene_campos')->insert($this->existentes('sys_modelo_tiene_campos', ['core_modelo_id' => $id, 'core_campo_id' => $c->core_campo_id, 'orden' => $c->orden]));
            }
        }

        if (!Schema::hasTable('permissions')) return;
        $permission = DB::table('permissions')->where('name', self::PERMISO)->value('id');
        if (!$permission) {
            $hermano = DB::table('permissions')->where('name', 'compras_notas_credito_valor')->first()
                ?: DB::table('permissions')->where('name', 'compras_notas_credito')->first();
            $permission = DB::table('permissions')->insertGetId($this->existentes('permissions', [
                'name' => self::PERMISO, 'descripcion' => 'Notas de ajuste documento soporte', 'core_app_id' => $hermano ? $hermano->core_app_id : 9,
                'modelo_id' => $id, 'url' => 'web', 'parent' => $hermano ? $hermano->parent : 0, 'orden' => $hermano ? $hermano->orden : 6, 'enabled' => 1,
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
        if (Schema::hasTable('compras_doc_encabezados') && Schema::hasColumn('compras_doc_encabezados', 'nota_ajuste_concepto')) {
            if (DB::table('compras_doc_encabezados')->where('core_tipo_transaccion_id', self::TRANSACCION_ID)->exists()) {
                throw new RuntimeException('No se puede revertir: hay notas de ajuste de documento soporte creadas.');
            }
            Schema::table('compras_doc_encabezados', function (Blueprint $t) { $t->dropColumn('nota_ajuste_concepto'); });
        }
        if (Schema::hasTable('compras_doc_registros') && Schema::hasColumn('compras_doc_registros', 'compras_doc_registro_origen_id')) {
            Schema::table('compras_doc_registros', function (Blueprint $t) { $t->dropIndex(['compras_doc_registro_origen_id']); $t->dropColumn('compras_doc_registro_origen_id'); });
        }
        if (!Schema::hasTable('sys_modelos')) return;
        $id = DB::table('sys_modelos')->where('name_space', self::NAMESPACE_MODELO)->value('id');
        if ($id) {
            DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', $id)->delete();
            $permission = DB::table('permissions')->where('name', self::PERMISO)->value('id');
            foreach (['role_has_permissions', 'user_has_permissions'] as $table) if ($permission && Schema::hasTable($table)) DB::table($table)->where('permission_id', $permission)->delete();
            DB::table('permissions')->where('name', self::PERMISO)->delete();
            DB::table('sys_modelos')->where('id', $id)->delete();
        }
        DB::table('sys_tipos_transacciones')->where('id', self::TRANSACCION_ID)->where('descripcion', self::DESCRIPCION)->delete();
        $tipoDoc = DB::table('core_tipos_docs_apps')->where('descripcion', self::DESCRIPCION)->value('id');
        if ($tipoDoc) {
            DB::table('core_transaccion_tiene_documento')->where('core_tipo_transaccion_id', self::TRANSACCION_ID)->where('core_tipo_doc_id', $tipoDoc)->delete();
            DB::table('core_tipos_docs_apps')->where('id', $tipoDoc)->delete();
        }
    }
}
