<div style="border: solid 1px #ddd; border-radius: 4px; margin: 10px 10px 4px 10px; text-align: center; width:100%;">

    @if($mostrar_descripcion)

        <?php 
            $mystring = $fila->descripcion;
            $findme   = '(';
            $pos = strpos($mystring, $findme);
            $descripcion = substr($mystring,0,$pos);
        ?>
        <p style="margin: 1px 0px -15px; width:100%;">
            <b>{{ strtoupper(substr( $descripcion, 0, 34)) }}</b>
        </p>
    @endif
    
    <?php
        
        $codigo_barras = $fila->codigo_barras;

        if ( !is_numeric($codigo_barras) ) {
            dd('El ítem ' .  $fila->descripcion . ' NO tiene un código de barras válido: ' . $fila->codigo_barras . '. Debe contener solo números.');
        }

        $ancho_codigo = 2;
        $alto_codigo = 86;        
    ?>
    <p style="margin-bottom: -1px; margin-left: -25px; width:100%; text-align: center;">
        <!-- Solo se envian los 12 primeros digitos, la function getBarcodePNG dibuja el codigo de barras con el digito de control al final -->
        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG( substr($codigo_barras,0,12), "EAN13", $ancho_codigo, $alto_codigo) }}" alt="barcode"  style="display:inline;"/>
    </p>
    {{ $codigo_barras . $fila->get_codigo_proveedor() . $fila->get_talla() }}
</div>


