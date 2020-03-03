<div class="footerarea" style="background-color: black;">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-sm-12 d-flex flex-wrap">
                @foreach($footer->categorias  as $item)
                    <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                        <h5 class="column-title"
                            style="color: white; font-size: 20px; font-weight: bold;">{{$item->texto}}</h5>
                        <aside class="">
                            <ul id="menu-menu4" class="menu">
                                @foreach($item->enlaces as $enlace)
                                    <li id="" class="" style="list-style: none; margin-top: 10px;"><a
                                                style="color: white; font-size: 14px" href="">{{$enlace->texto}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </aside>
                    </div><!--end .widget-column-2-->
                @endforeach


                <div class="contenido col-md-4 col-sm-12" style="margin-top: 20px">
                    <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;">SÍGUENOS</h5>
                    @if($redes->count() > 0)
                        <aside class="d-flex flex-wrap">
                            @foreach($redes as $red)
                                <a href="{{$red->enlace}}" target="_blank"
                                   style=" color: white; border-radius: 50%; font-size: 28px; margin-right: 20px;"><i
                                            class="fa fa-{{$red->icono}}"></i></a>
                            @endforeach
                        </aside>
                    @endif
                </div><!--end .widget-column-2-->
            </div>
            <div class="contenido col-md-3 col-sm-12" style="margin: 20px 0;">
                <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;">CONTACTANOS</h5>
                <aside class="" style="width:300px; height:450px">
                   {{Form::contactenos($contactenos)}}
                </aside>
            </div><!--end .widget-column-2-->
            <div class="contenido col-md-3 col-sm-12" style="margin: 20px 0;">
                <h5 class="column-title" style="color: white; font-size: 20px; font-weight: bold;">ENCUENTRANOS</h5>
                <aside class="" style="width:300px; height:450px">
                    {!! str_replace('width="300"','width="200"',$footer->ubicacion)!!}
                </aside>
            </div><!--end .widget-column-2-->
        </div>
    </div><!--end .container-->
</div>

