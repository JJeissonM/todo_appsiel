
<?php

    $img = '';

    $image = false;
    $local_path = null;
    if (is_string($url)) {
        $path_prefixes = [
            '/storage/app/logos_empresas/',
            'storage/app/logos_empresas/'
        ];

        foreach ($path_prefixes as $path_prefix) {
            $pos = strpos($url, $path_prefix);
            if ($pos === false) {
                continue;
            }

            $file = substr($url, $pos + strlen($path_prefix));
            $file = strtok($file, '?#');

            if ($file === false || $file === '') {
                continue;
            }

            $local_path = storage_path('app/logos_empresas/'.$file);
            break;
        }
    }

    if ($local_path && file_exists($local_path)) {
        $image = @getimagesize($local_path);
    }

    if ($image === false) {
        if (!empty($url)) {
            $img = '<img src="'.$url.'" style="margin-left: 10px; max-width: 160px; max-height: 100px;" />';
        }
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
