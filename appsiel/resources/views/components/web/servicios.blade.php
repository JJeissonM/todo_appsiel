<style>
    #services {
        position: relative;
        z-index: 80 !important;

        <?php
        if ($servicios != null) {
            if ($servicios->tipo_fondo == 'COLOR') {
                echo "background-color: " . $servicios->fondo . ";";
            } else {
        ?>background: url('{{$servicios->fondo}}') {{$servicios->repetir}} center {{$servicios->direccion}};
        <?php
            }
        }
        ?>
    }

    .servicios-font {
        @if( !is_null($servicios) )
            @if( !is_null($servicios->configuracionfuente ) )
                font-family: <?php echo $servicios->configuracionfuente->fuente->font; ?> !important;
            @endif
        @endif
    }


    #servicios {
        z-index: 80 !important;
    }


    #services p {
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

    @media (max-width: 468px) {
        .container h2 {
            font-size: 28px !important;
        }

        .container p {
            font-size: 16px !important;
        }

    }
</style>
@if($servicios!=null)
@if($servicios->disposicion == 'DEFAULT')
<section id="services" class="img">
    @else
    <section id="services" class="servicios-font">
        <!-- <img src="{ {asset('img/corazon/Diseño_Appsiel_3.jpg')}}" alt=""> -->
        @endif
        @else
        <section id="services" class="img">
            @endif

            <div id="visor_contenido_servicios">

            </div>

            <div class="container" id="contenedor_seccion_servicios">
                @if($servicios!=null)
                <div class="section-header">
                    <h2 class="section-title text-center wow fadeInDown animated servicios-font" style="visibility: visible; animation-name: fadeInDown;">{{$servicios->titulo}}</h2>
                    <p class="text-center wow fadeInDown animated servicios-font" style="visibility: visible; animation-name: fadeInDown;">{{$servicios->descripcion}}</p>
                </div>
                <div class="row">
                    @if(count($servicios->itemservicios) > 0)
                    <div class="features d-flex justify-content-around flex-wrap">
                        @foreach($servicios->itemservicios as $item)
                        @if($servicios->disposicion=='ICONO')
                        <div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
                            <div style="background-color: #f8f8f8; padding: 20px; border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px var(--color-terciario); -moz-box-shadow: 1px 1px 100px var(--color-terciario); box-shadow: 1px 1px 100px var(--color-terciario); opacity: 0.8;" class="col-md-12">
                                <div style="border-top: 10px solid; border-color: #7bb0e7; top: 0;"></div>
                                <div class="media service-box" style="height: 150px;margin: 20px 0;">
                                    <div class="pull-left">
                                        <i class="fa fa-{{$item->icono}}"></i>
                                    </div>
                                    <div class="media-body">
                                        <h4 class="media-heading servicios-font" style="margin-top: 0px;">{{$item->titulo}}</h4>
                                        <p class="servicios-font">{!! str_limit($item->descripcion,90) !!} </p>
                                    </div>
                                </div>
                                <div class="pull-right">
                                    @if($item->url!='')
                                    <a class="btn btn-primary animate btn-sm servicios-font" href="{{$item->url}}" style="cursor: pointer; color: #fff;">Ver <i class="fa fa-plus"></i></a>
                                    @else
                                    <a class="btn btn-primary animate btn-sm servicios-font" onclick="visor_contenido_servicios({{ $item->id }})" style="cursor: pointer; color: #fff;">Ver <i class="fa fa-plus"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- TIPO SANTILLANA -->
                        <div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
                            <div style="border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px var(--color-terciario); -moz-box-shadow: 1px 1px 100px var(--color-terciario); box-shadow: 1px 1px 100px var(--color-terciario);">
                                <div style="background-color: #fff; border-top-right-radius: 20px !important; border-top-left-radius: 20px !important;"><img style="width: 100%; border-radius: 20px 20px 0 0" src="{{asset($item->icono)}}"></div>
                                <div style="background-color: #fff; padding: 20px; border-bottom-right-radius: 20px !important; border-bottom-left-radius: 20px !important;">
                                    <h4 class="media-heading servicios-font" style="margin-top: 0px;">{{$item->titulo}}</h4>
                                    <p class="servicios-font">{!! str_limit($item->descripcion,90) !!} </p>
                                    <div class="pull-right">
                                        @if($item->url!='')
                                        <a class="btn btn-primary animate btn-sm servicios-font" href="{{$item->url}}" style="cursor: pointer; color: #fff;">Ver <i class="fa fa-plus"></i></a>
                                        @else
                                        <a class="btn btn-primary animate btn-sm servicios-font" onclick="visor_contenido_servicios({{ $item->id }})" style="cursor: pointer; color: #fff;">Ver <i class="fa fa-plus"></i></a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <!--/.col-md-4-->
                        @endforeach
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
            function visor_contenido_servicios(item_id) {
                $('#visor_contenido_servicios').html('');

                $('#contenedor_seccion_servicios').fadeOut(1000);

                var url = "{{url('/servicios')}}" + '/' + item_id + '/index';

                $.get(url).done(function(data) {
                        $('#visor_contenido_servicios').html(data);
                        $('#visor_contenido_servicios').fadeIn(500);
                    }).fail(function() {
                        $('#contenedor_seccion_servicios').fadeIn(500);
                        $('#visor_contenido_servicios').show();
                        $('#visor_contenido_servicios').html('<p style="color:red;">Elemento no puede ser mostrado. Por favor, intente nuevamente.</p>');
                    });
            }


            function ver_contenedor_seccion_servicios() {
                $('#contenedor_seccion_servicios').fadeIn(500);
                $('#visor_contenido_servicios').html('');
                $('#visor_contenido_servicios').hide();
            }
        </script>