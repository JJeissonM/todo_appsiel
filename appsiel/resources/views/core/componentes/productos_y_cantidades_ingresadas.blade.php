<script src="{{ asset( 'assets/js/core/commons.js?aux=' . uniqid() )}}"></script>

<?php 
    
    if ( !isset($numero_lineas) ) {
        $numero_lineas = 0;
    }
    if ( !isset($cantidades_ingresadas) ) {
        $cantidades_ingresadas = 0;
    }
?>

<table>
    <tr>
        <td style="text-align: right; font-weight:700;">Productos ingresados:</td>
        <td style="text-align: left; padding-left:5px;"><span id="numero_lineas"> {{ $numero_lineas }} </span></td>
    </tr>
    <tr>
        <td style="text-align: right; font-weight:700;">Cantidades ingresadas:</td>
        <td style="text-align: left; padding-left:5px;"><span id="cantidades_ingresadas"> {{ $cantidades_ingresadas }} </span></td>
    </tr>
</table>