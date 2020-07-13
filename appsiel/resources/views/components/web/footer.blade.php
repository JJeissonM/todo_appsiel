@if($footer!=null)
    <style>
        .footerarea {
           position: relative;
        }

        .footerarea img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        @media (max-width: 468px){
            .footerarea {
                background-color: {{$footer->background}};
            }

            .footerarea  img{
               display: none;
            }
        }

    </style>

    <!-- <div class="footerarea" style="padding-top: 20px; background: linear-gradient(180deg, rgba(255,255,255,1) 0%, { {$footer->background}} 100%); color: { {$footer->color}}"> -->
        
    <div class="footerarea" style="padding-top: 20px; background: {{$footer->background}}; color: {{$footer->color}}">
        <div class="container">
            <div class="row">
                @if($footer->ubicacion == '')
                    @if($contactenos == null)
                        <div class="col-md-12 col-sm-12 d-flex  flex-wrap">
                            @foreach($footer->categorias  as $item)
                                <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                                    <h5 class="column-title"
                                        style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">{{$item->texto}}</h5>
                                    <aside class="">
                                        <ul id="menu-menu4" class="menu">
                                            @foreach($item->enlaces as $enlace)
                                                <li id="" class="" style="list-style: none; margin-top: 10px;"><a
                                                            style="color: {{$footer->color}}; font-size: 14px;"
                                                            href="{{$enlace->enlace}}" target="_blank"><i
                                                                class="fa fa-{{$enlace->icono}}"></i> {{' '.$enlace->texto}}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </aside>
                                </div><!--end .widget-column-2-->
                            @endforeach
                        </div>
                    @else
                        <div class="col-md-9 col-sm-12 d-flex justify-content-between flex-wrap">
                            @foreach($footer->categorias  as $item)
                                <div class="contenido col-md-6 col-sm-12" style="margin-top: 20px">
                                    <h5 class="column-title"
                                        style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">{{$item->texto}}</h5>
                                    <aside class="">
                                        <ul id="menu-menu4" class="menu">
                                            @foreach($item->enlaces as $enlace)
                                                <li id="" class="" style="list-style: none; margin-top: 10px;"><a
                                                            style="color: {{$footer->color}}; font-size: 14px"
                                                            target="_blank"
                                                            href="{{$enlace->enlace}}"><i
                                                                class="fa fa-{{$enlace->icono}}"></i> {{' '.$enlace->texto}}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </aside>
                                </div><!--end .widget-column-2-->
                            @endforeach
                        </div>
                        <div class="col-md-3 col-sm-12 d-flex justify-content-between flex-wrap">
                            <div class="contenido col-md-12 col-sm-12" style="margin:20px 0;">
                                <h5 class="column-title"
                                    style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">
                                    CONTACTENOS</h5>
                                <aside class="">
                                    {{Form::contactenos($contactenos)}}
                                </aside>
                            </div><!--end .widget-column-2-->
                        </div>
                    @endif
                @else
                    @if($contactenos == null)
                        <div class="col-md-9 col-sm-12 d-flex  flex-wrap">
                            @foreach($footer->categorias  as $item)
                                <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                                    <h5 class="column-title"
                                        style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">{{$item->texto}}</h5>
                                    <aside class="">
                                        <ul id="menu-menu4" class="menu">
                                            @foreach($item->enlaces as $enlace)
                                                <li id="" class="" style="list-style: none; margin-top: 10px;"><a
                                                            style="color: {{$footer->color}}; font-size: 14px;"
                                                            href="{{$enlace->enlace}}" target="_blank"><i
                                                                class="fa fa-{{$enlace->icono}}"></i> {{' '.$enlace->texto}}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </aside>
                                </div><!--end .widget-column-2-->
                            @endforeach
                        </div>
                        <div class="col-md-3 col-sm-12 d-flex justify-content-between flex-wrap">
                            <div class="contenido col-md-12 col-sm-12" style="margin:20px 0;">
                                <h5 class="column-title"
                                    style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">
                                    ENCUENTRANOS</h5>
                                <aside class="">
                                    {!! str_replace('width="300"','width="200"',$footer->ubicacion)!!}
                                </aside>
                            </div><!--end .widget-column-2-->
                        </div>
                    @else
                        <div class="col-md-6 col-sm-12 d-flex justify-content-between flex-wrap">
                            @foreach($footer->categorias  as $item)
                                <div class="contenido col-md-6 col-sm-12" style="margin-top: 20px">
                                    <h5 class="column-title"
                                        style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">{{$item->texto}}</h5>
                                    <aside class="">
                                        <ul id="menu-menu4" class="menu">
                                            @foreach($item->enlaces as $enlace)
                                                <li id="" class="" style="list-style: none; margin-top: 10px;"><a
                                                            style="color: {{$footer->color}};"
                                                            href="{{$enlace->enlace}}" target="_blank"><i
                                                                class="fa fa-{{$enlace->icono}}"></i> {{' '.$enlace->texto}}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </aside>
                                </div><!--end .widget-column-2-->
                            @endforeach
                        </div>
                        <div class="col-md-6 col-sm-12 d-flex justify-content-between flex-wrap">
                            <div class="contenido col-md-6 col-sm-12" style="margin:20px 0;">
                                <h5 class="column-title"
                                    style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">
                                    CONTACTENOS</h5>
                                <aside class="">
                                    {{Form::contactenos($contactenos)}}
                                </aside>
                            </div><!--end .widget-column-2-->
                            <div class="contenido col-md-6 col-sm-12" style="margin:20px 0;">
                                <h5 class="column-title"
                                    style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">
                                    ENCUENTRANOS</h5>
                                <aside class="">
                                    {!! str_replace('width="300"','width="200"',$footer->ubicacion)!!}
                                </aside>
                            </div><!--end .widget-column-2-->
                        </div>
                    @endif
                @endif
                <div class="col-md-12 col-sm-12 d-flex justify-content-between flex-wrap"
                     style="margin-top: 20px;">
                    <p style="font-size: 15px; color: {{$footer->color}}; text-align: right; width: 50%;">
                        &copy; {{$footer->texto.' '.$footer->copyright}}</p>
                    <ul style="" class="d-flex justify-content-between">
                        @foreach($redes as $red)
                            <li style="list-style: none; margin-right: 10px;">
                                <a href="{{$red->enlace}}" style="color:{{$footer->color}}; font-size: 24px;" target="_blank"><i class="fa fa-{{$red->icono}}"></i></a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div><!--end .container-->
    </div>
@else
    <div class="section-header">
        <h2 class="section-title text-center wow fadeInDown">Sección</h2>
        <p class="text-center wow fadeInDown">Sin configuración</p>
    </div>
@endif

