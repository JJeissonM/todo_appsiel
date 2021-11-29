<?php 

namespace App\VentasPos\Services;

use App\Inventarios\Services\ValidacionExistencias;
use App\Inventarios\Services\InvDocumentsService;
use App\Inventarios\Services\InvDocumentsLinesService;

/**
        * !!!!! No se deberia acceder directamente a los modelos de Inventarios
 */
use App\Inventarios\InvProducto;
use App\Inventarios\InvBodega;
use App\Inventarios\InvMotivo;
use App\Inventarios\ItemDesarmeAutomatico;
use App\Inventarios\InvMovimiento;

use App\VentasPos\FacturaPos;
use App\VentasPos\DocRegistro;

/*use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvCostoPromProducto;*/

use View;
use DB;

class InventoriesServices
{
    const INV_DOC_HEADER_MODEL_ID = 25;
    const INV_APPLICATION_ID = 8;
    const INV_DOC_HEADER_MODEL_NAME = 'documentos_inventario';

	public static function tabla_items_existencias_negativas( $bodega_id, $fecha_corte, $lista_items )
	{
		$obj = new ValidacionExistencias( $bodega_id, $fecha_corte );
        $lista_items_existencia_negativa = $obj->lista_items_con_existencias_negativas( $lista_items );
        $items = [];
        foreach ($lista_items_existencia_negativa as $linea)
        {
        	$item = InvProducto::find($linea->item_id);

        	if ( $item->tipo == 'servicio' )
        	{
        		continue;
        	}
        	
        	$obj_aux = (object)[ 
						'item_id' => $linea->item_id,
						'descripcion' => $item->descripcion,
						'existencia' => $linea->existencia,
						'cantidad_facturada' => $linea->cantidad_a_disminuir,
						'nuevo_saldo' => $linea->nuevo_saldo
						];
			$items[] = $obj_aux;
        }

        if ( empty($items) )
        {
        	return 1;
        }

        $bodega = InvBodega::find($bodega_id);
        $lbl_encabezados = ['Cod.', 'Item', 'Existencia', 'Cant. Facturada', 'Nuevo saldo'];

        return View::make( 'inventarios.incluir.cantidad_existencias_tabla', compact( 'bodega', 'fecha_corte', 'lbl_encabezados', 'items' ) )->render();
	}

    // item_consumir_id: el que se compra
    // item_producir_id: el que se vende
    public function get_lineas_registros_desarme( $pdv_id, $bodega_default_id, $parametros_config_inventarios, $fecha )
    {
        $cantidades_facturadas = $this->resumen_cantidades_facturadas($pdv_id);
        $ids_items_facturados = $cantidades_facturadas->pluck('inv_producto_id')->all();

        $parametros_items_producir = ItemDesarmeAutomatico::whereIn('item_producir_id', $ids_items_facturados)->groupBy('item_producir_id')->get();

        /**
         * $lineas_desarme se puede rerfactorizar, no es necesario elaborar un String, sino un Array, que se convierte más fácil a JSON
         */
        $lineas_desarme = '[{"inv_producto_id":"","Producto":"","motivo":"","costo_unitario":"","cantidad":"","costo_total":""}';

        // Por cada item vendido con parametros de desarme
        $hay_productos = 0;
        foreach ($parametros_items_producir as $parametro_item_producir)
        {
            $cantidad_facturada = $cantidades_facturadas->where('inv_producto_id', $parametro_item_producir->item_producir_id)->sum('cantidad_facturada');

            $existencia_item_facturado = InvMovimiento::get_existencia_producto($parametro_item_producir->item_producir_id, $bodega_default_id, $fecha)->Cantidad;

            if ( is_null( $existencia_item_facturado ) )
            {
                $existencia_item_facturado = 0;
            }

            $cantidad_requerida_a_producir = $cantidad_facturada - $existencia_item_facturado;

            if ( $cantidad_requerida_a_producir <= 0 )
            {
                continue;
            }

            $cantidad_proporcional = $parametro_item_producir->cantidad_proporcional;
            if ($cantidad_proporcional == null || $cantidad_proporcional == 0)
            {
                $cantidad_proporcional = 1;
            }

            $cantidad_a_sacar = 1;
            if ( $cantidad_proporcional > 1 )
            {
                $parte_entera_requerida = intdiv( $cantidad_requerida_a_producir, $cantidad_proporcional);
                if ( $cantidad_requerida_a_producir % $cantidad_proporcional != 0 )
                {
                    $cantidad_a_sacar = $parte_entera_requerida + 1;
                }
            }else{
                // $cantidad_a_sacar = 1
                $cantidad_a_sacar = $cantidad_requerida_a_producir;
            }
            
            $cantidad_a_ingresar = $cantidad_a_sacar * $cantidad_proporcional;

            $costo_unitario_item_a_consumir = $parametro_item_producir->item_consumir->get_costo_promedio( $bodega_default_id );

            $motivo_salida = InvMotivo::find( (int)$parametros_config_inventarios['motivo_salida_id'] );
            $motivo_entrada = InvMotivo::find( (int)$parametros_config_inventarios['motivo_entrada_id'] );

            $lineas_desarme .= ',{"inv_producto_id":"' . $parametro_item_producir->item_consumir->id . '","Producto":"' . $parametro_item_producir->item_consumir->id . ' ' . $parametro_item_producir->item_consumir->descripcion . ' (' . $parametro_item_producir->item_consumir->unidad_medida1 . ')","motivo":"' . $motivo_salida->id . '-' . $motivo_salida->descripcion . '","costo_unitario":"$' . $costo_unitario_item_a_consumir . '","cantidad":"' . $cantidad_a_sacar . ' UND","costo_total":"$' . ($cantidad_a_sacar * $costo_unitario_item_a_consumir) . '"}';

            $costo_unitario_item_producir = $costo_unitario_item_a_consumir / $cantidad_proporcional;

            $lineas_desarme .= ',';

            $lineas_desarme .= '{"inv_producto_id":"' . $parametro_item_producir->item_producir->id . '","Producto":"' . $parametro_item_producir->item_producir->id . ' ' . $parametro_item_producir->item_producir->descripcion . ' (' . $parametro_item_producir->item_producir->unidad_medida1 . '))","motivo":"' . $motivo_entrada->id . '-' . $motivo_entrada->descripcion . '","costo_unitario":"$' . $costo_unitario_item_producir . '","cantidad":"' . $cantidad_a_ingresar . ' UND","costo_total":"$' . ($cantidad_a_ingresar * $costo_unitario_item_producir) . '"}';

            $hay_productos++;
        }

        if ( $hay_productos == 0 )
        {
            return 99; // Type integer
        }

        $lineas_desarme .= ',{"inv_producto_id":"","Producto":"00.00","motivo":"$00.00","costo_unitario":""},{"inv_producto_id":"Agregar productos","Producto":"Agregar productos","motivo":"Agregar productos","costo_unitario":"Agregar productos","cantidad":"Agregar productos","costo_total":"Calcular costos"}]';

        return $lineas_desarme; // Type string
    }

    /**
     * resumen_cantidades_facturadas
     * Esto no es de inventarios, pasar para AcummulationService
     * Analizar!!!!
     */
    public function resumen_cantidades_facturadas($pdv_id, $inv_producto_id = null)
    {
        $ids_encabezados_documentos = FacturaPos::where('pdv_id', $pdv_id)
                                                ->where('estado', 'Pendiente')
                                                ->select('id')
                                                ->get()
                                                ->pluck('id')
                                                ->all();

        $cantidades_facturadas = DocRegistro::whereIn('vtas_pos_doc_encabezado_id', $ids_encabezados_documentos)
                                            ->select(DB::raw('sum(cantidad) AS cantidad_facturada'), 'inv_producto_id')
                                            ->groupBy('inv_producto_id')
                                            ->get();
        if ($inv_producto_id !== null) {
            return $cantidades_facturadas->where('inv_producto_id', $inv_producto_id)->all();
        }

        return $cantidades_facturadas;
    }

    public function create_document_making( $pdv_id, $bodega_default_id, $fecha, $parametros_config_inventarios )
    {
        $movimiento = $this->get_lineas_registros_desarme($pdv_id, $bodega_default_id, $parametros_config_inventarios, $fecha);

        if ( gettype($movimiento) == "integer" )
        {
            return 0;
        }

        $request = $this->built_ObjRequets_desarme($parametros_config_inventarios, $bodega_default_id, $movimiento, $fecha);

        $obj_inv_docum_line_serv = new InvDocumentsLinesService();
        $lineas_registros = $obj_inv_docum_line_serv->preparar_array_lineas_registros( $bodega_default_id, $request->movimiento, null);

        $obj_inv_docum_head_serv = new InvDocumentsService();
        $obj_inv_docum_head_serv->store_document($request, $lineas_registros, self::INV_DOC_HEADER_MODEL_NAME);

        return 1;
    }

    public function built_ObjRequets_desarme( $parametros_config_inventarios, $bodega_default_id, $movimiento, $fecha)
    {
        $request = new Request;
        $user = Auth::user();
        $request["core_empresa_id"] = $user->empresa_id;
        $request["core_tipo_transaccion_id"] = (int)$parametros_config_inventarios['core_tipo_transaccion_id'];
        $request["core_tipo_doc_app_id"] = (int)$parametros_config_inventarios['core_tipo_doc_app_id'];
        $request["fecha"] = $fecha;
        $request["core_tercero_id"] = (int)$parametros_config_inventarios['core_tercero_id'];
        $request["descripcion"] = "";
        $request["documento_soporte"] = "";
        $request["inv_bodega_id"] = $bodega_default_id;
        $request["movimiento"] = $movimiento;
        $request["consecutivo"] = "";
        $request["hay_productos"] = "1";
        $request["creado_por"] = $user->email;
        $request["modificado_por"] = "0";
        $request["estado"] = "Activo";
        $request["url_id"] = self::INV_APPLICATION_ID;
        $request["url_id_modelo"] = self::INV_DOC_HEADER_MODEL_ID;
        $request["url_id_transaccion"] = (int)$parametros_config_inventarios['core_tipo_transaccion_id'];

        return $request;
    }

    // Crear Remisión (Sin contabilizarla)
    public function create_delivery_note_from_invoice( $invoice, $bodega_default_id )
    {
        $datos_remision = $invoice->toArray();
        $datos_remision['invoice_doc_lines'] = $invoice->lineas_registros;
        
        $obj_inv_docum_serv = new InvDocumentsService();
        return $obj_inv_docum_serv->create_doc_delivery_note( self::INV_DOC_HEADER_MODEL_NAME, $datos_remision, $bodega_default_id, 'Facturada' );
    }

        
}