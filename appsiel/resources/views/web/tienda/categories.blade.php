<style>
#exampleSlider {
    position: relative;      /* recommended */
}
#exampleSlider .MS-content {
    white-space: nowrap;     /* required */
    overflow: hidden;        /* required */
    margin: 0 5%;            /* makes room for L/R arrows */
}
#exampleSlider .MS-content .item {
    display: inline-block;   /* required */
    width: 50%;              /* required * Determines number of visible slides */
    position: relative;      /* required */
    vertical-align: top;     /* required */
    overflow: hidden;        /* required */
    height: 100%;            /* recommended */
    white-space: normal;     /* recommended */
}
@media (min-width: 768px) {
    #exampleSlider .MS-content .item {
        width: 33.3%;
    }
}
@media (min-width: 1200px) {
    #exampleSlider .MS-content .item {
        width: 20%;
    }
}

#exampleSlider .MS-controls button {
    position: absolute;      /* recommended */
    top: 0px;
    font-size: 25px
}
#exampleSlider .MS-controls .MS-left {
    left: 10px;
    border: none;
    height: 100%;
}
#exampleSlider .MS-controls .MS-right {
    right: 10px;
    border: none;
    height: 100%;
}
</style>
<!-- multislider -->
<script src="{{asset('assets/web/js/jquery-2.2.4.min.js')}}"></script>
<script src="{{asset('assets/web/js/multislider.js')}}"></script>

<div class="row"  class="font-oswald">
    <div class="col-left sidebar col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="block block-layered-nav h-100">
            <div class="block-content">  
                    <div id="exampleSlider">      <!-- Give wrapper ID to target with jQuery & CSS -->
                        <div class="MS-content">
                            @foreach($grupos as $key => $value)
                                <div class="item text-center">
                                        <?php 
                                            $url_imagen_producto = '#';
                                            if ( $value[0]->imagen != '' )
                                            {
                                                $url_imagen_producto = asset( config('configuracion.url_instancia_cliente') . 'storage/app/inventarios/' . $value[0]->imagen );
                                            }
                                        ?>
                                        
                                        <a style="display: flex; flex-direction: column; justify-content: space-between" class="ajaxLayer" onclick="filtrar_categoria('{{ $value[0]->id }}', this)" >
                                            
                                            <img class="my-4" src="{{ $url_imagen_producto }}" alt="{{ $url_imagen_producto }}" style="height: 80px; object-fit: contain">
                                            
                                            <span style="text-transform: uppercase; font-size: 16px; font-weight: bold">{{$key}} ({{$value->count()}})</span>
                                        </a>
                                    
                                </div>
                            @endforeach
                        </div>
                        <div class="MS-controls">
                            <button class="MS-left"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                            <button class="MS-right"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </dl>                  
        </div>
    </div>
</div>

<script type="text/javascript">    
    $('#exampleSlider').multislider({
        interval:false
    });
</script>    

