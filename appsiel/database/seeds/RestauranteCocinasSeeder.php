<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RestauranteCocinasSeeder extends Seeder
{
    const RUTA_STORAGE_IMAGEN = 'ventas/restaurante/cocinas/';

    public function run()
    {
        $this->prepararDirectorioImagenes();
        $this->seedCocinas();
        $this->migrarImagenesRemotas();
        $modeloId = $this->seedModelo();
        $this->seedCampos($modeloId);
        $this->seedPermiso($modeloId);
    }

    private function seedCocinas()
    {
        if (!Schema::hasTable('vtas_restaurante_cocinas')) {
            return;
        }

        $cocinas = [
            [
                'label' => 'Chuzos',
                'grupo_inventarios_id' => null,
                'bodega_default_id' => null,
                'url_imagen' => 'https://cdn.pixabay.com/photo/2016/06/06/18/29/meat-skewer-1440105_960_720.jpg',
                'estado' => 'Activo',
            ],
            [
                'label' => 'Hamburguesas',
                'grupo_inventarios_id' => 4,
                'bodega_default_id' => 1,
                'url_imagen' => 'https://cdn.pixabay.com/photo/2020/12/13/22/48/hamburguer-5829560_960_720.jpg',
                'estado' => 'Activo',
            ],
            [
                'label' => 'Bebidas',
                'grupo_inventarios_id' => 5,
                'bodega_default_id' => 1,
                'url_imagen' => 'https://cdn.pixabay.com/photo/2014/09/26/19/51/drink-462776_960_720.jpg',
                'estado' => 'Activo',
            ],
            [
                'label' => 'DAYTONA',
                'grupo_inventarios_id' => 7,
                'bodega_default_id' => 1,
                'url_imagen' => 'https://appsiel.com.co/nube/daytonajpeg.jpeg',
                'estado' => 'Activo',
            ],
        ];

        foreach ($cocinas as $cocina) {
            $registro = DB::table('vtas_restaurante_cocinas')->where('label', $cocina['label'])->first();

            if ($registro) {
                continue;
            }

            $cocina['updated_at'] = date('Y-m-d H:i:s');
            $cocina['created_at'] = date('Y-m-d H:i:s');
            DB::table('vtas_restaurante_cocinas')->insert($cocina);
        }
    }

    private function migrarImagenesRemotas()
    {
        if (!Schema::hasTable('vtas_restaurante_cocinas')) {
            return;
        }

        $cocinas = DB::table('vtas_restaurante_cocinas')
            ->where('url_imagen', 'LIKE', 'http%')
            ->get();

        foreach ($cocinas as $cocina) {
            $contenido = @file_get_contents($cocina->url_imagen);
            if ($contenido === false) {
                continue;
            }

            $path = parse_url($cocina->url_imagen, PHP_URL_PATH);
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $extension = 'jpg';
            }

            $nombreArchivo = str_slug($cocina->label) . '-' . uniqid() . '.' . $extension;
            Storage::put(self::RUTA_STORAGE_IMAGEN . $nombreArchivo, $contenido);

            DB::table('vtas_restaurante_cocinas')
                ->where('id', $cocina->id)
                ->update([
                    'url_imagen' => $nombreArchivo,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }

        $this->prepararDirectorioImagenes();
    }

    private function prepararDirectorioImagenes()
    {
        Storage::makeDirectory(self::RUTA_STORAGE_IMAGEN);

        $path = storage_path('app/' . self::RUTA_STORAGE_IMAGEN);
        @chmod(storage_path('app/ventas'), 0775);
        @chmod(storage_path('app/ventas/restaurante'), 0775);
        @chmod($path, 0775);
    }

    private function seedModelo()
    {
        if (!Schema::hasTable('sys_modelos')) {
            return 0;
        }

        $modelo = DB::table('sys_modelos')
            ->where('name_space', 'App\\Ventas\\RestauranteCocina')
            ->first();

        $datos = [
            'descripcion' => 'Cocinas restaurante',
            'modelo' => 'RestauranteCocina',
            'name_space' => 'App\\Ventas\\RestauranteCocina',
            'modelo_relacionado' => '',
            'url_crear' => 'web/create',
            'url_edit' => 'web/id_fila/edit',
            'url_print' => '',
            'url_ver' => 'web/id_fila',
            'enlaces' => '',
            'url_estado' => '',
            'url_eliminar' => 'web_eliminar/id_fila',
            'controller_complementario' => '',
            'url_form_create' => '',
            'home_miga_pan' => 'ventas,Ventas',
            'ruta_storage_imagen' => self::RUTA_STORAGE_IMAGEN,
            'ruta_storage_archivo_adjunto' => '',
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($modelo) {
            DB::table('sys_modelos')->where('id', $modelo->id)->update($datos);
            return (int)$modelo->id;
        }

        $datos['created_at'] = date('Y-m-d H:i:s');
        return (int)DB::table('sys_modelos')->insertGetId($datos);
    }

    private function seedCampos($modeloId)
    {
        if ($modeloId == 0 || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $campos = [
            [
                'orden' => 1,
                'descripcion' => 'Cocina',
                'tipo' => 'bsText',
                'name' => 'label',
                'opciones' => '',
                'value' => 'null',
                'atributos' => '',
                'definicion' => '',
                'requerido' => 1,
                'editable' => 1,
                'unico' => 0,
            ],
            [
                'orden' => 2,
                'descripcion' => 'Grupo inventario',
                'tipo' => 'select',
                'name' => 'grupo_inventarios_id',
                'opciones' => 'model_App\\Inventarios\\InvGrupo',
                'value' => 'null',
                'atributos' => '',
                'definicion' => '',
                'requerido' => 0,
                'editable' => 1,
                'unico' => 0,
            ],
            [
                'orden' => 3,
                'descripcion' => 'Bodega default',
                'tipo' => 'select',
                'name' => 'bodega_default_id',
                'opciones' => 'model_App\\Inventarios\\InvBodega',
                'value' => 'null',
                'atributos' => '',
                'definicion' => '',
                'requerido' => 0,
                'editable' => 1,
                'unico' => 0,
            ],
            [
                'orden' => 4,
                'descripcion' => 'Imagen',
                'tipo' => 'imagen',
                'name' => 'url_imagen',
                'opciones' => 'jpg,png,jpeg,gif,webp',
                'value' => 'null',
                'atributos' => '',
                'definicion' => '',
                'requerido' => 0,
                'editable' => 1,
                'unico' => 0,
            ],
            [
                'orden' => 5,
                'descripcion' => 'Printer IP',
                'tipo' => 'bsText',
                'name' => 'printer_ip',
                'opciones' => '',
                'value' => 'null',
                'atributos' => '',
                'definicion' => '',
                'requerido' => 0,
                'editable' => 1,
                'unico' => 0,
            ],
            [
                'orden' => 6,
                'descripcion' => 'Estado',
                'tipo' => 'select',
                'name' => 'estado',
                'opciones' => '{"Activo":"Activo","Inactivo":"Inactivo"}',
                'value' => 'Activo',
                'atributos' => '',
                'definicion' => '',
                'requerido' => 1,
                'editable' => 1,
                'unico' => 0,
            ],
        ];

        foreach ($campos as $campo) {
            $orden = $campo['orden'];
            unset($campo['orden']);

            $campoId = DB::table('sys_campos')
                ->where('name', $campo['name'])
                ->where('descripcion', $campo['descripcion'])
                ->value('id');

            if (!$campoId && $campo['name'] == 'url_imagen') {
                $campoId = DB::table('sys_campos')->where('name', 'url_imagen')->value('id');
            }

            if (!$campoId) {
                $campo['created_at'] = date('Y-m-d H:i:s');
                $campo['updated_at'] = date('Y-m-d H:i:s');
                $campoId = DB::table('sys_campos')->insertGetId($campo);
            } else {
                $campo['updated_at'] = date('Y-m-d H:i:s');
                DB::table('sys_campos')->where('id', $campoId)->update($campo);
            }

            DB::table('sys_modelo_tiene_campos')->updateOrInsert(
                [
                    'core_modelo_id' => $modeloId,
                    'core_campo_id' => $campoId,
                ],
                [
                    'orden' => $orden,
                ]
            );
        }
    }

    private function seedPermiso($modeloId)
    {
        if ($modeloId == 0 || !Schema::hasTable('permissions')) {
            return;
        }

        $ventasAppId = 13;
        if (Schema::hasTable('sys_aplicaciones')) {
            $ventasAppId = (int)DB::table('sys_aplicaciones')->where('descripcion', 'Ventas')->value('id');
            if ($ventasAppId == 0) {
                $ventasAppId = 13;
            }
        }

        $permission = Permission::firstOrNew(['name' => 'vtas_restaurante_cocinas']);
        $permission->core_app_id = $ventasAppId;
        $permission->modelo_id = $modeloId;
        $permission->descripcion = 'Cocinas restaurante';
        $permission->url = 'web';
        $permission->parent = 0;
        $permission->orden = 13;
        $permission->enabled = 0;
        $permission->fa_icon = 'cutlery';
        $permission->save();

        foreach (['SuperAdmin', 'Administrador'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $roleHasPermission = false;
            if (Schema::hasTable('role_has_permissions')) {
                $roleHasPermission = DB::table('role_has_permissions')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permission->id)
                    ->exists();
            }

            if (!$roleHasPermission) {
                $role->givePermissionTo($permission);
            }
        }
    }
}
