<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateTutorEstudiantePermissions extends Migration
{
    protected $permissions = [
        [
            'name' => 'academico_estudiante.horario',
            'descripcion' => 'Ver horario académico',
            'url' => 'academico_estudiante/horario',
            'orden' => 10,
            'fa_icon' => 'fa fa-calendar'
        ],
        [
            'name' => 'academico_estudiante.calificaciones',
            'descripcion' => 'Consultar calificaciones',
            'url' => 'academico_estudiante/calificaciones',
            'orden' => 20,
            'fa_icon' => 'fa fa-sort-numeric-desc'
        ],
        [
            'name' => 'academico_estudiante.aula_virtual',
            'descripcion' => 'Acceder al aula virtual',
            'url' => 'academico_estudiante_aula_virtual',
            'orden' => 30,
            'fa_icon' => 'fa fa-chalkboard'
        ],
        [
            'name' => 'academico_estudiante.mis_asignaturas',
            'descripcion' => 'Ver mis asignaturas',
            'url' => 'mis_asignaturas',
            'orden' => 40,
            'fa_icon' => 'fa fa-book'
        ],
        [
            'name' => 'academico_estudiante.libreta_pagos',
            'descripcion' => 'Consultar libreta de pagos',
            'url' => 'academico_estudiante/mi_plan_de_pagos',
            'orden' => 50,
            'fa_icon' => 'fa fa-dollar'
        ],
        [
            'name' => 'academico_estudiante.correo_institucional',
            'descripcion' => 'Ir al correo institucional',
            'url' => 'academico_estudiante',
            'orden' => 60,
            'fa_icon' => 'fa fa-envelope'
        ],
        [
            'name' => 'academico_estudiante.reconocimientos',
            'descripcion' => 'Ver reconocimientos',
            'url' => 'academico_estudiante/reconocimientos',
            'orden' => 70,
            'fa_icon' => 'fa fa-trophy'
        ],
        [
            'name' => 'matriculas.estudiantes.crear_tutor',
            'descripcion' => 'Crear usuario Tutor desde responsables',
            'url' => 'matriculas/estudiantes/responsables',
            'orden' => 80,
            'fa_icon' => 'fa fa-user-plus'
        ],
    ];

    public function up()
    {
        $roleTutor = Role::firstOrCreate(['name' => 'Tutor de estudiante']);

        $roleEstudiante = Role::where('name', 'Estudiante')->first();

        foreach ($this->permissions as $permissionData) {
            $permission = Permission::firstOrNew([
                'name' => $permissionData['name']
            ]);

            $permission->core_app_id = 6;
            $permission->modelo_id = 0;
            $permission->descripcion = $permissionData['descripcion'];
            $permission->url = $permissionData['url'];
            $permission->parent = 0;
            $permission->orden = $permissionData['orden'];
            $permission->enabled = 0;
            $permission->fa_icon = $permissionData['fa_icon'];
            $permission->save();

            $roleTutor->givePermissionTo($permission);
            if ($roleEstudiante instanceof Role) {
                $roleEstudiante->givePermissionTo($permission);
            }
        }

        $permission = Permission::where([
                'name' => 'Académico estudiante'
            ])->first();
        if ($permission) {
            $roleTutor->givePermissionTo($permission);
        }

        // Permiso Adicional para crear tutor desde responsables
         $permission = Permission::firstOrNew([
                'name' => 'matriculas.estudiantes.crear_tutor'
            ]);

        $permission->core_app_id = 6;
        $permission->modelo_id = 0;
        $permission->descripcion = 'Crear usuario Tutor desde responsables';
        $permission->url = 'matriculas/estudiantes/responsables';
        $permission->parent = 0;
        $permission->orden = 80;
        $permission->enabled = 0;
        $permission->fa_icon = 'fa fa-user-plus';
        $permission->save();
        
        $admin = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $admin->givePermissionTo($permission);
        
        $admin = Role::firstOrCreate(['name' => 'Administrador']);
        $admin->givePermissionTo($permission);

        $admin = Role::firstOrCreate(['name' => 'Admin Colegio']);
        $admin->givePermissionTo($permission);
        
    }

    public function down()
    {
        foreach ($this->permissions as $permissionData) {
            $permission = Permission::where('name', $permissionData['name'])->first();
            if (!is_null($permission)) {
                $permission->delete();
            }
        }

        Role::where('name', 'Tutor de estudiante')->delete();
    }
}
