<?php
    $cheques = $doc_encabezado->cheques_relacionados_pagos();
?>
@if( !empty( $cheques->toArray() ) )
<table class="table table-bordered">
    <tr>
        <td style="text-align: center; background-color: #ddd;"> 
            <span style="text-align: right; font-weight: bold;"> Cheques relacionados </span> 
        </td>
    </tr>
</table>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Fecha','Número','Referencia','Banco','Cliente','Valor']) }}
        <tbody>
            <?php
                $total_cheques = 0;
            ?>
            @foreach( $cheques as $cheque )
                <?php
                    $descripcion_banco = '';
                    if( !is_null($cheque->cuenta_bancaria) )
                    {
                        $descripcion_banco = $cheque->cuenta_bancaria->descripcion;
                    }
                ?>
                <tr>
                    <td> {{ $cheque->fecha_emision }} </td>
                    <td> {{ $cheque->numero_cheque }} </td>
                    <td> {{ $cheque->referencia_cheque }} </td>
                    <td> {{ $descripcion_banco }} </td>
                    <td> {{ $cheque->tercero->descripcion }} </td>
                    <td> $ {{ number_format( $cheque->valor, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_cheques += $cheque->valor;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="5" style="text-align: right;"> Totales </td>
                <td> $ {{ number_format($total_cheques, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>
@endif