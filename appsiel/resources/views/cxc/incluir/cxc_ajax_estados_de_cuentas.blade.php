<table class="table table-striped" id="myTable">
    {{ Form::bsTableHeader( ['Inmueble','Propietario','Documento','Detalle','Fecha','Fecha Vence','Estado','Vlr. cartera','Abono','Saldo pend.'] ) }}
    <tbody>
        <?php
        


        $total_1 = 0;
        $total_2 = 0;
        $total_3 = 0;
        for($i=0;$i<count($movimiento_cxc);$i++)
        { 
            
            $propiedad = App\PropiedadHorizontal\Propiedad::where('id',$movimiento_cxc[$i]['codigo_referencia_tercero'])->where('core_empresa_id',Auth::user()->empresa_id)->get();

            if ( count($propiedad) > 0) {
                $propiedad = $propiedad[0];
            }else{
                $propiedad = (object)array('codigo' => 0);
            }

            $id = $movimiento_cxc[$i]['id'];
            ?>
            <tr id="{{ $id }}">
                <td> {{ $propiedad->codigo }}</td>
                <td> {{ $movimiento_cxc[$i]['tercero'] }} </td>
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
            <td>  </td>
            <td>  </td>
            <td> {{ number_format($total_1, 0, ',', '.') }} </td>
            <td> {{ number_format($total_2, 0, ',', '.') }} </td>
            <td> {{ number_format($total_3, 0, ',', '.') }} </td>
        </tr>
    </tbody>
</table>