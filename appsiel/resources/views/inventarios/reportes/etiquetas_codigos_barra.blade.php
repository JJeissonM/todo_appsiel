<?php
	$total_debitos = 0;
    $total_creditos = 0;
    $total_saldo_inicial = 0;
    $total_saldo_final = 0;
    $j=0;
?>

<h3 style="width: 100%; text-align: center;"> Lista de etiquetas de códigos de barra de items </h3>
<hr>
<div class="container-fluid">
    
    <?php
        $i=$numero_columnas;
    ?>

    @foreach($items as $fila)
      
        @if($i % $numero_columnas == 0)
            <div class="row">
        @endif

        <div class="col-sm-{{12/$numero_columnas}}">
            <div style="border: solid 1px #ddd; border-radius: 4px; padding: 5px; text-align: center;">
                @if($etiqueta != '')
                    <b>{{ $etiqueta }}</b>
                    <br>
                @endif

                @if($mostrar_descripcion)
                    <b>{{ $fila->descripcion }}</b>
                    <br>
                @endif

                
                <!-- 
                    DNS1D::getBarcodePNG( texto_codigo, tipo_codigo, ancho, alto) 

                    tipo_codigo: { C128B, C39 }

                -->
                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG( (int)$fila->codigo_barras, "EAN13", 2, 100) }}" alt="barcode" />
                <br>
                {{ $fila->codigo_barras }}
            </div>
                
        </div>

        <?php
            $i++;
        ?>

        @if($i % $numero_columnas == 0)
            </div>
            <br/><br/>
        @endif

    @endforeach
</div>