<div class="imagen">
    <?php
        if ( $tercero->imagen == '') {
            $campo_imagen = 'avatar.png';
        }else{
            $campo_imagen = $tercero->imagen;
        }
        $url = config('configuracion.url_instancia_cliente')."/storage/app/fotos_terceros/".$campo_imagen.'?'.rand(1,1000);
        $imagen = '<img alt="imagen.jpg" src="'.asset($url).'" style="width: '.$width.'px; height: '.$height.'px; padding: 5px;" />';
    ?>

    {!! $imagen !!}
</div>