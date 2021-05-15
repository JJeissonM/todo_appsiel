<?php  
    $i = 1;
?>
@foreach( $doc_registros as $linea )
	<tr id="fila_{{$i}}" class="linea_registro">
		<td style="display: none;">
			<div class="inv_motivo_id">{{$linea->vtas_motivo_id}}</div>
		</td>
		<td style="display: none;">
			<div class="inv_bodega_id"> 1 </div>
		</td>
		<td style="display: none;">
			<div class="inv_producto_id">{{$linea->inv_producto_id}}</div>
		</td>
		<td style="display: none;">
			<div class="costo_unitario"> 0 </div>
		</td>
		<td style="display: none;">
			<div class="precio_unitario">{{$linea->precio_unitario}}</div>
		</td>
		<td style="display: none;">
			<div class="base_impuesto">{{$linea->base_impuesto}}</div>
		</td>
		<td style="display: none;">
			<div class="tasa_impuesto">{{$linea->tasa_impuesto}}</div>
		</td>
		<td style="display: none;">
			<div class="valor_impuesto">{{$linea->valor_impuesto}}</div>
		</td>
		<td style="display: none;">
			<div class="base_impuesto_total">{{$linea->base_impuesto * $linea->cantidad}}</div>
		</td>
		<td style="display: none;">
			<div class="cantidad">{{$linea->cantidad}}</div>
		</td>
		<td style="display: none;">
			<div class="costo_total">0</div>
		</td>
		<td style="display: none;">
			<div class="precio_total">{{$linea->precio_total}}</div>
		</td>
		<td style="display: none;">
			<div class="tasa_descuento">{{$linea->tasa_descuento}}</div>
		</td>
		<td style="display: none;">
			<div class="valor_total_descuento">{{$linea->valor_total_descuento}}</div>
		</td>
		<td> &nbsp; </td>
		<td>
			<span style="background-color:#F7B2A3;">{{$linea->inv_producto_id}}</span>{{$linea->producto->descripcion}}
		</td>
		<td>
			{{$linea->motivo->descripcion}}
		</td>
		<td>  
			0
		</td>
		<td>
			<div style="display: inline;"><div class="elemento_modificar" title="Doble click para modificar.">{{$linea->cantidad}}</div></div>
		</td>
		<td>  
			$ <div style="display: inline;"><div class="elemento_modificar" title="Doble click para modificar.">{{$linea->precio_unitario}}</div></div>
		</td>
		<td>
			<div style="display: inline;"><div class="elemento_modificar" title="Doble click para modificar.">{{ $linea->tasa_descuento}}</div></div> % 
		</td>
		<td>  
			${{ $linea->valor_total_descuento}}
		</td>
		<td>
			{{$linea->tasa_impuesto}}
		</td>
		<td>  
			${{ $linea->precio_total}}
		</td>
		<td>
			<button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button>
		</td>
	</tr>
	<?php  $i++; ?>
@endforeach