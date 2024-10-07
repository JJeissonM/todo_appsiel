<script src="{{ asset( 'assets/js/core/commons.js?aux=' . uniqid() )}}"></script>

<?php 
    
    if ( !isset($numero_lineas) ) {
        $numero_lineas = 0;
    }
    if ( !isset($cantidades_ingresadas) ) {
        $cantidades_ingresadas = 0;
    }
?>

<b>Productos ingresados &nbsp;:</b> <span id="numero_lineas"> {{ $numero_lineas }} </span>
<br/>
<b>Cantidades ingresadas:</b> <span id="cantidades_ingresadas"> {{ $cantidades_ingresadas }} </span>