<tr style="background: #4a4a4a; color: white;">
    <td colspan="{{$colspan2}}">
        <strong>Sub-total</strong>
    </td>
    <td>
        <strong> ${{ number_format($total_valor_documento_tercero, 2, ',', '.') }} </strong>
    </td>
    <td>
        <strong> ${{ number_format($total_valor_pagado_tercero, 2, ',', '.') }} </strong>
    </td>
    <td>
        <strong> ${{ number_format($total_saldo_pendiente_tercero, 2, ',', '.') }} </strong>
    </td>
</tr>
<tr style="background-color: white;"><td colspan="{{$colspan1}}">&nbsp;</td></tr>