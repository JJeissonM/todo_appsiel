{{-- Vista parcial: lista de EAs pendientes del proveedor para seleccionar y asignar --}}
<table class="table table-bordered table-condensed table-hover" style="font-size:13px; margin-bottom:0;">
    <thead style="background:#f5f5f5;">
        <tr>
            <th style="width:36px; text-align:center;">
                <input type="checkbox" id="chk_ea_all" title="Seleccionar todas">
            </th>
            <th>Documento</th>
            <th>Fecha</th>
            <th>Detalle</th>
            <th style="text-align:right;">Valor (sin IVA)</th>
            <th style="text-align:right;">Valor (con IVA)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($entradas as $ea)
        <tr>
            <td style="text-align:center;">
                <input type="checkbox" class="chk_ea_item" value="{{ $ea->id }}">
            </td>
            <td>
                <a href="#" target="_blank">
                    {{ $ea->documento_transaccion_prefijo_consecutivo }}
                </a>
            </td>
            <td>{{ $ea->fecha }}</td>
            <td>{{ str_limit($ea->descripcion, 50) }}</td>
            <td style="text-align:right;">${{ number_format($ea->total_documento, 2, ',', '.') }}</td>
            <td style="text-align:right;">${{ number_format($ea->total_documento_mas_iva, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div style="margin-top:10px; text-align:right;">
    <button type="button" class="btn btn-success btn-sm" id="btn_confirmar_asignar_ea"
            data-factura="{{ $compras_doc_encabezado_id }}">
        <i class="fa fa-link"></i> Asignar seleccionadas a esta factura
    </button>
</div>
