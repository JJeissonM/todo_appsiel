{{ Form::hidden('url_id', Input::get('id'), ['id' => 'url_id']) }}
{{ Form::hidden('url_id_modelo', Input::get('id_modelo'), ['id' => 'url_id_modelo' ]) }}

<input type="hidden" name="url_id_transaccion" id="url_id_transaccion" value="{{Input::get('id_transaccion')}}"
        required="required">

{{ Form::hidden( 'pdv_id', Input::get('pdv_id'), ['id'=>'pdv_id'] ) }}
{{ Form::hidden('cajero_id', Auth::user()->id, ['id'=>'cajero_id'] ) }}

{{ Form::hidden('inv_bodega_id_aux',$pdv->bodega_default_id,['id'=>'inv_bodega_id_aux']) }}

<input type="hidden" name="pdv_label" id="pdv_label" value="{{$pdv->descripcion}}" required="required">

<input type="hidden" name="cliente_id" id="cliente_id" value="{{$cliente->id}}" required="required">

<input type="hidden" name="aux_cliente_input" id="aux_cliente_input">

<input type="hidden" name="zona_id" id="zona_id" value="{{$cliente->zona_id}}" required="required">
<input type="hidden" name="clase_cliente_id" id="clase_cliente_id" value="{{$cliente->clase_cliente_id}}"
        required="required">

<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{$cliente->core_tercero_id}}"
        required="required">

<input type="hidden" name="caja_pdv_default_id" id="caja_pdv_default_id" value="{{$pdv->caja_default_id}}">

@if ($pdv->caja != null)
<input type="hidden" name="caja_pdv_default_label" id="caja_pdv_default_label" value="{{$pdv->caja->descripcion}}">
@else
<input type="hidden" name="caja_pdv_default_label" id="caja_pdv_default_label" value="">
@endif


<input type="hidden" name="vendedor_id" id="vendedor_id" data-vendedor_descripcion="{{$vendedor->tercero->descripcion}}"
        value="{{$vendedor->id}}">

<input type="hidden" name="equipo_ventas_id" id="equipo_ventas_id" value="{{$vendedor->equipo_ventas_id}}"
        required="required">

<input type="hidden" name="cliente_descripcion" id="cliente_descripcion" value="{{$cliente->tercero->descripcion}}"
        required="required">

<input type="hidden" name="lista_precios_id" id="lista_precios_id" value="{{$cliente->lista_precios_id}}"
        required="required">
<input type="hidden" name="lista_descuentos_id" id="lista_descuentos_id" value="{{$cliente->lista_descuentos_id}}"
        required="required">
<input type="hidden" name="liquida_impuestos" id="liquida_impuestos" value="{{$cliente->liquida_impuestos}}"
        required="required">

<input type="hidden" name="inv_motivo_id" id="inv_motivo_id" value="{{$inv_motivo_id}}">
<input type="hidden" name="teso_motivo_default_id" id="teso_motivo_default_id" value="{{(int)config('tesoreria.motivo_tesoreria_ventas_contado')}}">

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
<input type="hidden" name="valor_ajuste_al_peso" id="valor_ajuste_al_peso">

<input type="hidden" name="pedido_id" id="pedido_id" value="{{$pedido_id}}">

<input type="hidden" name="action" id="action" value="{{ Input::get('action') }}">

<input type="hidden" name="permitir_venta_menor_costo" id="permitir_venta_menor_costo"
        value="{{ config('ventas.permitir_venta_menor_costo') }}">

<input type="hidden" name="forma_lectura_codigo_barras" id="forma_lectura_codigo_barras"
        value="{{ config('codigos_barras.forma_lectura_codigo_barras') }}">

<input type="hidden" name="msj_resolucion_facturacion" id="msj_resolucion_facturacion"
        value="{{ $msj_resolucion_facturacion }}">

<input type="hidden" name="creado_por" id="creado_por" value="{{ \Illuminate\Support\Facades\Auth::user()->email }}">

<input type="hidden" name="manejar_propinas" id="manejar_propinas" value="{{ config('ventas_pos.manejar_propinas') }}">

<input type="hidden" name="manejar_datafono" id="manejar_datafono" value="{{ config('ventas_pos.manejar_datafono') }}">

@can('bloqueo_cambiar_precio_unitario')
<input type="hidden" name="bloqueo_cambiar_precio_unitario" id="bloqueo_cambiar_precio_unitario" value="1">
@else
<input type="hidden" name="bloqueo_cambiar_precio_unitario" id="bloqueo_cambiar_precio_unitario" value="0">
@endcan

@can('bloqueo_cambiar_tasa_descuento')
<input type="hidden" name="bloqueo_cambiar_tasa_descuento" id="bloqueo_cambiar_tasa_descuento" value="1">
@else
<input type="hidden" name="bloqueo_cambiar_tasa_descuento" id="bloqueo_cambiar_tasa_descuento" value="0">
@endcan

<input type="hidden" name="plantilla_factura_pos_default" id="plantilla_factura_pos_default"
        value="{{ $pdv->plantilla_factura_pos_default }}">

<input type="hidden" name="ocultar_boton_guardar_factura_pos" id="ocultar_boton_guardar_factura_pos"
        value="{{ (int)config('ventas_pos.ocultar_boton_guardar_factura_pos') }}">

<input type="hidden" name="categoria_id_paquetes_con_materiales_ocultos"
        id="categoria_id_paquetes_con_materiales_ocultos"
        value="{{ (int)config('inventarios.categoria_id_paquetes_con_materiales_ocultos') }}">

<input type="hidden" name="manejar_platillos_con_contorno" id="manejar_platillos_con_contorno"
        value="{{ (int)config('inventarios.manejar_platillos_con_contorno') }}">

@include('ventas_pos.campos.categoria_id_platillos_con_contornos')

<input type="hidden" name="permitir_precio_unitario_negativo" id="permitir_precio_unitario_negativo"
        value="{{ (int)config('ventas_pos.permitir_precio_unitario_negativo') }}">
        
<input type="hidden" name="tiempo_espera_guardar_factura" id="tiempo_espera_guardar_factura"
value="{{ (int)config('ventas_pos.tiempo_espera_guardar_factura') }}">

<input type="hidden" name="formato_impresion_pedidos" id="formato_impresion_pedidos"
value="{{ config('ventas_pos.formato_impresion_pedidos') }}">

<input type="hidden" name="uniqid" id="uniqid" value="{{ uniqid() }}">

<input type="hidden" name="object_anticipos" id="object_anticipos" value="null">

<!-- Campos ocultos para el manejo de bolsas -->
<input type="hidden" name="precio_bolsa" id="precio_bolsa" value="{{ $precio_bolsa }}">
<input type="hidden" name="valor_total_bolsas" id="valor_total_bolsas" value="{{$valor_total_bolsas}}">
@include('ventas_pos.campos.categoria_id_facturacion_bolsa')

<input type="hidden" name="acumular_facturas_en_tiempo_real" id="acumular_facturas_en_tiempo_real"
value="{{ config('ventas_pos.acumular_facturas_en_tiempo_real') }}">
<input type="hidden" name="permitir_inventarios_negativos" id="permitir_inventarios_negativos"
value="{{ config('ventas.permitir_inventarios_negativos') }}">

<input type="hidden" name="mostrar_saldo_pendiente_cxc_al_imprimir" id="mostrar_saldo_pendiente_cxc_al_imprimir"
value="{{ config('ventas_pos.mostrar_saldo_pendiente_cxc_al_imprimir') }}">

<!-- Boton para hacer pruebas -->
<button onclick="testing_print_jspm();" style="display: none;" id="btn_pruebas">testing_print_jspm</button>