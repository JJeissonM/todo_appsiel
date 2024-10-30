<div style="border: solid 1px #ddd; border-radius: 4px; padding: 1px; text-align: center; width:100%;">
    @if($etiqueta != '')
        <p style="margin-bottom: -5px; width:100%;">
            <b>{{ $etiqueta }}</b>
        </p>
    @endif

    @if($mostrar_descripcion)
        <p style="margin-bottom: -7px; width:100%;">
            <b>{{ substr($fila->descripcion,0,34) }}</b>
        </p>
    @endif
    
    <?php
        
        $codigo_barras = $fila->codigo_barras;
        
        if( $fila->codigo_barras == '' )
        {
            $codigo_barras = (new \App\Inventarios\Services\CodigoBarras($fila->id, 0, 0, 0))->barcode;
        }

        if ( !is_numeric($codigo_barras) ) {
            dd('El ítem ' .  $fila->descripcion . ' NO tiene un código de barras válido: ' . $fila->codigo_barras . '. Debe contener solo números.');
        }

        $ancho_codigo = 2;
        $alto_codigo = 95;

        if ($ancho != '') {
            $ancho_codigo = $ancho / 100;
        }
        if ($alto != '') {
            $alto_codigo = $alto;
        }

        
    ?>
    <p style="margin-bottom: -1px; margin-left: -25px; width:100%; text-align: center;">
        <!-- Solo se envian los 12 primeros digitos, la function getBarcodePNG dibuja el codigo de barras con el digito de control al final -->
        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG( substr($codigo_barras,0,12), "EAN13", $ancho_codigo, $alto_codigo) }}" alt="barcode"  style="display:inline;"/>
    </p>
    {{ $codigo_barras . $fila->get_codigo_proveedor() }}
</div>


