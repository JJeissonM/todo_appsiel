<?php
    $menu_user = Auth::check() ? Auth::user() : null;
    $menu_usuario_administrador_reportes = isset($menu_usuario_administrador_reportes) ? $menu_usuario_administrador_reportes : false;

    if ($menu_user != null && !$menu_usuario_administrador_reportes) {
        $roles_administradores_reportes = array_unique(array_merge(
            ['SuperAdmin', 'Administrador'],
            array_map('trim', (array)config('filtrado_registros.roles_sin_filtro', []))
        ));

        foreach ($roles_administradores_reportes as $role) {
            if ($role != '' && $menu_user->hasRole($role)) {
                $menu_usuario_administrador_reportes = true;
                break;
            }
        }
    }

    $menu_ancestor_is_reports_menu = isset($menu_ancestor_is_reports_menu) ? $menu_ancestor_is_reports_menu : false;
    $item_url = isset($item['url']) ? trim((string)$item['url']) : '';
    $item_description = isset($item['descripcion']) ? trim((string)$item['descripcion']) : '';
    $item_is_reports_menu = $menu_ancestor_is_reports_menu || stripos($item_description, 'reporte') !== false || stripos($item_url, 'vista_reporte') !== false;

    $item_permission_name = isset($item['name']) ? trim((string)$item['name']) : '';
    $item_can_show = ($item_permission_name != '' && isset($menu_permission_names) && in_array($item_permission_name, $menu_permission_names) && $menu_user != null && $menu_user->hasPermissionTo($item_permission_name)) || ($menu_usuario_administrador_reportes && $item_is_reports_menu);
?>

@if ($item['submenu'] == [])
    @if($item_can_show)
        <li>
            <a style="color: #FFFFFF !important;" href="{{ url($item['url'].'?id='.$item['core_app_id'].'&id_modelo='.$item['modelo_id']) }}">{{ $item['descripcion'] }} </a>
        </li>
    @endif
@else
    <li class="dropdown">
        @if($item_can_show)
            <a style="color: #FFFFFF !important;" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $item['descripcion'] }} <span class="caret"></span></a>
        @endif
        <ul class="dropdown-menu sub-menu" style="background-color: #42A3DC !important;">
            @foreach ($item['submenu'] as $submenu)
                @if ($submenu['submenu'] == [])
                    <?php
                        $submenu_url = isset($submenu['url']) ? trim((string)$submenu['url']) : '';
                        $submenu_description = isset($submenu['descripcion']) ? trim((string)$submenu['descripcion']) : '';
                        $submenu_is_reports_menu = $item_is_reports_menu || stripos($submenu_description, 'reporte') !== false || stripos($submenu_url, 'vista_reporte') !== false;
                        $submenu_permission_name = isset($submenu['name']) ? trim((string)$submenu['name']) : '';
                        $submenu_can_show = ($submenu_permission_name != '' && isset($menu_permission_names) && in_array($submenu_permission_name, $menu_permission_names) && $menu_user != null && $menu_user->hasPermissionTo($submenu_permission_name)) || ($menu_usuario_administrador_reportes && $submenu_is_reports_menu);
                    ?>
                    @if($submenu_can_show)
                        <li>
                            <a style="color: #FFFFFF !important;" href="{{ url($submenu['url'].'?id='.$submenu['core_app_id'].'&id_modelo='.$submenu['modelo_id']) }}"> {{ $submenu['descripcion'] }} </a>
                        </li>
                    @endif
                @else
                    @include('layouts.menu-item', [ 'item' => $submenu, 'menu_ancestor_is_reports_menu' => $item_is_reports_menu, 'menu_usuario_administrador_reportes' => $menu_usuario_administrador_reportes ])
                @endif
            @endforeach
        </ul>
    </li>
@endif
