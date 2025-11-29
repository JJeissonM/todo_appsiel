
<?php

    $image = getimagesize($url);
    $ancho = $image[0];            
    $alto = $image[1];		
    
    $img = '';

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

?>
{!! $img !!}