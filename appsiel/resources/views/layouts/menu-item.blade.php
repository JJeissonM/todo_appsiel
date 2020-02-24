@if ($item['submenu'] == [])
@can($item['name'])
<li>
    <a href="{{ url($item['url'].'?id='.$item['core_app_id'].'&id_modelo='.$item['modelo_id']) }}">{{ $item['descripcion'] }} </a>
</li>
@endcan
@else
<li class="dropdown">
    @can($item['name'])
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $item['descripcion'] }} <span class="caret"></span></a>
    @endcan
    <ul class="dropdown-menu sub-menu">
        @foreach ($item['submenu'] as $submenu)
        @if ($submenu['submenu'] == [])
        @can($item['name'])
        <li>
            <a href="{{ url($submenu['url'].'?id='.$submenu['core_app_id'].'&id_modelo='.$submenu['modelo_id']) }}"> {{ $submenu['descripcion'] }} </a>
            <!-- <a href="{ { url($submenu['url'].'?id='.$submenu['core_app_id'].'&id_modelo='.$submenu['modelo_id']) }}" target="_blank"> <i class="fa fa-external-link"></i> </a> -->
        </li>
        @endcan
        @else
        @include('layouts.menu-item', [ 'item' => $submenu ])
        @endif
        @endforeach
    </ul>
</li>
@endif