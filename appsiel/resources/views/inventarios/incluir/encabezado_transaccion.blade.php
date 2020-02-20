<table class="table table-bordered tabla_pdf">
    <tr>
        <td>
            <h2 align="center">{{ $descripcion_transaccion }}</h2>
        </td>
        <td>
            <div style="vertical-align: center;">
                <br/>
                <b>Documento:</b> {{ $datos_encabezado_doc['campo2'] }}
                <br/>
                <b>Fecha:</b> {{ $datos_encabezado_doc['campo1'] }}
                <?php 
                    $reg_fatura_venta = App\Ventas\VtasDocEncabezado::where('remision_doc_encabezado_id',$datos_encabezado_doc['campo9'])->get()->first();

                    if( !is_null($reg_fatura_venta) )
                    {
                        $fatura_venta = App\Ventas\VtasDocEncabezado::get_registro_impresion( $reg_fatura_venta->id );
                        echo '<br/>
                                <b>Factura de ventas: </b> <a href="'.url('ventas/'.$fatura_venta->id.'?id=13&id_modelo=139').'" target="_blank">'.$fatura_venta->documento_transaccion_prefijo_consecutivo.'</a>';
                    }
                ?>
                <?php 
                    $reg_fatura_compras = App\Compras\ComprasDocEncabezado::where('entrada_almacen_id',$datos_encabezado_doc['campo9'])->get()->first();

                    if( !is_null($reg_fatura_compras) )
                    {
                        $fatura_compra = App\Compras\ComprasDocEncabezado::get_registro_impresion( $reg_fatura_compras->id );
                        echo '<br/>
                                <b>Factura de compras: </b> <a href="'.url('compras/'.$fatura_compra->id.'?id=9&id_modelo=147').'" target="_blank">'.$fatura_compra->documento_transaccion_prefijo_consecutivo.'</a>';
                    }
                ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <b>Bodega:</b> {{ $datos_encabezado_doc['campo7'] }}
        </td>
        <td>
            <b>Tercero:</b> {{ $datos_encabezado_doc['campo3'] }}
        </td>
    </tr>
    <tr>
        <td>
            <b>Detalle:</b> {{ $datos_encabezado_doc['campo6'] }}
        </td>
        <td>
            <b>Doc. soporte:</b> {{ $datos_encabezado_doc['campo5'] }}
        </td>
    </tr>
</table>