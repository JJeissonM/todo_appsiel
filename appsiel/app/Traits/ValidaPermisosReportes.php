<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

trait ValidaPermisosReportes
{
    private static $MENSAJE_PERMISO_DENEGADO = '<h2>Su perfil de usuario no tiene permiso para generar este reporte.</h2>';

    private function respuestaSinPermiso()
    {
        return self::$MENSAJE_PERMISO_DENEGADO;
    }

    private function usuarioTienePermisoReporte(string $permisoName): bool
    {
        $user = Auth::user();

        if ($this->usuarioTieneRolPrivilegiadoReporte($user)) {
            return true;
        }

        try {
            return $user->hasPermissionTo($permisoName);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    private function usuarioTieneRolPrivilegiadoReporte($user): bool
    {
        $rolesPrivilegiados = array_unique(array_merge(
            ['SuperAdmin', 'Administrador'],
            array_map('trim', (array)config('filtrado_registros.roles_sin_filtro', []))
        ));

        if (empty($rolesPrivilegiados)) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            try {
                return $user->hasAnyRole($rolesPrivilegiados);
            } catch (\Throwable $e) {
                // Compatibilidad con instalaciones donde hasAnyRole no acepte arreglos.
            }
        }

        foreach ($rolesPrivilegiados as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
