<?php 

namespace App\Ventas\Services;

use App\Ventas\Cliente;
use App\Ventas\VtasPedido;

class PedidosRestauranteServices
{

    public function mesas_permitidas_para_cambiar()
    {
        $mesas = $this->get_clientes_tipo_mesas();

        $mesas_pedidos_pendientes = VtasPedido::where([
                        ['estado','=','Pendiente'],
                        ['core_tipo_transaccion_id','=',60],
                    ])
                    ->whereIn('cliente_id',$mesas->pluck('id')->toArray())
                    ->get()->pluck('cliente_id')->toArray();
                    
        $disponibles = [];
        foreach ($mesas as $mesa) {
            if (in_array($mesa->id,$mesas_pedidos_pendientes)) {
                continue;
            }
            
            $disponibles[] = $this->build_obj_mesa($mesa);               
        }

        return $disponibles;
    }

    public function cambiar_pedidos_de_mesa($mesa_pedidos_id, $nueva_mesa_id)
    {
        $pedidos_pendientes_una_mesa = VtasPedido::where([
                        ['estado','=','Pendiente'],
                        ['cliente_id','=',$mesa_pedidos_id],
                        ['core_tipo_transaccion_id','=',60],
                    ])
                    ->get();
                    
        foreach ($pedidos_pendientes_una_mesa as $pedido) {            
            $pedido->cliente_id = $nueva_mesa_id;
        }

        return true;
    }

    // vendedor = mesero
    public function get_pedidos_pendientes_mesero($mesero_id)
    {
        $pedidos = VtasPedido::where([
            ['estado','=','Pendiente'],
            ['vendedor_id','=',$mesero_id],
            ['core_tipo_transaccion_id','=',60],
                                    ])
                    ->get();

                    $pendientes = [];
        foreach ($pedidos as $pedido) {
            
            $pendientes[] = $this->build_json_pedido_for_create($pedido);               
        }

        return $pendientes;
    }

    public function cargar_datos_editar_pedido($pedido_id)
    {
        $pedido = VtasPedido::find($pedido_id);

        $lineas_registro = $this->armar_cuerpo_tabla_lineas_registros($pedido->lineas_registros);

        return [
            'doc_encabezado_documento_transaccion_descripcion' => $pedido->tipo_documento_app->descripcion,
            'doc_encabezado_documento_transaccion_prefijo_consecutivo' => $pedido->tipo_documento_app->prefijo . ' ' . $pedido->consecutivo,
            'doc_encabezado_fecha' => $pedido->fecha,
            'doc_encabezado_tercero_nombre_completo' => $pedido->cliente->tercero->descripcion,
            'doc_encabezado_vendedor_descripcion' => $pedido->vendedor->tercero->descripcion,
            'cantidad_total_productos' => count($pedido->lineas_registros),
            'doc_encabezado_descripcion' => $pedido->descripcion,
            'pedido_id' => $pedido_id,
            'pedido_label' => $pedido->get_label_documento(),
            'mesero_label' => $pedido->vendedor->tercero->descripcion,
            'mesa_label' => $pedido->cliente->tercero->descripcion,
            'lineas_registro' => $lineas_registro,
            'numero_lineas' => $pedido->lineas_registros->count(),
            'valor_total' => $pedido->valor_total
        ];
    }

    public function armar_cuerpo_tabla_lineas_registros($lineas_registros_documento)
    {
        $cuerpo_tabla_lineas_registros = [];
        $i = 1;
        foreach ($lineas_registros_documento as $linea) {

            $cuerpo_tabla_lineas_registros[] = $this->build_obj_linea_registro_pedido($i,$linea);
            $i++;
        }
        return $cuerpo_tabla_lineas_registros;
    }

    public function get_mesas_disponibles_mesero($mesero_id)
    {
        $mesas = $this->get_clientes_tipo_mesas();

        $mesas_pedidos_pendientes_otros_meseros = VtasPedido::where([
                        ['estado','=','Pendiente'],
                        ['vendedor_id','<>',$mesero_id],
                        ['core_tipo_transaccion_id','=',60],
                    ])
                    ->whereIn('cliente_id',$mesas->pluck('id')->toArray())
                    ->get()->pluck('cliente_id')->toArray();
                    
        $disponibles = [];
        foreach ($mesas as $mesa) {
            if (in_array($mesa->id,$mesas_pedidos_pendientes_otros_meseros)) {
                // La mesa del cliente "PARA LLEVAR" siempre va a estar disponible
                if ( $mesa->id !== (int)config('pedidos_restaurante.cliente_default_id') ) {
                    continue;
                }
            }
            
            $disponibles[] = $this->build_obj_mesa($mesa);               
        }

        return $disponibles;
    }

    public function get_pedidos_mesero_para_una_mesa($mesero_id, $mesa_id)
    {
        $pedidos_pendientes_mesero = VtasPedido::where([
                        ['estado','=','Pendiente'],
                        ['vendedor_id','=',$mesero_id],
                        ['cliente_id','=',$mesa_id],
                        ['core_tipo_transaccion_id','=',60],
                    ])
                    ->get();
                    
        $pendientes = [];
        foreach ($pedidos_pendientes_mesero as $pedido) {            
            $pendientes[] = $this->build_json_pedido_for_create($pedido);               
        }

        return $pendientes;
    }

    public function get_clientes_tipo_mesas()
    {
        return Cliente::where([
                ['estado','=','Activo'],
                ['clase_cliente_id','=',(int)config('pedidos_restaurante.clase_cliente_tipo_mesas_id')]
            ])
            ->orWhere('id',(int)config('pedidos_restaurante.cliente_default_id'))
            ->get();
    }

    public function build_json_pedido_for_create($pedido)
    {
        return [
            'pedido_id' => $pedido->id,
            'pedido_label' => $pedido->get_label_documento() . ', ' . $pedido->cliente->tercero->descripcion,
            'cliente' => $pedido->cliente
        ];
    }

    public function build_obj_mesa($mesa)
    {
        return [
            'mesa_id' =>  $mesa->id,
            'mesa_descripcion' =>  $mesa->tercero->descripcion,
            'cliente_id' => $mesa->id,
            'zona_id' => $mesa->zona_id,
            'clase_cliente_id' => $mesa->clase_cliente_id,
            'liquida_impuestos' => $mesa->liquida_impuestos,
            'core_tercero_id' => $mesa->core_tercero_id,
            'lista_precios_id' => $mesa->lista_precios_id,
            'lista_descuentos_id' => $mesa->lista_descuentos_id,
            'vendedor_id' => $mesa->vendedor_id,
            'inv_bodega_id' => $mesa->inv_bodega_id,
            'descripcion_cliente' => $mesa->tercero->descripcion,
            'nombre_cliente' => $mesa->tercero->nombre,
            'numero_identificacion' => $mesa->tercero->numero_identificacion,
            'direccion1' => $mesa->tercero->direccion1,
            'telefono1' => $mesa->tercero->telefono1,
            'dias_plazo' => $mesa->dias_plazo,
            'dias_plazo' => $mesa->dias_plazo
        ];
    }

    public function build_obj_linea_registro_pedido($numero_linea,$linea)
    {
        return [
            'numero_linea' => $numero_linea,
            'linea_registro_id' => $linea->id,
            'inv_producto_id' => $linea->inv_producto_id,
            'precio_unitario' => $linea->precio_unitario,
            'base_impuesto' => $linea->base_impuesto,
            'tasa_impuesto' => $linea->tasa_impuesto,
            'valor_impuesto' => $linea->valor_impuesto,
            'base_impuesto_total' => $linea->base_impuesto_total,
            'cantidad' => $linea->cantidad,
            'precio_total' => $linea->precio_total,
            'tasa_descuento' => $linea->tasa_descuento,
            'valor_total_descuento' => $linea->valor_total_descuento,                
            'lbl_producto_descripcion' => $linea->item->descripcion,
            'cantidad' => $linea->cantidad,
            'lbl_producto_unidad_medida' => $linea->item->unidad_medida1,
            'lbl_tasa_impuesto' => $linea->tasa_impuesto,
            'lbl_precio_total' => $linea->precio_total,
        ];
    }

}
