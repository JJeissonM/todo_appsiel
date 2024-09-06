<div id="div_ingreso_registros">
    <br>
            <h4>Líneas de registros <small style="color: red;"> «En cada campo presione Enter para continuar.»</small></h4>
        <hr>
        <div class="table-responsive" id="table_content">
        <table class="table table-striped" id="ingreso_registros">
            <thead>
                <tr>
                    <th data-override="inv_motivo_id" style="display: none;"></th>
                    <th data-override="inv_bodega_id" style="display: none;"></th>
                    <th data-override="inv_producto_id" style="display: none;"></th>
                    <th data-override="costo_unitario" style="display: none;"></th>
                    <th data-override="precio_unitario" style="display: none;"></th>
                    <th data-override="base_impuesto" style="display: none;"></th>
                    <th data-override="tasa_impuesto" style="display: none;"></th>
                    <th data-override="valor_impuesto" style="display: none;"></th>
                    <th data-override="base_impuesto_total" style="display: none;"></th>
                    <th data-override="cantidad" style="display: none;"></th>
                    <th data-override="costo_total" style="display: none;"></th>
                    <th data-override="precio_total" style="display: none;"></th>
                    <th data-override="tasa_descuento" style="display: none;"></th>
                    <th data-override="valor_total_descuento" style="display: none;"></th>
                    <th width="10px">&nbsp;</th>
                    <th width="280px">ITEM</th>
                    <th width="200px">MOTIVO</th>
                    <th width="35px">STOCK</th>
                    <th>CANTIDAD</th>
                    <th>PRECIO UNIT. (IVA INCLUIDO)</th>
                    <th>DCTO. (%)</th>
                    <th>DCTO. TOT. ($)</th>
                    <th>IVA</th>
                    <th>TOTAL</th>
                    <th width="10px">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                {!! $linea_registro !!}
            </tbody>
            <tfoot>
                <tr id="linea_ingreso_default">
                    <td style="display: none;"><div class="inv_motivo_id">10</div></td>
                    <td style="display: none;"><div class="inv_bodega_id"></div></td>
                    <td style="display: none;"><div class="inv_producto_id"></div></td>                    
                    <td style="display: none;"><div class="costo_unitario"></div></td>
                    <td style="display: none;"><div class="precio_unitario"></div></td>
                    <td style="display: none;"><div class="base_impuesto"></div></td>
                    <td style="display: none;"><div class="tasa_impuesto"></div></td>
                    <td style="display: none;"><div class="valor_impuesto"></div></td>
                    <td style="display: none;"><div class="base_impuesto_total"></div></td>
                    <td style="display: none;"><div class="cantidad"></div></td>
                    <td style="display: none;"><div class="costo_total"></div></td>
                    <td style="display: none;"><div class="precio_total"></div></td>
                    <td style="display: none;"><div class="tasa_descuento"></div></td>
                    <td style="display: none;"><div class="valor_total_descuento"></div></td>
                    <td> <label class="checkbox-inline" title="Activar ingreso por código de barras"><input type="checkbox" id="modo_ingreso" name="modo_ingreso" value="false"><i class="fa fa-barcode"></i></label> </td>
                    <td> 
                            <input id="inv_producto_id" data-toggle="tooltip" autocomplete="off" title="" name="inv_producto_id" type="text" data-original-title="Presione dos veces ESC para terminar."><input id="tipo_producto" name="tipo_producto" type="hidden" value="producto">
                            <div id="suggestions" style="display: none;"></div>
                    </td>
                    <td> <select id="inv_motivo_id" name="inv_motivo_id" style="background-color:#ECECE5;" disabled="disabled"><option value="10-salida">Ventas</option></select> </td>
                    <td> <input disabled="disabled" id="existencia_actual" width="15px" name="existencia_actual" type="text" style="background-color:#ECECE5;"> </td>
                    <td> <input id="cantidad" width="30px" name="cantidad" type="text" style="background-color:#ECECE5;" disabled="disabled"> </td>
                    <td> <input id="precio_unitario" name="precio_unitario" type="text"><input id="costo_unitario" name="costo_unitario" type="hidden"> </td>
                    <td> <input id="tasa_descuento" width="30px" name="tasa_descuento" type="text" value="0"> </td>
                    <td> <input id="valor_unitario_descuento" name="valor_unitario_descuento" type="text" value="0"><input id="valor_total_descuento" name="valor_total_descuento" type="text" value="0"> </td>
                    <td> <input disabled="disabled" id="tasa_impuesto" width="15px" name="tasa_impuesto" type="text" style="background-color:#ECECE5;"> </td>
                    <td> <input id="precio_total" name="precio_total" type="text"><input id="costo_total" name="costo_total" type="hidden"> </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>