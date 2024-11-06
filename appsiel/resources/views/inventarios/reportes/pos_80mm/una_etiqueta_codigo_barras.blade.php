<div style="width: 100%; text-align:left;">
    <div style="border: solid 1.2px black; border-radius: 4px; margin: {{$top_margin}}px 10px 4px 10px; text-align: left; font-size:{{$tamanio_letra}}px; height:118px; width:228px;">

        @if($mostrar_descripcion)

            <?php 
                $mystring = $fila->descripcion;
                $findme   = '(';
                $pos = strpos($mystring, $findme);
                $descripcion = substr($mystring,0,$pos);

                //dd($mystring, $findme, $pos,  $descripcion, strtoupper(substr( $descripcion, 0, 30)));
            ?>
            <p style="margin: 4px 0px -12px; width:100%; padding-left: 6px;">
                <b>{{ strtoupper(substr( $descripcion, 0, 34)) }}</b>
            </p>
        @endif
        
        <?php
            
            $codigo_barras = $fila->codigo_barras;

            if ( !is_numeric($codigo_barras) ) {
                dd('El ítem ' .  $fila->descripcion . ' NO tiene un código de barras válido: ' . $fila->codigo_barras . '. Debe contener solo números.');
            }

            $ancho_codigo = 1.9;
            $alto_codigo = 80;        
        ?>
        <p style="margin-bottom: -1px; margin-left: -25px; padding-left: 15px; width:100%; text-align: left;">
            <!-- Solo se envian los 12 primeros digitos, la function getBarcodePNG dibuja el codigo de barras con el digito de control al final -->
            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG( substr($codigo_barras,0,12), "EAN13", $ancho_codigo, $alto_codigo) }}" alt="barcode"  style="display:inline;"/>
        </p>
        <span style="padding-left: 45px;">
            {{ $codigo_barras . $fila->get_codigo_proveedor() . $fila->get_talla() }}
        </span>
    </div>
</div>


