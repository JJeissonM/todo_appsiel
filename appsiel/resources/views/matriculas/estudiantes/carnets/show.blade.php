
<style type="text/css">

    body{
        font-family: Verdana, Arial, Helvetica, sans-serif !important;
    }

    @page{ margin: 30px !important; }

    table.carnet_table{
        font-size: {{$tamanio_letra}}px;
    }

    table.carnet_table, .carnet_table>tbody>tr>td{
        border: 1px solid gray;
        height: 15px;
    }

    .celda{
        border: 2px solid rgb(59, 59, 59) !important;
        background-color: rgb(255, 189, 167);
        margin: 2px 0px 2px;
        padding: 4px;
    }

    p.descripcion_colegio{
        text-align: center; 
        color: {{config('configuracion.color_principal_empresa')}};
        font-size: 1.2em;
        padding:0;
        margin:0;
    }

    p.slogan_colegio{
        text-align: center;
        color: {{config('configuracion.color_principal_empresa')}};
        font-size: 0.9em;
        opacity:0.8;
        font-style:italic;
        padding:0;
        margin:0;
    }

    .page-break {
        page-break-after: always;
    }

</style>

<div class="container" style="width: 100%;">    
    <?php
        $i = $numero_columnas;
        $minimo_comun_multiplo_columnas = 12;
        if ($tamanio_letra == null) {
            $tamanio_letra = 12;
        }

        $cant_estudiantes = count($estudiantes);
        $k = 0;
    ?>

    <table class="table" style="width: 100%;">
        <tbody>
        
            @foreach($estudiantes as $estudiante)
                @if($i % $numero_columnas == 0)
                    <tr>
                @endif

                <td colspan="{{ $minimo_comun_multiplo_columnas / $numero_columnas }}" style="padding: 5px;">
                    @include('matriculas.estudiantes.carnets.one_carnet')                        
                </td>

                <?php
                    $i++;
                    $k++;
                ?>

                @if($cant_estudiantes % $numero_columnas != 0 && $k == $cant_estudiantes)
                    <td colspan="{{ $minimo_comun_multiplo_columnas / $numero_columnas }}" style="width: 50%; padding: 5px;">
                        &nbsp;
                    </td>
                @endif

                @if($k % 8 == 0)
                    <div class="page-break"></div>
                @endif
                
                @if($i % $numero_columnas == 0)
                    </tr>
                    <!-- <tr><td colspan="2">&nbsp;</td></tr> -->
                @endif

            @endforeach
        </tbody>
    </table>
</div>