<?php
    $propiedad = App\PropiedadHorizontal\Propiedad::where('id',$movimiento_cxc[0]['codigo_referencia_tercero'])->where('core_empresa_id',Auth::user()->empresa_id)->get()[0];

    $elaboro = Auth::user()->email;
?>
<table style="font-size: 15px; border: 1px solid; border-collapse: collapse;" width="100%">
    <tr>
        <td width="50%" style="border: solid 1px black; padding-top: -20px;">
            <div>
                @include('core.dis_formatos.plantillas.banner_logo_datos_empresa')
            </div>
        </td>
        <td style="border: solid 1px black; padding-top: -20px;">
            <div style="vertical-align: center;">
                <b style="font-size: 1.4em; text-align: center; display: block;">
                    Estado de cuentas
                </b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border: solid 1px black;">
            @php $fecha = explode("-", date('Y-m-d') ) @endphp
            <b>Fecha generación: </b> &nbsp; {{ $fecha[2] }} de {{ Form::NombreMes([$fecha[1]]) }} de {{ $fecha[0] }}
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border: solid 1px black;">
            <b style="width: 100%;text-align: center;">Propietario </b>
            <br/>
            <b>Nombre: </b>{{ $movimiento_cxc[0]['tercero'] }}
            <br/>
            <b>Teléfono: </b>{{ $movimiento_cxc[0]['telefono'] }}
            <br/>
            <b>Email: </b>{{ $movimiento_cxc[0]['email'] }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>{{ $propiedad->tipo_propiedad }}: </b> {{ $propiedad->nomenclatura }}
        </td>
        <td style="border: solid 1px black;">
            <b>Cód. inmueble: </b> {{ $propiedad->codigo }}
        </td>
    </tr>
</table>
<table style="font-size: 14px; border: 1px solid; border-collapse: collapse;" border="1" width="100%">
    <thead>
        <tr style="background: #ccc; font-weight: bold; text-align: center;">
            <th> Doc. </th>
            <th> Detalle </th>
            <th> Fecha </th>
            <th> Fecha Vence </th>
            <th> Estado </th>
            <th> Vlr. cartera </th>
            <th> Pagado </th>
            <th> Saldo pend. </th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_1 = 0;
        $total_2 = 0;
        $total_3 = 0;
        for($i=0;$i<count($movimiento_cxc);$i++)
        { 
            $id = $movimiento_cxc[$i]['id'];
            ?>
            <tr id="{{ $id }}">
                <td> {{ $movimiento_cxc[$i]['documento'] }} </td>
                <td> {{ $movimiento_cxc[$i]['detalle_operacion'] }} </td>
                <td> {{ $movimiento_cxc[$i]['fecha'] }} </td>
                <td> {{ $movimiento_cxc[$i]['fecha_vencimiento'] }} </td>
                <td> {{ $movimiento_cxc[$i]['estado'] }} </td>
                <td class="col_valor_cartera"> {{ number_format($movimiento_cxc[$i]['valor_cartera'], 0, ',', '.') }} </td>
                <td class="col_valor_pagado"> {{ number_format($movimiento_cxc[$i]['valor_pagado'], 0, ',', '.') }} </td>
                <td class="col_saldo_pendiente" > {{ number_format($movimiento_cxc[$i]['saldo_pendiente'], 0, ',', '.') }} </td>
            </tr>
        <?php 
            $total_1+=$movimiento_cxc[$i]['valor_cartera'];
            $total_2+=$movimiento_cxc[$i]['valor_pagado'];
            $total_3+=$movimiento_cxc[$i]['saldo_pendiente'];
        } 
        ?>
        <tr>
            <td>  </td>
            <td>  </td>
            <td>  </td>
            <td>  </td>
            <td>  </td>
            <td> {{ number_format($total_1, 0, ',', '.') }} </td>
            <td> {{ number_format($total_2, 0, ',', '.') }} </td>
            <td> {{ number_format($total_3, 0, ',', '.') }} </td>
        </tr>
    </tbody>
</table>
<br><br>
<table width="100%" style="margin-top: 3px;">
    <tr>
        <td width="15%"> </td>
        <td width="30%"> _______________________ </td>
        <td width="10%"> </td>
        <td width="30%"> _______________________ </td>
        <td width="15%"> </td>
    </tr>
    <tr>
        <td width="15%"> </td>
        <td width="30%"> Impreso por: {{ explode("@",$elaboro)[0] }} </td>
        <td width="10%"> </td>
        <td width="30%"> &nbsp; </td>
        <td width="15%"> </td>
    </tr>
</table>