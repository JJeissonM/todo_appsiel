<style>
    #clientes {
        position: relative;
        z-index: 80 !important;

        <?php
        if ($clientes != null) {
            if ($clientes->tipo_fondo == 'COLOR') {
                echo "background-color: " . $clientes->fondo . ";";
            } else {
        ?>background: url('{{$clientes->fondo}}') {{$clientes->repetir}} center {{$clientes->direccion}};
        <?php
            }
        }
        ?>
    }

    .clientes-font {
        @if( !is_null($clientes ) )
            @if( !is_null($clientes->configuracionfuente ) )
                font-family: <?php echo $clientes->configuracionfuente->fuente->font; ?> !important;
            @endif
        @endif
    }
    
    #clientes2 {
        z-index: 80 !important;
    }


    #clientes p {
        color: #000;
        font-weight: bold;
    }

    #clientes .container {
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

    @media (max-width: 468px) {
        .container h2 {
            font-size: 28px !important;
        }

        .container p {
            font-size: 16px !important;
        }

    }
</style>
@if($clientes!=null)
<section id="clientes" style="padding: 100px;" class="clientes-font">
    <div class="container" id="contenedor_seccion_clientes2">
        @if($clientes!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated clientes-font" style="visibility: visible; animation-name: fadeInDown;">{{$clientes->title}}</h2>
            <p class="text-center wow fadeInDown animated clientes-font" style="visibility: visible; animation-name: fadeInDown;">{{$clientes->descripcion}}</p>
        </div>
        <div class="row">
            @if(count($clientes->clienteitems) > 0)
            <div class="features d-flex justify-content-around flex-wrap">
                <div class="col-sm-12">
                    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner" role="listbox">
                            <?php $i=0; $primero=true; $total=count($clientes->clienteitems); $van=0; ?>
                            @foreach($clientes->clienteitems as $item)
                            @if($i == 0)
                            @if($primero)
                            <div class="carousel-item active">
                            <?php $primero=false; ?>
                            @else
                            <div class="carousel-item">
                            @endif
                            <div class="row">
                                <div class="col-sm-3">
                                    <img class="d-block img-fluid" src="{{$item->logo}}" alt="First slide">
                                    <div class="text-center">
                                        <h6 style="padding: 10px;" class="card-title clientes-font">@if($item->enlace!=null || $item->enlace!='')<a target="_blank" href="{{$item->enlace}}">{{$item->nombre}}</a> @else {{$item->nombre}} @endif</h6>
                                    </div>
                                </div> 
                            @else
                            <div class="col-sm-3">
                                <img class="d-block img-fluid" src="{{$item->logo}}" alt="First slide">
                                <div class="text-center">
                                    <h6 style="padding: 10px;" class="card-title clientes-font">@if($item->enlace!=null || $item->enlace!='')<a target="_blank" href="{{$item->enlace}}">{{$item->nombre}}</a> @else {{$item->nombre}} @endif</h6>
                                </div>
                            </div> 
                            @endif
                            <?php $i=$i+1; $van=$van+1; ?>
                            @if($i==4)
                            <?php $i=0; ?>
                                </div>
                            </div>
                            @else
                            @if($van==$total)
                                </div>
                            </div>
                            @endif
                            @endif
                            @endforeach
                        </div>
                        <a style="width: 5% !important;" class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                            <i style="color: #000; opacity: 0.9; font-size: 60px;" class="fa fa-arrow-left"></i>
                            <!--<span class="carousel-control-prev-icon" aria-hidden="true"></span>-->
                            <span class="sr-only">Anterior</span>
                        </a>
                        <a style="width: 5% !important;" class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                            <i style="color: #000; opacity: 0.9; font-size: 60px;" class="fa fa-arrow-right"></i>
                            <!--<span class="carousel-control-next-icon" aria-hidden="true"></span>-->
                            <span class="sr-only">Siguiente</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <!--/.row-->
        @else
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">Sección</h2>
            <p class="text-center wow fadeInDown">Sin configuración</p>
        </div>
        @endif
    </div>
    <!--/.container-->

</section>
<script type="text/javascript">

</script>
@endif