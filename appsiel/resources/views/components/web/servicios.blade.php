<style>

    #services {
        position: relative;
    }

    #services img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }


    #services p{
        color: #000;
        font-weight: bold;  
    }

    #services .container {
        position: relative;
        z-index: 1000;
    }


    .ilustracion {
        position: absolute;
        top: 0;
        left: 0;
        width: 60%;
        height: 550px;
    }

    @media (max-width: 468px){
        .container h2{
            font-size: 28px !important;
        }

        .container p {
            font-size: 16px !important;
        }

        #services img {
            display: none;
        }

    }

</style>
@if($servicios->disposicion == 'DEFAULT')
    <section id="services" class="img">
@else
    <section id="services">
        <!-- <img src="{ {asset('img/corazon/Diseño_Appsiel_3.jpg')}}" alt=""> -->
@endif
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
                                <div class="col-md-4 col-sm-6 wow fadeInUp animated service-info"
                                     data-wow-duration="300ms"
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
                                        <a class="btn btn-primary"
                                           href="{{route('servicios.leer_servicio',$item->id)}}">Leer
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
        @if($servicios->disposicion == 'DEFAULT')
            <!-- <img class="ilustracion" src="{ {asset('img/lading-page/bg-2.svg')}}" alt=""> -->
        @endif
    </section>