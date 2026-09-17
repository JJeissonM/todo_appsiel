
<tr class="success">
    <td width="35%">
        <strong> SubTotal </strong>
    </td>
    <td style="text-align: right;" colspan="2">
        <div id="lbl_sub_total_factura" style="display: inline;"> $ {{ number_format( $valor_sub_total_factura,'2',',','.') }}</div>
        <input type="hidden" name="valor_sub_total_factura" id="valor_sub_total_factura"
            value="{{$valor_sub_total_factura}}">
    </td>
</tr>
<tr class="default">
    <td width="35%">
        <strong> Comisión </strong>
    </td>
    <td style="text-align: right;">
        <input type="hidden" name="motivo_tesoreria_datafono" id="motivo_tesoreria_datafono" value="{{ (int)config('ventas_pos.motivo_tesoreria_datafono') }}">

        @if( (int)config('ventas_pos.motivo_tesoreria_datafono') != 0)

            <?php
                $motivo_datafono = App\Tesoreria\TesoMotivo::find( (int)config('ventas_pos.motivo_tesoreria_datafono') );

                if( $motivo_datafono == null)
                {
                    dd("El motivo de tesorería para la comisión del datáfono no existe. Por favor verifique la configuración de la aplicación.");
                }

                $label_motivo_datafono = ($motivo_datafono !== null) ? $motivo_datafono->descripcion : '';
            ?>
            <input type="hidden" name="motivo_tesoreria_datafono_label" id="motivo_tesoreria_datafono_label" value="{{ $label_motivo_datafono }}">
        @else
            <input type="hidden" name="motivo_tesoreria_datafono_label" id="motivo_tesoreria_datafono_label" value="">
        @endif        

        <input type="hidden" name="porcentaje_datafono" id="porcentaje_datafono" value="{{ (float)config('ventas_pos.porcentaje_datafono') }}">

        <input id="valor_datafono" name="valor_datafono" type="text" value="{{ $valor_lbl_datafono }}" class="form-control" autocomplete="off" style="background-color: white !important; border-radius: 4px;" readonly="readonly">

        <div id="lbl_datafono" style="display: inline;">  {{ number_format( $valor_lbl_datafono, 0, ',', '.') }}</div>
        <input type="hidden" name="aux_datafono" id="aux_datafono" value="{{ $valor_lbl_datafono }}">
    </td>
    <td style="width:35px;">
        <input type="checkbox" class="form-control" id="calcular_comision_datafono" >
    </td>
</tr>