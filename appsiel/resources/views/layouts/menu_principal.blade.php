<?php
    use App\Core\Menu; 
    $menus = Menu::menus(Input::get('id'));

    $item_reporte = "";
    
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
@if(config('configuracion.usuario_en_mora') == 'true')		
<div class="alert alert-danger alert-dismissible mora" role="alert">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    Señor usuario, su cuenta esta en mora. 
    <style>
        .navbar{
            margin-bottom: 0;
        }
        .mora{
            margin: 2px 8px;
        }
    </style>
</div>	

@endif