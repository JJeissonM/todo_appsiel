<?php

namespace App\Traits;

use App\User;
use App\UserHasRole;
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

    public static function aplicarFiltroCreadoPorUsuarioSeleccionado($query, $user, $columna = null)
    {
        $column = $columna ?: 'creado_por';
        $emails = self::obtenerEmailsFiltroUsuarioSeleccionado($user);

        if (empty($emails)) {
            return $query;
        }

        return $query->whereIn($column, $emails);
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

    protected static function obtenerEmailsFiltroUsuarioSeleccionado($user): array
    {
        if (is_null($user) || empty($user->email)) {
            return [];
        }

        $emails = [$user->email];
        $roles_sin_filtro = self::rolesSinFiltro();

        if (empty($roles_sin_filtro)) {
            return $emails;
        }

        $empresa_id = $user->empresa_id ?? Auth::user()->empresa_id ?? null;

        $query = UserHasRole::leftJoin('users', 'users.id', '=', 'user_has_roles.user_id')
            ->leftJoin('roles', 'roles.id', '=', 'user_has_roles.role_id');

        if (!is_null($empresa_id)) {
            $query->where('users.empresa_id', $empresa_id);
        }

        $emails_roles_sin_filtro = $query->whereIn('roles.name', $roles_sin_filtro)
            ->whereNotNull('users.email')
            ->pluck('users.email')
            ->toArray();

        return array_values(array_unique(array_merge($emails, $emails_roles_sin_filtro)));
    }

    public static function obtenerUsuarioFiltro($user_id, $empresa_id = null)
    {
        if ((int)$user_id === 0) {
            return null;
        }

        $query = User::query();

        if (!is_null($empresa_id)) {
            $query->where('empresa_id', $empresa_id);
        }

        return $query->find((int)$user_id);
    }
}
