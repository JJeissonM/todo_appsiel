@php
    $es_bodega_especifica = !in_array($bodega, ['VARIAS', 'NINGUNA', ''], true);
@endphp

<table style="width: 100%; border-collapse: collapse; font-size: 15px; margin-bottom: 0;" border="1">
    <tr>
        <td style="width: 58%; padding: 12px 10px; text-align: center; vertical-align: middle;">
            <h4 style="margin: 0;">
                Listado de Existencias
            </h4>
        </td>
        <td style="width: 42%; padding: 12px 10px; vertical-align: middle; line-height: 1.5;">
            <div><strong>Fecha corte:</strong> {{ $fecha_corte }}</div>
            <div><strong>Cantidad de registros:</strong> {{ $cantidad_registros }}</div>
        </td>
    </tr>
    @if(!empty($filtros))
        <tr>
            <td colspan="2" style="padding: 8px 10px; vertical-align: top;">
                @foreach($filtros as $etiqueta => $valor)
                    <div><strong>{{ $etiqueta }}:</strong> {{ $valor }}</div>
                @endforeach
            </td>
        </tr>
    @endif
</table>
    
