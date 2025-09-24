@if( $errores_einvoice == '' && isset($json_dataico->invoice) )
    <b>CUFE: &nbsp;&nbsp;</b> {{ $json_dataico->invoice->cufe }}
    <br>
    <p style="width: 100%; text-align: center;">
        <img style="height: 150px; display: inline;" src="data:image/png;base64,{{DNS2D::getBarcodePNG($json_dataico->invoice->qrcode, 'QRCODE')}}" alt="barcode" />
    </p>
    <br>
    Proveedor Tecnológico DATAICO SAS 901223648
@endif

@if( $resultado_envio != null )

    @if( (int)$resultado_envio->esValidoDian )
        <b>CUFE: &nbsp;&nbsp;</b> {{ $resultado_envio->cufe }}
        <br>
        <p style="width: 100%; text-align: center;">
            <img style="height: 150px; display: inline;" src="data:image/png;base64,{{DNS2D::getBarcodePNG($resultado_envio->QRcode, 'QRCODE')}}" alt="barcode" />
        </p>
        <br>
        Generado por: Software propio - NIT: {{ $empresa->numero_identificacion }}
    @endif
@endif