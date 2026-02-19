<?php

namespace App\Inventarios\Services;

use App\Core\Transactions\TransactionDocument;
use App\Inventarios\InvBodega;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvProducto;
use App\Inventarios\InvMovimiento;

class MovementService
{
    public $descripcion_bodega = '';
    public $cantidad_registros = 0;

    public function get_movement_with_item_relations($array_wheres)
    {
        return InvMovimiento::where($array_wheres)
                        ->select('inv_movimientos.*')
                        ->orderBy('fecha')
                        ->get();
    }

    public function movements_by_purpose($init_date,$end_date,$transaction_type_id,$purpose_id)
    {
        $array_wheres = [
            ['inv_movimientos.fecha','>=',$init_date],
            ['inv_movimientos.fecha','<=',$end_date]
        ];

        if ( $purpose_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_movimientos.inv_motivo_id' => $purpose_id] );
        }

        $arr_purposes_id = InvMotivo::where([
            ['core_tipo_transaccion_id','=',$transaction_type_id]
        ])->get()->pluck('id')->toArray();

        return InvMovimiento::where($array_wheres)
                        ->whereIn('inv_movimientos.inv_motivo_id',$arr_purposes_id)
                        ->select('inv_movimientos.*')
                        ->orderBy('fecha')
                        ->get();
    }

    public function build_array_of_stocks_old( $filters )
    {
        $movin_filtrado = (new FiltroMovimientos())->aplicar_filtros( null, $filters->fecha_corte, $filters->mov_bodega_id, $filters->grupo_inventario_id, $filters->item_id, (int)$filters->tipo_prenda_id );
        
        $lista_items = array_keys($movin_filtrado->groupBy('inv_producto_id')->toArray() );
        $lista_bodegas = array_keys($movin_filtrado->groupBy('inv_bodega_id')->toArray() );

        $bodegas = InvBodega::all();
        $items = InvProducto::all();
        
        $stock_serv = new StockAmountService();

        $productos = [];
        $i = 0;
        foreach ( $lista_items as $key => $item_id )
        {
            $item = $items->where( 'id',$item_id )->first();

            $total_cantidad_item = 0;
            $total_costo_item = 0;
            $aux = [];
            $cantidad_bodegas = 0;
            foreach ( $lista_bodegas as $key2 => $bodega_id )
            {
                $productos[$i]['id'] = $item_id;
                $productos[$i]['descripcion'] = $item->get_value_to_show(true);

                $productos[$i]['unidad_medida1'] = $item->get_unidad_medida1();
                $productos[$i]['unidad_medida2'] = $item->unidad_medida2;
                $productos[$i]['referencia'] = $item->referencia;

                $bodega = $bodegas->where( 'id',$bodega_id )->first();
                
                $descripcion_bodega = '';
                if ($bodega != null) {
                    $descripcion_bodega = $bodega->descripcion;
                }
                $productos[$i]['bodega'] = $this->descripcion_bodega;

                //$productos[$i]['Cantidad'] = $movin_filtrado->where('inv_bodega_id', $bodega_id)->where('inv_producto_id', $item_id)->sum('cantidad');
                $productos[$i]['Cantidad'] = $stock_serv->get_stock_amount_item($bodega_id, $item_id, $filters->fecha_corte);
                
                $productos[$i]['CostoPromedio'] = 0;

                if ($productos[$i]['Cantidad'] == 0) {
                    continue;
                }

                // Costo total del movimiento de inventarios del item
                $productos[$i]['Costo'] = $stock_serv->get_total_cost_amount_item($bodega_id, $item_id, $filters->fecha_corte);

                //$productos[$i]['Costo'] = $movin_filtrado->where('inv_bodega_id', $bodega_id)->where('inv_producto_id', $item_id)->sum('costo_total');

                $total_cantidad_item += $productos[$i]['Cantidad'];
                $total_costo_item += $productos[$i]['Costo'];

                if ( $item->item_mandatario() != null) {

                    $productos[$i]['Material'] = $item->item_mandatario()->tipo_material->descripcion;
                    $productos[$i]['TipoPrenda'] = $item->item_mandatario()->tipo_prenda->descripcion;
                }                
            
                $i++;
                $cantidad_bodegas++;
            }

            $costo_promedio = 0;
            if ($total_cantidad_item != 0) {

                $costo_promedio = $total_costo_item / $total_cantidad_item;

                if ( $total_costo_item < 0 || $filters->fecha_corte == date('Y-m-d') ) {
                    $costo_promedio = $item->get_costo_promedio();
                    $total_costo_item = $total_cantidad_item * $costo_promedio;
                }

                for ($i2=$cantidad_bodegas; $i2 > 0; $i2--) {
                    if (isset($productos[$i - $i2]['CostoPromedio'])) {
                        $productos[$i - $i2]['CostoPromedio'] = $costo_promedio;
                    }
                }
            }

            $productos[$i]['id'] = 0;
            $productos[$i]['descripcion'] = '';
            $productos[$i]['unidad_medida1'] = '';
            $productos[$i]['unidad_medida2'] = '';
            $productos[$i]['bodega'] = '';

            $productos[$i]['Cantidad'] = $total_cantidad_item;
            $productos[$i]['CostoPromedio'] = $costo_promedio;

            $productos[$i]['Costo'] = $total_costo_item;

            $productos[$i]['Material'] = '';
            $productos[$i]['TipoPrenda'] = '';

            $i++;
        }        

        switch( count($lista_bodegas) )
        {
            case '0':
                $this->descripcion_bodega = "NINGUNA";
                break;
            case '1':
                $this->descripcion_bodega = $descripcion_bodega;
                break;
            default:
                $this->descripcion_bodega = "VARIAS";
                break;
        }

        return $productos;
    }

    public function build_array_of_stocks_new( $filters )
    {
        $productos = [];
        $mostrar_items_sin_movimiento = isset($filters->mostrar_items_sin_movimiento) && (int)$filters->mostrar_items_sin_movimiento === 1;

        $obj = new ItemsFiltersServices();

        $lista_items = $obj->get_listado_de_items( $filters, $mostrar_items_sin_movimiento );

        if ( empty( $lista_items->toArray() ) ) {
            $this->descripcion_bodega = "NINGUNA";
            return $productos;
        }

        $items_ids = $lista_items->pluck('id')->toArray();

        $movimientos = InvMovimiento::where('fecha', '<=', $filters->fecha_corte)
                                    ->whereIn('inv_producto_id', $items_ids);

        if ( $filters->mov_bodega_id != '' )
        {
            $movimientos->where('inv_bodega_id', (int)$filters->mov_bodega_id);
        }

        $saldos = $movimientos->selectRaw('inv_bodega_id, inv_producto_id, SUM(cantidad) as cantidad, SUM(costo_total) as costo_total')
                                ->groupBy('inv_bodega_id', 'inv_producto_id')
                                ->get();

        $saldos_indexados = [];
        foreach ($saldos as $linea)
        {
            $saldos_indexados[$linea->inv_bodega_id][$linea->inv_producto_id] = [
                'cantidad' => (float)$linea->cantidad,
                'costo_total' => (float)$linea->costo_total
            ];
        }

        $lista_bodegas = array_keys($saldos_indexados);
        if ( $mostrar_items_sin_movimiento )
        {
            if ( $filters->mov_bodega_id != '' )
            {
                $lista_bodegas = [ (int)$filters->mov_bodega_id ];
            }else{
                $lista_bodegas = InvBodega::orderBy('id')->pluck('id')->toArray();
            }
        }

        $bodegas = InvBodega::all();

        $i = 0;
        foreach ( $lista_items as $item )
        {
            $total_cantidad_item = 0;
            $total_costo_item = 0;
            $cantidad_bodegas = 0;
            
            $descripcion_bodega = '';
            foreach ( $lista_bodegas as $key2 => $bodega_id )
            {
                $saldo_item = $saldos_indexados[$bodega_id][$item->id] ?? [ 'cantidad' => 0, 'costo_total' => 0 ];
                $cantidad = (float)$saldo_item['cantidad'];

                if ( !$mostrar_items_sin_movimiento && $cantidad == 0 )
                {
                    continue;
                }

                $productos[$i]['id'] = $item->id;
                $productos[$i]['descripcion'] = $item->get_value_to_show_interno(true);

                $productos[$i]['unidad_medida1'] = $item->get_unidad_medida1();
                $productos[$i]['unidad_medida2'] = $item->unidad_medida2;
                $productos[$i]['referencia'] = $item->referencia;

                $bodega = $bodegas->where( 'id',$bodega_id )->first();
                
                if ($bodega != null) {
                    $descripcion_bodega = $bodega->descripcion;
                }
                $productos[$i]['bodega'] = $descripcion_bodega;

                $productos[$i]['Cantidad'] = $cantidad;
                
                $productos[$i]['CostoPromedio'] = 0;

                if ($productos[$i]['Cantidad'] == 0) {
                    if ( !$mostrar_items_sin_movimiento ) {
                        continue;
                    }
                }

                // Costo total del movimiento de inventarios del item
                $productos[$i]['Costo'] = (float)$saldo_item['costo_total'];

                $total_cantidad_item += $productos[$i]['Cantidad'];
                $total_costo_item += $productos[$i]['Costo'];

                if ( $item->item_mandatario() != null) {

                    $productos[$i]['Material'] = $item->item_mandatario()->tipo_material->descripcion;
                    $productos[$i]['TipoPrenda'] = $item->item_mandatario()->tipo_prenda->descripcion;
                }
                
                $productos[$i]['url_imagen'] = $item->get_url_imagen();             
            
                $i++;
                $cantidad_bodegas++;
                
                $this->cantidad_registros++;
            }
            
            if ( !$mostrar_items_sin_movimiento && $total_cantidad_item == 0 )
            {
                continue;
            }

            $costo_promedio = 0;
            if ($total_cantidad_item != 0) {

                $costo_promedio = $total_costo_item / $total_cantidad_item;

                if ( $total_costo_item < 0 || $filters->fecha_corte == date('Y-m-d') ) {
                    $costo_promedio = $item->get_costo_promedio();
                    $total_costo_item = $total_cantidad_item * $costo_promedio;
                }

                for ($i2=$cantidad_bodegas; $i2 > 0; $i2--) {
                    if (isset($productos[$i - $i2]['CostoPromedio'])) {
                        $productos[$i - $i2]['CostoPromedio'] = $costo_promedio;
                    }
                }
            }

            $productos[$i]['id'] = 0;
            $productos[$i]['descripcion'] = '';
            $productos[$i]['unidad_medida1'] = '';
            $productos[$i]['unidad_medida2'] = '';
            $productos[$i]['bodega'] = '';

            $productos[$i]['Cantidad'] = $total_cantidad_item;
            $productos[$i]['CostoPromedio'] = $costo_promedio;

            $productos[$i]['Costo'] = $total_costo_item;

            $productos[$i]['Material'] = '';
            $productos[$i]['TipoPrenda'] = '';

            $i++;
        }

        switch( count($lista_bodegas) )
        {
            case '0':
                $this->descripcion_bodega = "NINGUNA";
                break;
            case '1':
                $this->descripcion_bodega = $descripcion_bodega;
                break;
            default:
                $this->descripcion_bodega = "VARIAS";
                break;
        }

        return $productos;
    }
}
