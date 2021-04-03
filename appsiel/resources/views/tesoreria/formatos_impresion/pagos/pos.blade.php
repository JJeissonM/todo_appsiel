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

    <div class="row" align="center" style="text-align: center; background-color: #ddd;">
        <b>Conceptos pagados</b>
    </div>

    <?php
        $total_abono = 0;
        $nit_tercero_anterior = $doc_encabezado->numero_identificacion;
    ?>
    @foreach($doc_registros as $linea )
        <?php 
        ?>
        <div class="row">
            @if( $nit_tercero_anterior != $linea->numero_identificacion )
                <b> Tercero: </b> {{ $linea->tercero_nombre_completo }}
                <br>                
            @endif
            <b>Motivo / Detalle: </b> {{ $linea->motivo }} / {{ $linea->detalle_operacion }}
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
        <b>Total pagado: </b> $ {{ number_format($total_abono, 0, ',', '.') }}
    </div>
@endsection

@section('lbl_firma')
    Firma del aceptante:
@endsection


</body>
</html>