@if( $errores_einvoice == '' )
    <b>CUFE: &nbsp;&nbsp;</b> {{ $json_dataico->invoice->cufe }}
    <br>
    <p style="width: 100%; text-align: center;">
        <img style="height: 150px; display: inline;" src="data:image/png;base64,{{DNS2D::getBarcodePNG($json_dataico->invoice->qrcode, 'QRCODE')}}" alt="barcode" />
    </p>
    <br>
    Proveedor Tecnológico DATAICO SAS 901223648
@endif