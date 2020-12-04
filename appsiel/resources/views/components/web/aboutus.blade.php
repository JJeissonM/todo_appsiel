<style>

    #aboutus {
        position: relative;
        z-index: 80 !important;

        <?php
        if ($aboutus != null) {
            if ($aboutus->tipo_fondo == 'COLOR') {
                echo "background-color: " . $aboutus->fondo . ";";
            } else {
        ?>background: url('{{$aboutus->fondo}}') {{$aboutus->repetir}} center {{$aboutus->direccion}};
        <?php
            }
        }
        ?>
    }

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
<div class="aboutus" id="aboutus" style="padding-top: 100px;">

    <div id="visor_contenido_aboutus">

    </div>

    <div class="container" id="contenedor_seccion_aboutus">
        <div class="container">
            @if($aboutus!=null)
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">{{$aboutus->titulo}}</h2>
                <p class="text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">
                    {{$aboutus->descripcion}}
                </p>
                <!-- <center><a class="btn btn-primary btn-md text-center"
                               href="{ {route('aboutus.leer_institucional',$aboutus->id)}}">Leer todo</a></center> -->
            </div>
            <div class="row">
                <div class="col-sm-6 wow fadeInLeft animated" style="visibility: visible; animation-name: fadeInLeft; text-align: center; padding-top: 200px !important;">
                    <img class="img-responsive" src="{{url($aboutus->imagen)}}" alt="">
                </div>
                <div class="col-sm-6">
                    <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight; border-radius: 20px; border: 1px solid; padding: 15px;">
                        <div class="pull-left">
                            <i class="fa fa-{{$aboutus->mision_icono}}"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">Misión</h4>
                            <p>{!! str_limit($aboutus->mision,110) !!}</p>

                            <a class="pull-right btn btn-primary" onclick="visor_contenido_aboutus({{ $aboutus->id }})" href="#">Leer
                                mas...</a>
                        </div>
                    </div>

                    <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight; border-radius: 20px; border: 1px solid; padding: 15px;">
                        <div class="pull-left">
                            <i class="fa fa-{{$aboutus->vision_icono}}"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">Visión</h4>
                            <p>{!! str_limit($aboutus->vision,110) !!}</p>

                            <a class="pull-right btn btn-primary" onclick="visor_contenido_aboutus({{ $aboutus->id }})" href="#">Leer
                                mas...</a>
                        </div>
                    </div>
                    @if($aboutus->valores != '')
                    <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight; border-radius: 20px; border: 1px solid; padding: 15px;">
                        <div class="pull-left">
                            <i class="fa fa-{{$aboutus->valor_icono}}"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">Valores</h4>
                            <p>{!! str_limit($aboutus->valores,110) !!}</p>

                            <a class="pull-right btn btn-primary" onclick="visor_contenido_aboutus({{ $aboutus->id }})" href="#">Leer
                                mas...</a>
                        </div>
                    </div>
                    @endif
                    @if( $aboutus->resenia != '')
                    <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight; border-radius: 20px; border: 1px solid; padding: 15px;">
                        <div class="pull-left">
                            <i class="fa fa-{{$aboutus->resenia_icono}}"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">Reseña Historica</h4>
                            <p>{!! str_limit($aboutus->resenia,110) !!}</p>

                            <a class="pull-right btn btn-primary" onclick="visor_contenido_aboutus({{ $aboutus->id }})" href="#">Leer
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

<script type="text/javascript">
    function visor_contenido_aboutus(item_id) {
        $('#visor_contenido_aboutus').html('');

        $('#contenedor_seccion_aboutus').fadeOut(1000);

        var url = "{{url('/aboutus')}}" + '/' + item_id + '/institucional/leer';

        $.get(url)
            .done(function(data) {

                $('#visor_contenido_aboutus').html(data);
                $('#visor_contenido_aboutus').fadeIn(500);
            })
            .error(function() {

                $('#contenedor_seccion_aboutus').fadeIn(500);
                $('#visor_contenido_aboutus').show();
                $('#visor_contenido_aboutus').html('<p style="color:red;">Elemento no puede ser mostrado. Por favor, intente nuevamente.</p>');
            });
    }


    function ver_contenedor_seccion_aboutus() {
        $('#contenedor_seccion_aboutus').fadeIn(500);
        $('#visor_contenido_aboutus').html('');
        $('#visor_contenido_aboutus').hide();
    }
</script>