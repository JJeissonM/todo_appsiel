<b>Condición de pago: &nbsp;&nbsp;</b> {{ ucfirst($doc_encabezado->condicion_pago) }}
@if( $doc_encabezado->condicion_pago == 'credito' )
    <br/>
    <b>Fecha vencimiento: &nbsp;&nbsp;</b> {{ $doc_encabezado->fecha_vencimiento }}
@else
    <br/>
	<b>Medio de pago: &nbsp;&nbsp;</b> {{ $doc_encabezado->medio_pago() }}
    <br/>
	<b>Caja/Banco: &nbsp;&nbsp;</b> {{ $doc_encabezado->caja_banco() }}
@endif