
<style type="text/css">
    p { margin: -2px; }
</style>

<h3 style="width: 100%; text-align: center;"> Lista de etiquetas de códigos de barra de items </h3>
<hr>

VER EL SIGUIENTE VIDEO PARA CONTINUAR CON LA IMPRESIÓN DEL CÓDIGO DE BARRAS; SI YA CONOCE EL PROCESO, PUEDE OMITIR.
<a href="https://youtu.be/e9KWVpHbLzc" target="_blank" style="font-size: 1.2em;">📹 VIDEO TUTORIAL</a>

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