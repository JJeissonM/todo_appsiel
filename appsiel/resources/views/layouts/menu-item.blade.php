@if ($item['submenu'] == [])
    @can($item['name'])
        <li>
            <a style="color: #FFFFFF !important;" href="{{ url($item['url'].'?id='.$item['core_app_id'].'&id_modelo='.$item['modelo_id']) }}">{{ $item['descripcion'] }} </a>
        </li>
    @endcan
@else
    <li class="dropdown">
        @can($item['name'])
            <a style="color: #FFFFFF !important;" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $item['descripcion'] }} <span class="caret"></span></a>
        @endcan
        <ul class="dropdown-menu sub-menu" style="background-color: #42A3DC !important;">
            @foreach ($item['submenu'] as $submenu)
                @if ($submenu['submenu'] == [])
                    @can($submenu['name'])
                        <li>
                            <a style="color: #FFFFFF !important;" href="{{ url($submenu['url'].'?id='.$submenu['core_app_id'].'&id_modelo='.$submenu['modelo_id']) }}"> {{ $submenu['descripcion'] }} </a>
                        </li>
                    @endcan
                @else
                    @include('layouts.menu-item', [ 'item' => $submenu ])
                @endif
            @endforeach
        </ul>
    </li>
@endif