<?php
    $encabezado_doc = \App\FacturacionElectronica\Factura::find( $doc_encabezado->id );
    $documento_electronico = (new \App\FacturacionElectronica\DATAICO\FacturaGeneral( $encabezado_doc, 'factura' ))->consultar_documento();
    
    //dd($documento_electronico);
?>
@if(gettype($documento_electronico) == 'object')
    <b>CUFE: &nbsp;&nbsp;</b> {{ $documento_electronico->cufe }}
    <br>
    <p style="width: 100%; text-align: center;">
        <img style="height: 150px; display: inline;" src="data:image/png;base64,{{DNS2D::getBarcodePNG($documento_electronico->qrcode, 'QRCODE')}}" alt="barcode" />
    </p>
@endif