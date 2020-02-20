@if ($item['submenu'] == [])
    <li>
        <a href="{{ url( $item['enlace'] ) }}" target="{{ $item['target'] }}">{{ $item['descripcion'] }} </a>
    </li>
@else
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $item['descripcion'] }} <span class="caret"></span></a>

        <ul class="dropdown-menu sub-menu">
            @foreach ($item['submenu'] as $submenu)
                @if ($submenu['submenu'] == [])
                    <li>
                        <a href="{{ url( $submenu['enlace'] ) }}"  target="{{ $item['target'] }}">{{ $submenu['descripcion'] }} </a>
                    </li>
                @else
                    @include('web.front_end.modulos.menu.menu-item', [ 'item' => $submenu ])
                @endif
            @endforeach
        </ul>
    </li>
@endif