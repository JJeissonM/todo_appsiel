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

        if ($user->hasRole('SuperAdmin') || $user->hasRole('Administrador')) {
            return true;
        }

        try {
            return $user->hasPermissionTo($permisoName);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }
}
