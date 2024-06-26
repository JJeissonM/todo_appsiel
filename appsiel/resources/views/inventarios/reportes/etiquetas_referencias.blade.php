
<style type="text/css">
    p { margin: -2px; }
</style>

<h3 style="width: 100%; text-align: center;"> Lista de etiquetas de Referencias de prendas </h3>
<hr>
<div class="container-fluid">
    
    <?php
        $i=$numero_columnas;
        $minimo_comun_multiplo_columnas = 12;
    ?>

    <table class="table" style="width: 100%; font-size: 12px;">
        <tbody>            
        
            @foreach($items as $fila)
              
                @if($i % $numero_columnas == 0)
                    <tr>
                @endif

                <td colspan="{{ $minimo_comun_multiplo_columnas / $numero_columnas }}">
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

                        @if($mostrar_precio_ventas)
                            <p style="font-size:120%;font-family:cursive">
                                <b>$ {{ number_format($fila->get_precio_venta(), 0, ',', '.') }}</b>
                            </p>
                        @endif
                        
                        {{ $fila->referencia }}
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