{{ Form::hidden('url_id',Input::get('id')) }}
{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}

<input type="hidden" name="url_id_transaccion" id="url_id_transaccion"
    value="{{Input::get('id_transaccion')}}" required="required">

{{ Form::hidden( 'pdv_id', $pdv->id, ['id'=>'pdv_id'] ) }}
{{ Form::hidden('cajero_id', Auth::user()->id, ['id'=>'cajero_id'] ) }}

{{ Form::hidden('inv_bodega_id_aux',$pdv->bodega_default_id,['id'=>'inv_bodega_id_aux']) }}

<input type="hidden" name="cliente_id" id="cliente_id" value="{{$cliente->id}}"
    required="required">
<input type="hidden" name="zona_id" id="zona_id" value="{{$cliente->zona_id}}" required="required">
<input type="hidden" name="clase_cliente_id" id="clase_cliente_id"
    value="{{$cliente->clase_cliente_id}}" required="required">

<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{$cliente->core_tercero_id}}"
    required="required">

<input type="hidden" name="caja_pdv_default_id" id="caja_pdv_default_id" value="{{$pdv->caja_default_id}}">

<input type="hidden" name="fecha_entrega" id="fecha_entrega" value="{{ date('Y-m-d') }}">

<?php 
    $user_vendedor_id = 0;
    if ($vendedor != null ) {
        if ($vendedor->usuario != null ) {
            $user_vendedor_id = $vendedor->usuario->id;
        }
    }
?>

<input type="hidden" name="vendedor_id" id="vendedor_id" data-vendedor_descripcion="{{$vendedor->tercero->descripcion}}" data-user_id="{{$user_vendedor_id}}" value="{{$vendedor->id}}">

<input type="hidden" name="vendedor_default_id" id="vendedor_default_id" data-vendedor_descripcion="{{$vendedor->tercero->descripcion}}" data-user_id="{{$user_vendedor_id}}" value="{{$vendedor->id}}">

<input type="hidden" name="equipo_ventas_id" id="equipo_ventas_id" value="{{$vendedor->equipo_ventas_id}}" required="required">

<input type="hidden" name="cliente_descripcion" id="cliente_descripcion"
    value="{{$cliente->tercero->descripcion}}" required="required">

<input type="hidden" name="lista_precios_id" id="lista_precios_id"
    value="{{$cliente->lista_precios_id}}" required="required">
<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id"
    value="{{$cliente->lista_descuentos_id}}" required="required">
<input type="hidden" name="liquida_impuestos" id="liquida_impuestos"
    value="{{$cliente->liquida_impuestos}}" required="required">

<input type="hidden" name="inv_motivo_id" id="inv_motivo_id" value="{{$inv_motivo_id}}">

<input type="hidden" name="lineas_registros" id="lineas_registros" value="0">
<input type="hidden" name="lineas_registros_medios_recaudos" id="lineas_registros_medios_recaudos" value="0">

<input type="hidden" name="estado" id="estado" value="Pendiente">

<input type="hidden" name="tipo_transaccion" id="tipo_transaccion" value="factura_directa">

<input type="hidden" name="rm_tipo_transaccion_id" id="rm_tipo_transaccion_id"
    value="{{config('ventas')['rm_tipo_transaccion_id']}}">
<input type="hidden" name="dvc_tipo_transaccion_id" id="dvc_tipo_transaccion_id"
    value="{{config('ventas')['dvc_tipo_transaccion_id']}}">

<input type="hidden" name="caja_id" id="saldo_original" value="0">

<input type="hidden" name="valor_total_cambio" id="valor_total_cambio" value="0">
<input type="hidden" name="total_efectivo_recibido" id="total_efectivo_recibido">

<input type="hidden" name="pedido_id" id="pedido_id" value="{{$pedido_id}}">

<input type="hidden" name="action" id="action" value="{{ Input::get('action') }}">

<input type="hidden" name="permitir_venta_menor_costo" id="permitir_venta_menor_costo" value="{{ config('ventas.permitir_venta_menor_costo') }}">

<input type="hidden" name="categoria_id_paquetes_con_materiales_ocultos" id="categoria_id_paquetes_con_materiales_ocultos" value="{{ (int)config('inventarios.categoria_id_paquetes_con_materiales_ocultos') }}">

<input type="hidden" name="manejar_platillos_con_contorno" id="manejar_platillos_con_contorno" value="{{ (int)config('inventarios.manejar_platillos_con_contorno') }}">
<input type="hidden" name="categoria_id_platillos_con_contornos" id="categoria_id_platillos_con_contornos" value="{{ (int)config('inventarios.categoria_id_platillos_con_contornos') }}">