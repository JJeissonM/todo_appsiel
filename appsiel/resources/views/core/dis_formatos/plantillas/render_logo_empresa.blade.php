
<?php

    $img = '';

    $image = false;
    $local_path = null;
    if (is_string($url)) {
        $path_prefix = '/storage/app/logos_empresas/';
        $pos = strpos($url, $path_prefix);
        if ($pos !== false) {
            $file = substr($url, $pos + strlen($path_prefix));
            $file = strtok($file, '?#');
            $local_path = storage_path('app/logos_empresas/'.$file);
        }
    }

    if ($local_path && file_exists($local_path)) {
        $image = @getimagesize($local_path);
    } else {
        $image = @getimagesize($url);
    }

    if ($image === false) {
        $img = '';
    } else {
        $ancho = $image[0];            
        $alto = $image[1];

        if($ancho >= $alto ){
        $pancho = (160*100)/$ancho;
        $alto = $alto*$pancho/100;				
        if($alto > 100){
            $ancho = 160;
            $palto = (100*100)/$alto;
            $ancho = $ancho*$palto/100;
            $img = '<img src="'.$url.'" width="'.$ancho.'" height="100" style="margin-left: 10px" />';
        }else{
            $img = '<img src="'.$url.'" height="'.$alto.'" width="160" style="margin-left: 10px" />';
        }
        }else{
        $palto = (100*100)/$alto;
        $ancho = $ancho*$palto/100;
        $img = '<img src="'.$url.'" width="'.$ancho.'" height="100" style="margin-left: 10px" />';
        }
    }

?>
{!! $img !!}
