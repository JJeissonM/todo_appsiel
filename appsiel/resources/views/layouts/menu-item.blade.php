<?php
    $item_permission_name = isset($item['name']) ? trim((string)$item['name']) : '';
    $item_can_show = $item_permission_name != '' && isset($menu_permission_names) && in_array($item_permission_name, $menu_permission_names) && Auth::check() && Auth::user()->hasPermissionTo($item_permission_name);
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
                        $submenu_permission_name = isset($submenu['name']) ? trim((string)$submenu['name']) : '';
                        $submenu_can_show = $submenu_permission_name != '' && isset($menu_permission_names) && in_array($submenu_permission_name, $menu_permission_names) && Auth::check() && Auth::user()->hasPermissionTo($submenu_permission_name);
                    ?>
                    @if($submenu_can_show)
                        <li>
                            <a style="color: #FFFFFF !important;" href="{{ url($submenu['url'].'?id='.$submenu['core_app_id'].'&id_modelo='.$submenu['modelo_id']) }}"> {{ $submenu['descripcion'] }} </a>
                        </li>
                    @endif
                @else
                    @include('layouts.menu-item', [ 'item' => $submenu ])
                @endif
            @endforeach
        </ul>
    </li>
@endif
