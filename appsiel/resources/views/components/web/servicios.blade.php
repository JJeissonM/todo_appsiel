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

        <div id="visor_contenido_servicios">

        </div>

        <div class="container" id="contenedor_seccion_servicios">
            @if($servicios!=null)
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">{{$servicios->titulo}}</h2>
                <p class="text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">{{$servicios->descripcion}}</p>
            </div>
            <div class="row">
                @if(count($servicios->itemservicios) > 0)
                <div class="features d-flex justify-content-around flex-wrap">
                    @foreach($servicios->itemservicios as $item)
                    <div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
                        <div style="background-color: #f8f8f8; padding: 20px;  -webkit-box-shadow: 5px 5px 10px -8px rgba(0,0,0,0.75);
-moz-box-shadow: 5px 5px 10px -8px rgba(0,0,0,0.75);
box-shadow: 5px 5px 10px -8px rgba(0,0,0,0.75);" class="col-md-12">
                            <div style="border-top: 10px solid; border-color: #7bb0e7; top: 0;"></div>
                            <div class="media service-box" style="height: 150px">
                                <div class="pull-left">
                                    <i class="fa fa-{{$item->icono}}"></i>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">{{$item->titulo}}</h4>
                                    <p>{!! str_limit($item->descripcion,120) !!} </p>
                                </div>
                            </div>
                            <div class="pull-right">
                                <a class="btn btn-primary animate" onclick="visor_contenido_servicios({{ $item->id }})" href="#">Leer más...</a>
                            </div>
                        </div>
                    </div>
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
        @if($servicios->disposicion == 'DEFAULT')
        <!-- <img class="ilustracion" src="{ {asset('img/lading-page/bg-2.svg')}}" alt=""> -->
        @endif
    </section>
    <script type="text/javascript">
        function visor_contenido_servicios(item_id) {
            $('#visor_contenido_servicios').html('');

            $('#contenedor_seccion_servicios').fadeOut(1000);

            var url = "{{url('/servicios')}}" + '/' + item_id + '/index';

            $.get(url)
                .done(function(data) {

                    $('#visor_contenido_servicios').html(data);
                    $('#visor_contenido_servicios').fadeIn(500);
                })
                .error(function() {

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