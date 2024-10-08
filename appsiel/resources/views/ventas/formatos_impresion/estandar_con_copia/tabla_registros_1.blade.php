@include('ventas.incluir.lineas_registros_imprimir',compact('total_cantidad','total_factura'))

@if( !is_null( $otroscampos ) )
    {!! $otroscampos->terminos_y_condiciones !!}
@endif

<table class="table table-bordered">
    <tr>
        <td width="50%"> 
            @include('ventas.incluir.factura_detalles_impuestos',compact('array_tasas'))
        </td>
        <td width="30%">
            @include('ventas.formatos_impresion.estandar_con_copia.resumen_totales', compact('subtotal', 'total_descuentos', 'impuesto_iva', 'total_impuestos', 'total_factura'))
        </td>
        <td>
            <div style="position: relative;">
                <div style="text-align: center; width:100%; padding: 0px 5px;">
                    <br><br><br>
                    __________________
                    <br>
                    <b> Firma <br> del aceptante </b> 
                </div>
            </div>
        </td>
    </tr>
</table>


