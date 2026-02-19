<?php 
    $campo = \App\Sistema\Campo::where('name','formato_impresion_pedidos_ventas')->get()->first();

    $arr_select = ['pos'=>'POS','estandar'=>'Estándar','estandar2'=>'Estándar v2'];
    $default_key = 'pos';
    if ( $campo != null ) {
        $arr_select = json_decode($campo->opciones,true);
        $default_key = array_keys($arr_select)[0];
    }
?>
<div>
    Formato: {{ Form::select('formato_impresion_id', $arr_select, $default_key, [ 'id' =>'formato_impresion_id' ]) }}
    {{ Form::bsBtnPrint( 'vtas_pedidos_imprimir/'.$id.$variables_url.'&formato_impresion_id=' . $default_key ) }}
    {{ Form::bsBtnEmail( 'vtas_pedidos_enviar_por_email/'.$id.$variables_url.'&formato_impresion_id=' . $default_key ) }}

    @if( config('ventas.metodo_impresion_pedido_ventas') == 'apm' )
        <div class="col">
            <br><br>
            <button class="btn btn-success btn-sm" id="btn_imprimir_en_cocina"><i class="fa fa-btn fa-print"></i> Imprimir en Cocina </button>
            
            <input type="hidden" id="impresora_cocina_por_defecto" name="impresora_cocina_por_defecto" value="{{ config('ventas_pos.impresora_cocina_por_defecto') }}">

            <input type="hidden" id="tamanio_letra_impresion_items_cocina" name="tamanio_letra_impresion_items_cocina" value="{{ config('ventas_pos.tamanio_letra_impresion_items_cocina') }}">

            <input type="hidden" id="lbl_consecutivo_doc_encabezado" value="{{ $doc_encabezado->consecutivo }}">
            <input type="hidden" id="lbl_fecha" value="{{ $doc_encabezado->fecha }}">
            <input type="hidden" id="lbl_cliente_descripcion" value="{{ $doc_encabezado->tercero_nombre_completo }}">
            <input type="hidden" id="lbl_descripcion_doc_encabezado" value="{{ $doc_encabezado->descripcion }}">
            <input type="hidden" id="lbl_total_factura" value="{{ '$ ' . number_format($doc_encabezado->valor_total,0,',','.') }}">
            <input type="hidden" id="nombre_vendedor" value="{{ $doc_encabezado->vendedor->tercero->descripcion }}">

        </div>
    @endif
</div>