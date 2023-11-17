
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
        <strong> Propina </strong>
    </td>
    <td style="text-align: right;">
        <input type="hidden" name="motivo_tesoreria_propinas" id="motivo_tesoreria_propinas" value="{{ (int)config('ventas_pos.motivo_tesoreria_propinas') }}">

        @if( (int)config('ventas_pos.motivo_tesoreria_propinas') != 0)
            <input type="hidden" name="motivo_tesoreria_propinas_label" id="motivo_tesoreria_propinas_label" value="{{ App\Tesoreria\TesoMotivo::find( (int)config('ventas_pos.motivo_tesoreria_propinas') )->descripcion }}">
        @else
            <input type="hidden" name="motivo_tesoreria_propinas_label" id="motivo_tesoreria_propinas_label" value="">
        @endif        

        <input type="hidden" name="porcentaje_propina" id="porcentaje_propina" value="{{ (float)config('ventas_pos.porcentaje_propina') }}">

        <input id="valor_propina" name="valor_propina" type="text" value="{{ $valor_lbl_propina }}" class="form-control" autocomplete="off" style="background-color: white !important; border-radius: 4px;">

        <div id="lbl_propina" style="display: inline;">  {{ number_format( $valor_lbl_propina, 0, ',', '.') }}</div>
        <input type="hidden" name="aux_propina" id="aux_propina" value="{{ $valor_lbl_propina }}">
    </td>
    <td style="width:35px;">
        <button type="button" class="btn btn-danger btn-xs" id="remove_tip"><i class="fa fa-close"></i></button>

    </td>
</tr>