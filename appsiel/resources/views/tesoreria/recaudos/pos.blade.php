@extends('transaccion.formatos_impresion.pos')

@section('lbl_tercero')
    Tercero:
@endsection

@section('documento_transaccion_prefijo_consecutivo')
    {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
@endsection

@section('encabezado_datos_adicionales')
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
@endsection

@section('tabla_registros_1')   

    <div class="row" align="center" style="text-align: center; font-style: oblique;">
        <b><u>Motivos registrados</u></b>
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
            <b>{{ $lbl_caja_o_banco }}: </b> {{ $caja_o_banco }}
            <br>
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
    <div class="row" align="center">
        <b>Total documento: </b> $ {{ number_format($total_abono, 0, ',', '.') }}
    </div>
@endsection

@section('lbl_firma')
    Firma del aceptante:
@endsection


</body>
</html>