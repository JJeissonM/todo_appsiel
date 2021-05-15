<br/><br/><br/>
<h4 style="width: 100%; text-align: center;">Ingreso de productos</h4>
<table class="table table-striped" id="ingreso_productos">
    <thead>
        <tr>
            <th data-override="inv_producto_id">Cod.</th>
            <th width="280px">Producto</th>
            <th width="200px" data-override="motivo">Motivo</th>
            <th data-override="costo_unitario"> Costo Unit. </th>
            <th data-override="cantidad">Cantidad</th>
            <th data-override="costo_total">Costo Total</th>
            <th width="10px">&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td class="nom_prod"></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>

        	<!-- Cuando se está haciendo ajuste desde Inventario Físico -->
        	@if( isset($filas_tabla) )
        		{!! $filas_tabla !!}
        		{{ Form::hidden('hay_productos_aux', $cantidad_filas,['id'=>'hay_productos_aux']) }}
        	@endif

    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">&nbsp;</td>
            <td align="center"> <div id="total_cantidad"> 0 </div> </td>
            <td align="right"> <div id="total_costo_total"> $0</div> </td>
            <td> &nbsp;</td>
        </tr>
        <tr>
            <td colspan="5">
            	<!-- Trigger the modal with a button -->
				<button type="button" class="btn btn-info btn-xs" id="btn_nuevo" style="display:none;"><i class="fa fa-btn fa-plus"></i> Agregar productos </button>
			</td>
            <td colspan="2">
            	@if($id_transaccion==4)
            		<button type="button" class="btn btn-warning btn-xs" id="btn_calcular_costos_finales"><i class="fa fa-btn fa-calculator"></i> Calcular costos</button>
            	@else
            		&nbsp;
            	@endif
			</td>
        </tr>
    </tfoot>
</table>