
<style type="text/css">
    p { margin: -2px; }
</style>

<h3 style="width: 100%; text-align: center;"> Lista de etiquetas de códigos de barra de items </h3>
<hr>
<div class="container-fluid">
    
    <?php
        $i=$numero_columnas;
        $minimo_comun_multiplo_columnas = 12;
        if ($tamanio_letra == null) {
            $tamanio_letra = 12;
        }
    ?>

    <table class="table" style="width: 100%; font-size: {{$tamanio_letra}}px;">
        <tbody>
        
            @foreach($items as $fila)
              
                @if($i % $numero_columnas == 0)
                    <tr>
                @endif

                <td colspan="{{ $minimo_comun_multiplo_columnas / $numero_columnas }}">
                    @include('inventarios.reportes.una_etiqueta_codigo_barras')                        
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