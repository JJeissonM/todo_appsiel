<div style="border: solid 1px #ddd; border-radius: 4px; padding: 5px; text-align: center;">
    @if($etiqueta != '')
        <p>
            <b>{{ $etiqueta }}</b>
        </p>
    @endif

    @if($mostrar_descripcion)
        <p>
            <b>{{ $fila->descripcion }}</b>
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
        $alto_codigo = 100;
        if ( $numero_columnas == 4) {
            $ancho_codigo = 1.35;
            $alto_codigo = 80;
        }

        if ($ancho != '') {
            $ancho_codigo = $ancho / 100;
        }
        if ($alto != '') {
            $alto_codigo = $alto;
        }
    ?>
    <!-- 
        DNS1D::getBarcodePNG( texto_codigo, tipo_codigo, ancho, alto) 
        tipo_codigo: { C128B, C39 }
    -->
    <p style="margin-left: -26px;">
        <!-- Solo se envian los 12 primeros digitos, la function getBarcodePNG dibuja el codigo de barras con el digito de control al final -->
        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG( substr($codigo_barras,0,12), "EAN13", $ancho_codigo, $alto_codigo) }}" alt="barcode"/>
    </p>
    <p>
        {{ $codigo_barras . $fila->get_codigo_proveedor() }}
    </p>
</div>


