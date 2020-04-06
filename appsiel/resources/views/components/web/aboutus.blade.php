<style>
    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    @-webkit-keyframes rotate {
        from {
            -webkit-transform: rotate(0deg);
        }
        to {
            -webkit-transform: rotate(360deg);
        }
    }

    .imgr {
        -webkit-animation: 50s rotate linear infinite;
        animation: 50s rotate linear infinite;
        -webkit-transform-origin: 50% 50%;
        transform-origin: 50% 50%;
    }

    .imgr {
        position: absolute;
        background-repeat: no-repeat;
        background-position: right;
        background-size: 25% 95%;
        right: -330px;
    }

    .faq-area-img {
        position: absolute;
        background-repeat: no-repeat;
        background-position: right;
        background-size: 25% 95%;
        right: -330px;
        /*-webkit-animation: ani-rotate 50s linear infinite;*/

    }

    .faq-img {
        width: 100%;
    }

    .img-fluid {
        max-width: 100%;
        height: auto;

    }
</style>
<div class="aboutus">
    <div class="container" style="margin-top: 40px;">
        <div class="imgr">
            <img src="{{asset('img/lading-page/faq-bg-1.png')}}" class="img-fluid" alt="">
        </div>
        <div class="container">
            @if($aboutus!=null)
                <div class="section-header">
                    <h2 class="section-title text-center wow fadeInDown animated"
                        style="visibility: visible; animation-name: fadeInDown;">{{$aboutus->titulo}}</h2>
                    <p class="text-center wow fadeInDown animated"
                       style="visibility: visible; animation-name: fadeInDown;">{{$aboutus->descripcion}}
                    <center><a class="btn btn-primary btn-md text-center"
                               href="{{route('aboutus.leer_institucional',$aboutus->id)}}">Leer todo</a></center>
                </div>
                <div class="row">
                    <div class="col-sm-6 wow fadeInLeft animated"
                         style="visibility: visible; animation-name: fadeInLeft;">
                        <img class="img-responsive" src="{{url($aboutus->imagen)}}" alt="">
                    </div>
                    <div class="col-sm-6">
                        <div class="media service-box wow fadeInRight animated"
                             style="visibility: visible; animation-name: fadeInRight;">
                            <div class="pull-left">
                                <i class="fa fa-{{$aboutus->mision_icono}}"></i>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">Misión</h4>
                                <p>{!! str_limit($aboutus->mision,150) !!}</p>

                                <a class="pull-right" href="{{route('aboutus.leer_institucional',$aboutus->id)}}">Leer
                                    mas...</a>
                            </div>
                        </div>

                        <div class="media service-box wow fadeInRight animated"
                             style="visibility: visible; animation-name: fadeInRight;">
                            <div class="pull-left">
                                <i class="fa fa-{{$aboutus->vision_icono}}"></i>
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">Visión</h4>
                                <p>{!! str_limit($aboutus->vision,150) !!}</p>

                                <a class="pull-right" href="{{route('aboutus.leer_institucional',$aboutus->id)}}">Leer
                                    mas...</a>
                            </div>
                        </div>
                        @if($aboutus->valores != '')
                            <div class="media service-box wow fadeInRight animated"
                                 style="visibility: visible; animation-name: fadeInRight;">
                                <div class="pull-left">
                                    <i class="fa fa-{{$aboutus->valor_icono}}"></i>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">Valores</h4>
                                    <p>{!! str_limit($aboutus->valores,150) !!}</p>

                                    <a class="pull-right" href="{{route('aboutus.leer_institucional',$aboutus->id)}}">Leer
                                        mas...</a>
                                </div>
                            </div>
                        @endif
                        @if( $aboutus->resenia != '')
                            <div class="media service-box wow fadeInRight animated"
                                 style="visibility: visible; animation-name: fadeInRight;">
                                <div class="pull-left">
                                    <i class="fa fa-{{$aboutus->resenia_icono}}"></i>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">Reseña Historica</h4>
                                    <p>{!! str_limit($aboutus->resenia,150) !!}</p>

                                    <a class="pull-right" href="{{route('aboutus.leer_institucional',$aboutus->id)}}">Leer
                                        mas...</a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="section-header">
                    <h2 class="section-title text-center wow fadeInDown">Sección</h2>
                    <p class="text-center wow fadeInDown">Sin configuración</p>
                </div>
            @endif
        </div>
    </div>
</div>