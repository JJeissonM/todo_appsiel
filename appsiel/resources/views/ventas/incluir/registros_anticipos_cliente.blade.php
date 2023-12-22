
@inject('cxc_services', 'App\Ventas\Services\CxCServices')

<h5>Registros de Anticipos</h5>
<hr>
{!! $cxc_services->get_tabla_cartera_afavor_tercero($cliente->core_tercero_id, date('Y-m-d')) !!}

<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <td> &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> Total anticipos: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> 
                <div id="div_total_anticipos" data-vlr_total_anticipos="0">$ 0</div>
            </td>
        </tr>
        <tr>
            <td> &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> Saldo pendiente (este documento): &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> 
                <div id="div_saldo_pendiente_documento" data-vlr_saldo_pendiente_documento="0">$ 0</div>
            </td>
        </tr>
    </table>
</div>