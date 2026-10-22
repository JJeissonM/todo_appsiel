@if(in_array((int)Input::get('id_transaccion'), [25, 48]) && (new \App\Compras\Services\ReteicaService())->habilitado())
<?php $retenciones_ica = (new \App\Compras\Services\ReteicaService())->retenciones_activas(); ?>
<tr id="fila_reteica">
    <td style="text-align:right"><span id="reteica_etiqueta">Ret. ICA</span></td>
    <td colspan="2">
        <div id="reteica_editor" style="display:none">
            <label class="sr-only" for="reteica_select">Tarifa ReteICA</label>
            <select id="reteica_select" class="form-control" aria-label="Tarifa ReteICA">
                <option value="0">Seleccione una retención</option>
                @foreach($retenciones_ica as $retencion_ica)
                    <option value="{{ $retencion_ica->id }}" data-tasa="{{ $retencion_ica->tasa_retencion }}">{{ $retencion_ica->descripcion }} ({{ (float)$retencion_ica->tasa_retencion * 10 }} por mil)</option>
                @endforeach
            </select>
            <label class="sr-only" for="reteica_preview">Valor ReteICA</label>
            <input id="reteica_preview" class="form-control" readonly value="0.00" aria-label="Valor calculado ReteICA">
            <label for="reteica_base">Base de retención</label>
            <input type="number" id="reteica_base" name="reteica_base" form="form_create" class="form-control" min="0" max="9999999999999.99" step="0.01" value="0.00">
            <input type="hidden" id="reteica_base_manual" name="reteica_base_manual" form="form_create" value="0">
            <small>Se calcula sin IVA. Puede modificarla manualmente.</small>
        </div>
        <span id="reteica_importe"></span>
    </td>
    <td style="white-space:nowrap">
        <button type="button" id="reteica_add" class="btn btn-link" title="Aplicar ReteICA" aria-label="Aplicar ReteICA"><i class="fa fa-plus-circle"></i></button>
        <button type="button" id="reteica_confirmar" class="btn btn-link text-success" style="display:none" title="Confirmar ReteICA" aria-label="Confirmar ReteICA"><i class="fa fa-check"></i></button>
        <button type="button" id="reteica_editar" class="btn btn-link" style="display:none" title="Editar ReteICA" aria-label="Editar ReteICA"><i class="fa fa-pencil"></i></button>
        <button type="button" id="reteica_reset" class="btn btn-link" style="display:none" title="Eliminar ReteICA" aria-label="Eliminar ReteICA"><i class="fa fa-trash"></i></button>
    </td>
</tr>
@endif
