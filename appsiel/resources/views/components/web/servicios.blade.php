<style>
    #services {
        position: relative;
        z-index: 80 !important;

        
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

    #contenedor_seccion_servicios a:hover{
        -webkit-box-shadow: 1px 1px 100px var(--color-terciario); -moz-box-shadow: 1px 1px 100px var(--color-terciario); box-shadow: 1px 1px 100px var(--color-terciario);
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

        <div id="visor_contenido_servicios">

        </div>
        <?php
            $estilo = "";
            if ($servicios != null)
            {
                if ($servicios->tipo_fondo == 'COLOR')
                {
                    $estilo = "background-color: " . $servicios->fondo . ";";
                }else {
                    $estilo = "background: url('" . $servicios->fondo . "') " . $servicios->repetir . " center " . $servicios->direccion;
                }
            }
        ?>

        <div class="p-md-5 p-2" id="contenedor_seccion_servicios" style="{{ $estilo }}">
            @if( is_null($servicios) )
                
                <section class="img">
                    <div class="section-header">
                        <h2 class="section-title text-center wow fadeInDown">Sección</h2>
                        <p class="text-center wow fadeInDown">Sin configuración</p>
                    </div>
                </section>

            @else

                @if($servicios->disposicion == 'DEFAULT')
                    <section id="services" class="img container servicios-font" >
                @else
                    
                    <section class="container servicios-font">
                @endif

                <div class="section-header">
                    <h2 class="section-title text-center wow fadeInDown animated servicios-font" style="visibility: visible; animation-name: fadeInDown;">{{$servicios->titulo}}</h2>
                    <p class="text-center wow fadeInDown animated servicios-font" style="visibility: visible; animation-name: fadeInDown;">{{$servicios->descripcion}}</p>
                </div>

                <div class="row">
                    @if(count($servicios->itemservicios) > 0)
                        <div class="features d-flex justify-content-around flex-wrap" style="width: 100%">
                            @foreach( $servicios->itemservicios as $item )
                                @if( $servicios->disposicion == 'ICONO' )
                                    @include('components.web.servicios_disposicion_icono')
                                @else
                                    @include('components.web.servicios_disposicion_imagen')
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                </section>
            @endif
        </div>
        <!--/.container-->


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