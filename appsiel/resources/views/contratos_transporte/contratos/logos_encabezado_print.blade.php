<table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <td class="border" style="width: 48%;"><img style="width: 380px; height: 70px;" src="{{ asset('img/logos/min_transporte.png') }}"></td>
            <td class="border" style="width: 12%; text-align: center;"><img style="height: 70px;" src="data:image/png;base64,{{DNS2D::getBarcodePNG($url, 'QRCODE')}}" alt="barcode" /></td>
            <td class="border" style="width: 40%; text-align:center;">
                <div style="width: 100%;">
                    <div style="width: 40%;float:left;">
                        <img style="max-height: 70px;" src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$emp->imagen }}">
                    </div>
                    <div style="width: 60%;float:right;">
                        <br/>
                        <span style="color:{{ config('contrato_transporte.color_emp_label') }}; font-weight:bold;">{{ $emp->descripcion }}</span>
                    </div>
                </div>                
                <div style="width: 100%;clear:both; font-size:9px;">
                    <span style="color:{{ config('contrato_transporte.color_slogan') }}; font-weight:bold;">{{ config('contrato_transporte.slogan') }}</span></div>
            </td>
        </tr>
    </tbody>
</table>