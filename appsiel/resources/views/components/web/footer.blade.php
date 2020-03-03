@if($footer!=null)
<div class="footerarea" style="background-color: {{$footer->background}}; height: 100vh">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-sm-12 d-flex justify-content-between flex-wrap">
                @foreach($footer->categorias  as $item)
                    <div class="contenido col-md-6 col-sm-12" style="margin-top: 20px">
                        <h5 class="column-title"
                            style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">{{$item->texto}}</h5>
                        <aside class="">
                            <ul id="menu-menu4" class="menu">
                                @foreach($item->enlaces as $enlace)
                                    <li id="" class="" style="list-style: none; margin-top: 10px;"><a
                                                style="color: {{$footer->color}}; font-size: 14px" href="">{{$enlace->texto}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </aside>
                    </div><!--end .widget-column-2-->
                @endforeach
            </div>
            <div class="col-md-6 col-sm-12 d-flex justify-content-between flex-wrap">
                <div class="contenido col-md-6 col-sm-12" style="margin:20px 0;">
                    <h5 class="column-title" style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">CONTACTENOS</h5>
                    <aside class="">
                        {{Form::contactenos($contactenos)}}
                    </aside>
                </div><!--end .widget-column-2-->
                <div class="contenido col-md-6 col-sm-12" style="margin:20px 0;">
                    <h5 class="column-title" style="color: {{$footer->color}}; font-size: 20px; font-weight: bold;">ENCUENTRANOS</h5>
                    <aside class="" >
                        {!! str_replace('width="300"','width="200"',$footer->ubicacion)!!}
                    </aside>
                </div><!--end .widget-column-2-->
            </div>
            <div class="col-md-12 col-sm-12 d-flex justify-content-between flex-wrap" style="height: 150px; margin-top: 20px;">
                <p style="font-size: 20px; color: {{$footer->color}}">&copy; {{$footer->texto.' '.$footer->copyright}}</p>
                <ul style="" class="d-flex justify-content-between">
                    @foreach($redes as $red)
                        <li style="list-style: none; margin-right: 10px;">
                            <a href="" style="color:white; font-size: 30px;"><i class="fa fa-{{$red->icono}}"></i></a>
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

