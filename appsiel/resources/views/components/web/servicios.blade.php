<style>

    #services {
        position: relative;
    }

    #services .container {
        position: relative;
       z-index: 1000;
    }

    .ilustracion{
        position: absolute;
        top:0;
        left: 0;
        width: 60%;
        height: 550px;
    }

</style>

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
                    <div class="features d-flex justify-content-around flex-wrap">
                        @foreach($servicios->itemservicios as $item)
                            <div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms"
                                 data-wow-delay="0ms"
                                 style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
                                <div class="media service-box" style="height: 150px">
                                    <div class="pull-left">
                                        <i class="fa fa-{{$item->icono}}"></i>
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading">{{$item->titulo}}</h4>
                                        <p>{!! str_limit($item->descripcion,150) !!} </p>
                                    </div>
                                </div>
                                <div class="col-md-12 d-flex justify-content-end">
                                    <a class="btn btn-primary" href="{{route('servicios.leer_servicio',$item->id)}}">Leer
                                        más...</a>
                                </div>
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
    <img class="ilustracion" src="{{asset('img/lading-page/bg-2.svg')}}" alt="">
</section>