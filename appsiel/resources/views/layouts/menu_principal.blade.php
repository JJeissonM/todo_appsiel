<?php
    use App\Core\Menu;
    use Spatie\Permission\Models\Permission;
    $menus = Menu::menus(Input::get('id'));

    $item_reporte = "";
    
    $user = \Auth::user();
    
    if( $user != null)
    {
        $permiso_bloquear_reportes = 'core_bloquear_menu_reportes';
        $existe_permiso = Permission::where('name', $permiso_bloquear_reportes)->exists();

        if ( !$existe_permiso || !$user->hasPermissionTo($permiso_bloquear_reportes) )
        {
            $reportes = App\Sistema\Reporte::where( ['core_app_id' => Input::get('id'), 'estado' => 'Activo'] )->get();
            
            if ( !$reportes->isEmpty() ) {

                    $item_reporte = '<li class="dropdown">
                                        <a href="#" style="color: #FFFFFF !important;" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> Reportes <span class="caret"></span></a>
                                        <ul class="dropdown-menu sub-menu" style="background-color: #42A3DC !important;">';
                                            foreach($reportes as $un_reporte)
                                            {
                                                $item_reporte .= '<li> <a href="'.url('vista_reporte?id='.$un_reporte->core_app_id.'&reporte_id='.$un_reporte->id).'" style="color: #FFFFFF !important;">'.$un_reporte->descripcion.'</a>
                                                                </li>';
                                            }

                    $item_reporte .= '   </ul>
                                    </li>';
            }
        }
    }

    
?>

@if (!Auth::guest())
    <nav class="navbar navbar-inverse navbar-static-top" style="background: rgb(87, 70, 150) !important;">
        <div class="container-fluid">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/inicio') }}" style="height: 50px; padding-top: 5px;">
    				<img src="{{ asset('assets/img/appsiel-logo2.png') }}" width="180" height="50px">
    			</a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                
                <!-- No muestra el menú en entorno demo para las aplicaciones del array -->
                @if( app()->environment() != 'demo' || !in_array( Input::get('id'), $aplicaciones_inactivas_demo ) )
                <ul class="nav navbar-nav">
                    @foreach ($menus as $key => $item)
                        @if ($item['parent'] != 0)
                            @break
                        @endif
                        @include('layouts.menu-item', ['item' => $item])
                    @endforeach
                    @if (Auth::check() && Auth::user()->hasRole('Empleado'))
                        <?php
                            $empleadoUrl = url('nomina/empleado');
                            if ( Input::get('id') )
                            {
                                $empleadoUrl .= '?id=' . Input::get('id');
                            }
                        ?>
                        <li>
                            <a href="{{ $empleadoUrl }}" style="color: #FFFFFF !important;">
                                <i class="fa fa-user-circle"></i> Mi nómina
                            </a>
                        </li>
                    @endif
                    {!! $item_reporte !!}
                </ul>
                @endif

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
                    <!-- Authentication Links -->
                    @if (Auth::guest())
                        <li><a style="color: #FFFFFF !important;" href="{{ url('/login') }}">Ingresar</a></li>
                    @else
                        
                        @if( config('configuracion.usar_mensajes_internos') )
                            <li>
                                <a title="Mis Mensajes" style="color: #FFFFFF !important;" href="{{url('/messages')}}"><i class="fa fa-btn fa-envelope"></i>  @include('core.messenger.unread-count')</a>
                            </li>
                        @endif

                        <li class="dropdown">
                            <a style="color: #FFFFFF !important;" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu" style="background-color: #42A3DC !important;">
                                <!-- <li><a href="{ { url('/dashboard?id='.Input::get('id')) }}"><i class="fa fa-btn fa-dashboard"></i>DashBoard</a></li> -->

                                @if( !is_null( Input::get('id') ) )
                                    <li><a style="color: #FFFFFF !important;" href="{{ url('/core/usuario/perfil/?id='.Input::get('id')) }}"><i class="fa fa-btn fa-user"></i> Perfil</a></li>
                                @else
                                    <li><i>(Ingrese a una aplicación para ver su perfil)</i></li>
                                @endif

                                <li><a style="color: #FFFFFF !important;" href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i> Cerrar sesión</a></li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif
