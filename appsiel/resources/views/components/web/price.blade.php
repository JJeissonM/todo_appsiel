<style>
    #Price {
        position: relative;
        z-index: 80 !important;
        padding: 100px 0 75px;

        <?php
        if ($Price != null) {
            if ($Price->tipo_fondo == 'COLOR') {
                echo "background-color: " . $Price->fondo . ";";
            } else {
        ?>background: url('{{$Price->fondo}}') {{$Price->repetir}} center {{$Price->direccion}};
        <?php
            }
        }
        ?>
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
@if($Price!=null)
<section id="Price">
    <div id="visor_contenido_servicios">

    </div>
    <div class="container" id="contenedor_seccion_servicios">
        @if($Price!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown; color: {{$Price->title_color}} !important;">{{$Price->title}}</h2>
            <p class="text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown; color: {{$Price->description_color}} !important;">{{$Price->description}}</p>
        </div>
        <div class="row">
            @if(count($Price->priceitems) > 0)
            @foreach($Price->priceitems as $item)
            <!-- Price member -->
            <div class="col-xs-12 col-sm-6 col-md-4 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
                <div style="border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px #cf9ec3; -moz-box-shadow: 1px 1px 100px #cf9ec3; box-shadow: 1px 1px 100px #cf9ec3;">
                    <div style="background-color: {{$item->background_color}}; border-top-right-radius: 20px !important; border-top-left-radius: 20px !important;"><img style="width: 100%;" src="{{asset($item->imagen_cabecera)}}"></div>
                    <div style="background-color: {{$item->background_color}}; padding: 20px; border-bottom-right-radius: 20px !important; border-bottom-left-radius: 20px !important;">
                        <h4 class="media-heading" style="margin-top: 0px; color: {{$item->text_color}} !important;">{{$item->precio}}</h4>
                        <?php
                        if ($item->lista_items != 'null') {
                            $lista = json_decode($item->lista_items);
                            foreach($lista as $l){
                                echo "<p style='color: ".$item->text_color." !important;'><i style='color: ".$item->button_color." !important;' class='fa fa-".$l->icono."'></i> ".$l->item."</p>";
                            }
                        }else{
                            echo "<p style='color: ".$item->text_color." !important;'>No hay información en este plan</p>";
                        }
                        ?>
                        <a style="background-color: {{$item->button_color}} !important; border-color: {{$item->button2_color}} !important;" class="btn btn-primary animate btn-block" href="{{$item->url}}">DESCUBRE EL PLAN...</a>
                    </div>
                </div>
            </div>
            <!-- ./Price member -->
            @endforeach
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
@endif
<script type="text/javascript">
    
</script>