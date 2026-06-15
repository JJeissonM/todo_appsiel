<div class="row" align="center" style="text-align: center; font-style: oblique;">
    <b><u>{{ $titulo_registros }}</u></b>
</div>

<?php
    $total_abono = 0;
    $nit_tercero_anterior = $doc_encabezado->numero_identificacion;
?>
@foreach($doc_registros as $linea )
    <?php
        $detalle = '--';
        if ( $linea->detalle_operacion != 0 ) {
            $detalle = $linea->detalle_operacion;
        }

        $caja_o_banco = '';
        $lbl_caja_o_banco = '';
        if($linea->caja != null)
        {
            $caja_o_banco = $linea->caja;
            $lbl_caja_o_banco = 'Caja';
        }
        if($linea->cuenta_bancaria != null)
        {
            $caja_o_banco = $linea->cuenta_bancaria;
            $lbl_caja_o_banco = 'Banco';
        }
    ?>
    <div class="row">
        @if( $lbl_caja_o_banco != '' )
            <b>{{ $lbl_caja_o_banco }}: </b> {{ $caja_o_banco }}
            <br>
        @endif
        @if( $nit_tercero_anterior != $linea->numero_identificacion )
            <b> Tercero: </b> {{ $linea->tercero_nombre_completo }}
            <br>
        @endif
        <b>Motivo / Detalle: </b> {{ $linea->motivo }} / {{ $detalle }}
        <br>
        <b>Valor: </b>$ {{ number_format( $linea->valor, 0, ',', '.') }}
    </div>
    <hr>
    <?php
        $total_abono += $linea->valor;
        $nit_tercero_anterior = $linea->numero_identificacion;
    ?>
@endforeach

@if( $doc_encabezado->core_tipo_transaccion_id != 43 ) {{-- Si no es un traslado de efectivo, se muestra el total del recaudo --}}
    <div class="row" align="center">
        <b>{{ $lbl_total }}: </b> $ {{ number_format($total_abono, 0, ',', '.') }}
    </div>
@endif