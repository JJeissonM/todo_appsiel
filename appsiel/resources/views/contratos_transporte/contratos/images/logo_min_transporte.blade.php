<?php
    $source = "https://appsiel.com/el_software/assets/images/logo_min_transporte.jpg";
    switch (config('contratos_transporte.logo_min_transporte')) {
        case 'super_transporte':
            $source = "https://appsiel.com/el_software/assets/images/logo_min_transporte.jpg";
            break;
        case 'solo_transporte':
            $source = "https://appsiel.com/el_software/assets/images/solo_transporte.jpg";
            break;
        
        default:
            # code...
            break;
    }
?>
<img style="max-width: 500px; height: 75px; margin-top: 5px;"  src="{{ $source }}">