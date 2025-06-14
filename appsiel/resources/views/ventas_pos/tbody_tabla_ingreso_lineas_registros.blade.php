@foreach( $lineas_archivo_plano as $linea )
    <tr class="linea_registro" data-numero_linea="1">
        <td style="display: none;">
            <div class="inv_producto_id">{{$linea->articulo->id}}</div>
        </td>
        <td style="display: none;">
            <div class="precio_unitario">{{$linea->precio_unitario}}</div>
        </td>
        <td style="display: none;">
            <div class="base_impuesto">{{$linea->precio_unitario}}</div>
        </td>
        <td style="display: none;">
            <div class="tasa_impuesto">0</div> <!-- { {$linea->articulo->impuesto->tasa_impuesto}} -->
        </td>
        <td style="display: none;">
            <div class="valor_impuesto">0</div>
        </td>
        <td style="display: none;">
            <div class="base_impuesto_total">{{$linea->precio_unitario * $linea->cantidad}}</div>
        </td>
        <td style="display: none;">
            <div class="cantidad">{{$linea->cantidad}}</div>
        </td>
        <?php
            $precio_total = $linea->precio_unitario * $linea->cantidad;
        ?>
        <td style="display: none;">
            <div class="precio_total">{{$precio_total}}</div>
        </td>
        <td style="display: none;">
            <div class="tasa_descuento">0</div>
        </td>
        <td style="display: none;">
            <div class="valor_total_descuento">0</div>
        </td>
        <td> &nbsp; </td>
        <td> 
            <span style="background-color:#F7B2A3;">{{$linea->articulo->id}}</span> 
            <div class="lbl_producto_descripcion" style="display: inline;"> {{$linea->articulo->descripcion}} </div> 
        </td>
        <td> 
            <div style="display: inline;"> 
                <div class="elemento_modificar" title="Doble click para modificar."> {{$linea->cantidad}}</div>
            </div>  
            (<div class="lbl_producto_unidad_medida" style="display: inline;">{{$linea->articulo->get_unidad_medida1()}}</div>) 
        </td>
        <td> 
            <div class="lbl_precio_unitario" style="display: inline;">$ {{ number_format( $linea->precio_unitario, 0, ',', '.') }}</div>
        </td>
        <td>
            0% ( $<div class="lbl_valor_total_descuento" style="display: inline;">0</div> )
        </td>
        <td>
            <div class="lbl_tasa_impuesto" style="display: inline;">0%</div> <!-- { {$linea->articulo->impuesto->tasa_impuesto}} -->
        </td>
        <td> 
            <div class="lbl_precio_total" style="display: inline;">$ {{ number_format( $precio_total, 0, ',', '.') }} </div> 
        </td> 
        <td>
            <button type="button" class="btn btn-danger btn-xs btn_eliminar">
                <i class="fa fa-btn fa-trash"></i>
            </button>
        </td>
    </tr>
@endforeach