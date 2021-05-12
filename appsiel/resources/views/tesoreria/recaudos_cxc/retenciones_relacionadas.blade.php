<?php
    $retenciones = $doc_encabezado->retenciones_relacionadas();
?>
@if( !empty( $retenciones->toArray() ) )
<table class="table table-bordered contenido">
    <tr>
        <td style="text-align: center; background-color: #ddd;"> 
            <span style="text-align: right; font-weight: bold;"> RETENCIONES SUFRIDAS </span> 
        </td>
    </tr>
</table>
<div class="table-responsive contenido">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Retenciones','Categorías','Valor']) }}
        <tbody>
            <?php
                $total_retenciones = 0;
            ?>
            @foreach( $retenciones as $retencion )
                <tr>
                    <td> {{ $retencion->retencion->nombre_corto }}, {{ $retencion->retencion->descripcion }} </td>
                    <td> {{ $retencion->retencion->categoria_retencion->nombre_corto }}, {{ $retencion->retencion->categoria_retencion->descripcion }} </td>
                    <td class="text-right"> $ {{ number_format( $retencion->valor, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_retenciones += $retencion->valor;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="2" style="text-align: right;"> Totales </td>
                <td class="text-right"> $ {{ number_format($total_retenciones, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>
@endif