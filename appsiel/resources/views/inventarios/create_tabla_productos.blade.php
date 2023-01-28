<br/><br/><br/>
<h4 style="width: 100%; text-align: center;">Ingreso de productos</h4>
<table class="table table-striped table-bordered" id="ingreso_productos">
    <thead>
        <tr>
            <th style="display:none;">linea_registro_doc_origen_id</th>
            <th data-override="inv_producto_id">COD.</th>
            <th width="280px">PRODUCTO</th>
            <th width="200px" data-override="motivo">MOTIVO</th>
            <th data-override="costo_unitario"> COSTO UNIT. </th>
            <th data-override="cantidad">CANT.</th>
            <th data-override="costo_total">COSTO TOTAL</th>
            <th width="10px">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="display:none;"></td>
            <td width="100px"></td>
            <td class="nom_prod"></td>
            <td></td>
            <td width="150px"></td>
            <td width="150px"></td>
            <td width="150px"></td>
            <td></td>
        </tr>

        	<!-- Cuando se está haciendo ajuste desde Inventario Físico -->
        	@if( isset($filas_tabla) )
        		{!! $filas_tabla !!}
        	@endif

    </tbody>
    <tfoot>
        <tr id="fila_totales">
            <td colspan="4">&nbsp;</td>
            <td class="text-center"> <div id="total_cantidad"> 0 </div> </td>
            <td class="text-right"> <div id="total_costo_total"> $0</div> </td>
            <td> &nbsp;</td>
        </tr>
        <tr>
            <td colspan="5">
            	<!-- Trigger the modal with a button -->
				<button type="button" class="btn btn-info btn-xs" id="btn_nuevo" style="display:none;"><i class="fa fa-btn fa-plus"></i> Agregar productos </button>
			</td>
            <td colspan="2">
            	@if($id_transaccion==4)
                <button type="button" class="btn btn-primary btn-xs" id="btn_cargar_ingredientes"><i class="fa fa-btn fa-arrow-up"></i> Cargar ingredientes</button>
                <button type="button" class="btn btn-warning btn-xs" id="btn_calcular_costos_finales"><i class="fa fa-btn fa-calculator"></i> Calcular costos</button>
            	@else
            		&nbsp;
            	@endif
			</td>
        </tr>
    </tfoot>
</table>