<table style="display: none;">
    <tr id="linea_ingreso_default_aux">
        <td style="display: none;">
            <div class="inv_producto_id"></div>
        </td>
        <td style="display: none;">
            <div class="precio_unitario"></div>
        </td>
        <td style="display: none;">
            <div class="base_impuesto"></div>
        </td>
        <td style="display: none;">
            <div class="tasa_impuesto"></div>
        </td>
        <td style="display: none;">
            <div class="valor_impuesto"></div>
        </td>
        <td style="display: none;">
            <div class="base_impuesto_total"></div>
        </td>
        <td style="display: none;">
            <div class="cantidad"></div>
        </td>
        <td style="display: none;">
            <div class="precio_total"></div>
        </td>
        <td style="display: none;">
            <div class="tasa_descuento"></div>
        </td>
        <td style="display: none;">
            <div class="valor_total_descuento"></div>
        </td>
        <td>
            <button id="btn_listar_items" style="border: 0; background: transparent;"><i
                        class="fa fa-btn fa-search"></i></button>
        </td>
        <td>
            {{ Form::text( 'inv_producto_id', null, [ 'class' => 'form-control', 'id' => 'inv_producto_id', 'autocomplete' => 'off' ] ) }}
        </td>
        <td>
            <input class="form-control" id="cantidad" width="30px" name="cantidad" type="text" autocomplete="off">
            <span id="existencia_actual" style="display: none; color:#574696; font-size:0.9em;"></span>
        </td>
        <td>
            @can('bloqueo_cambiar_precio_unitario')
                <input class="form-control" id="precio_unitario" name="precio_unitario" type="text" readonly="readonly">
            @else
                <input class="form-control" id="precio_unitario" name="precio_unitario" type="text">
            @endcan
        </td>
        <td>
            @can('bloqueo_cambiar_tasa_descuento')
                <input class="form-control" id="tasa_descuento" width="30px" name="tasa_descuento" type="text" readonly="readonly">
            @else
                <input class="form-control" id="tasa_descuento" width="30px" name="tasa_descuento" type="text">
            @endcan
        </td>
        <td>
            <input class="form-control" id="tasa_impuesto" width="30px" name="tasa_impuesto" type="text">
        </td>
        <td>
            <input class="form-control" id="precio_total" name="precio_total" type="text">
        </td>
        <td></td>
    </tr>
</table>