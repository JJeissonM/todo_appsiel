<table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <td class="border" style="width: 48%;"><img style="width: 380px; height: 70px;" src="{{ asset('img/logos/min_transporte.png') }}"></td>
            <td class="border" style="width: 12%; text-align: center;"><img style="height: 70px;" src="data:image/png;base64,{{DNS2D::getBarcodePNG($url, 'QRCODE')}}" alt="barcode" /></td>
            <td class="border" style="width: 40%; text-align:center;"><img style="max-height: 70px;" src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$emp->imagen }}"></td>
        </tr>
    </tbody>
</table>