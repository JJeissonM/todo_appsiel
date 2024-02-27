<table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <td class="border" style="width: 38%;"><img style="width: 250px; height: 70px;" src="https://www.mintransporte.gov.co/info/mintransporte/media/galeria/thumbs/thgaleria_220X220_19757.jpg" ></td>
            <td class="border" style="width: 12%; text-align: center;"><img style="height: 70px;" src="data:image/png;base64,{{DNS2D::getBarcodePNG($url, 'QRCODE')}}" alt="barcode" /></td>

            <?php 
                $porcentaje_ancho_ultima_celda = '50%';
            ?>
            @if( config('contratos_transporte.url_imagen_sello_icontec') != '')
                <td class="border" style="width: 12%; text-align: center;"><img style="width: 90px; height: 90px;" src="{{config('contratos_transporte.url_imagen_sello_icontec')}}" /></td>
                <?php 
                    $porcentaje_ancho_ultima_celda = '38%';
                ?>
            @endif
            <td class="border" style="width: {{$porcentaje_ancho_ultima_celda}}; text-align:center;">
                <div style="width: 100%;">
                    @if( config('contratos_transporte.color_emp_label') != '')
                        <div style="width: 40%;float:left;">
                            <img style="max-height: 70px; max-width: 250px;" src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'. $empresa->imagen }}">
                        </div>
                        <div style="width: 60%;float:right;">
                            <br/>
                            <span style="color:{{ config('contratos_transporte.color_emp_label') }}; font-weight:bold; font-size:10px;">{{ $empresa->descripcion }}</span>
                        </div>
                    @else
                        <div style="width: 100%;">
                            <img style="max-height: 70px; max-width: 250px;" src="{{ asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'. $empresa->imagen }}">
                        </div>
                    @endif
                </div>
                @if( config('contratos_transporte.slogan') != '')
                    <div style="width: 100%;clear:both; font-size:9px;">
                        <span style="color:{{ config('contratos_transporte.color_slogan') }}; font-weight:bold;">{{ config('contratos_transporte.slogan') }}</span>
                    </div>
                @endif
            </td>
        </tr>
    </tbody>
</table>