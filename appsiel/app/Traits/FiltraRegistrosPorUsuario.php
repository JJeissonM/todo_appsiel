<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait FiltraRegistrosPorUsuario
{
    protected static function aplicarFiltroCreadoPor($query, $columna = null)
    {
        $column = $columna ?: 'creado_por';
        $user = Auth::user();
        $roles_sin_filtro = self::rolesSinFiltro();

        if (is_null($user) || empty($user->email)) {
            return $query;
        }

        if (self::usuarioTieneRolPrivilegiado($user, $roles_sin_filtro)) {
            return $query;
        }

        return $query->where($column, $user->email);
    }

    protected static function usuarioTieneRolPrivilegiado($user, array $roles)
    {
        if (method_exists($user, 'hasAnyRole')) {
            try {
                return $user->hasAnyRole($roles);
            } catch (\Throwable $e) {
                // Caída al trabajar con arreglos en lugar de colecciones, continuamos.
            }
        }

        $rolesUsuario = self::obtenerNombresRoles($user);
        return $rolesUsuario->intersect($roles)->count() > 0;
    }

    protected static function obtenerNombresRoles($user): Collection
    {
        if (method_exists($user, 'getRoleNames')) {
            return collect($user->getRoleNames());
        }

        $roles = $user->roles ?? [];
        return collect($roles)->pluck('name');
    }

    protected static function rolesSinFiltro(): array
    {
        $roles = config('filtrado_registros.roles_sin_filtro', []);
        return array_map('trim', (array)$roles);
    }
}
