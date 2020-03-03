<section id="services">
    <div class="container">
    @if($servicios!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated"
                style="visibility: visible; animation-name: fadeInDown;">{{$servicios->titulo}}</h2>
            <p class="text-center wow fadeInDown animated"
               style="visibility: visible; animation-name: fadeInDown;">{{$servicios->descripcion}}</p>
        </div>
        <div class="row">
            @if(count($servicios->itemservicios) > 0)
                <div class="features d-flex flex-wrap">
                    @foreach($servicios->itemservicios as $item)
                        <div class="col-md-4 col-sm-6 wow fadeInUp animated" data-wow-duration="300ms"
                             data-wow-delay="0ms"
                             style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp;">
                            <div class="media service-box">
                                <div class="pull-left">
                                    <i class="fa fa-{{$item->icono}}"></i>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">{{$item->titulo}}</h4>
                                    <p>{{$item->descripcion}}</p>
                                </div>
                            </div>
                            <a class="btn btn-primary" href="{{route('servicios.leer_servicio',$item->id)}}">Leer más...</a>
                        </div><!--/.col-md-4-->
                    @endforeach
                </div>
            @endif
        </div><!--/.row-->
        @else
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">Sección</h2>
            <p class="text-center wow fadeInDown">Sin configuración</p>
        </div>
        @endif
    </div><!--/.container-->
</section>