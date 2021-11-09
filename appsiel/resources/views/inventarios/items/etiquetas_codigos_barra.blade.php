
<style type="text/css">
    p { margin: 1px; }
</style>

<div class="container-fluid">
    
    <?php
        $numero_columnas = 3;
        $i = 3;
        $minimo_comun_multiplo_columnas = 12;
    ?>

    <table class="table" style="width: 100%; font-size: 12px;">
        <tbody>
            
        
            @foreach($items as $fila)
              
                @if($i % $numero_columnas == 0)
                    <tr>
                @endif

                <td colspan="{{ $minimo_comun_multiplo_columnas / $numero_columnas }}">
                    <div style="padding: 5px; text-align: center;">

                        <p>
                            <b>{{ substr( $fila->descripcion, 0, 20) }}</b>
                        </p>

                        
                        <!-- 
                            DNS1D::getBarcodePNG( texto_codigo, tipo_codigo, ancho, alto) 

                            tipo_codigo: { C128B, C39 }

                        -->
                        <p>
                            <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG( (int)$fila->codigo_barras, "EAN13", 2, 100) }}" alt="barcode" />
                        </p>
                        {{ $fila->codigo_barras }}
                    </div>
                        
                </td>

                <?php
                    $i++;
                ?>

                @if($i % $numero_columnas == 0)
                    </tr>
                @endif

            @endforeach
        </tbody>
    </table>
</div>