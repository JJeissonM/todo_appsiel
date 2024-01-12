<?php 

namespace App\Inventarios\Services;

use Illuminate\Http\Request;

use App\Inventarios\Services\InvDocumentsService;
use App\Inventarios\Services\InvDocumentsLinesService;

/**
        * !!!!! No se deberia acceder directamente a los modelos de Inventarios
 */
use App\Inventarios\InvMotivo;

use App\Inventarios\RecetaCocina;

use Illuminate\Support\Facades\Auth;

class RecipeServices
{
    const INV_DOC_HEADER_MODEL_ID = 25; // Documentos de inventario
    const INV_APPLICATION_ID = 8; // Inventarios
    const INV_DOC_HEADER_MODEL_NAME = 'documentos_inventario';

    // item_ingrediente_id: el que se compra
    // item_platillo_id: el que se vende
    public function get_lineas_registros_desarme( $lineas_registros, $bodega_default_id, $parametros_config_inventarios, $fecha )
    {
        $motivo_salida = InvMotivo::find( (int)$parametros_config_inventarios['motivo_salida_id'] );
        $motivo_entrada = InvMotivo::find( (int)$parametros_config_inventarios['motivo_entrada_id'] );

        $cantidades_facturadas = $this->resumen_cantidades_facturadas($lineas_registros);
        $ids_items_facturados = $cantidades_facturadas->pluck('inv_producto_id')->all();
        
        $items_con_receta = RecetaCocina::whereIn('item_platillo_id', $ids_items_facturados)->groupBy('item_platillo_id')->get();

        /**
         * $lineas_desarme se puede rerfactorizar, no es necesario elaborar un String, sino un Array, que se convierte más fácil a JSON
         */
        $lineas_desarme = '[{"inv_producto_id":"","Producto":"","motivo":"","costo_unitario":"","cantidad":"","costo_total":""}';

        $hay_productos = 0;
        foreach ($items_con_receta as $receta_platillo)
        {
            $cantidad_facturada = (float)$cantidades_facturadas->where('inv_producto_id', $receta_platillo->item_platillo_id)->sum('cantidad_facturada');

            $ingredientes = $receta_platillo->ingredientes();
            $costo_total_ingredientes = 0;
            $arr = [];
            foreach ($ingredientes as $ingrediente) {
                $cantidad_a_sacar = $ingrediente['cantidad_porcion'] * $cantidad_facturada;

                $costo_unitario_ingrediente = $ingrediente['ingrediente']->get_costo_promedio( $bodega_default_id );

                $costo_total_ingredientes += $costo_unitario_ingrediente * $ingrediente['cantidad_porcion'];

                // Una linea de salida por cada ingrediente
                $lineas_desarme .= ',{"inv_producto_id":"' . $ingrediente['ingrediente']->id . '","Producto":"' . $ingrediente['ingrediente']->id . ' ' . $ingrediente['ingrediente']->descripcion . ' (' . $ingrediente['ingrediente']->unidad_medida1 . ')","motivo":"' . $motivo_salida->id . '-' . $motivo_salida->descripcion . '","costo_unitario":"$' . $costo_unitario_ingrediente . '","cantidad":"' . $cantidad_a_sacar . ' UND","costo_total":"$' . ($cantidad_a_sacar * $costo_unitario_ingrediente) . '"}';

            }
            
            $lineas_desarme .= ',';
            
            // Un solo registro de entrada para el platillo
            $lineas_desarme .= '{"inv_producto_id":"' . $receta_platillo->item_platillo->id . '","Producto":"' . $receta_platillo->item_platillo->id . ' ' . $receta_platillo->item_platillo->descripcion . ' (' . $receta_platillo->item_platillo->unidad_medida1 . '))","motivo":"' . $motivo_entrada->id . '-' . $motivo_entrada->descripcion . '","costo_unitario":"$' . $costo_total_ingredientes . '","cantidad":"' . $cantidad_facturada . ' UND","costo_total":"$' . ($cantidad_facturada * $costo_total_ingredientes) . '"}';

            $hay_productos++;
        }

        if ( $hay_productos == 0 )
        {
            return 99; // Type integer
        }

        $lineas_desarme .= ',{"inv_producto_id":"","Producto":"00.00","motivo":"$00.00","costo_unitario":""},{"inv_producto_id":"Agregar productos","Producto":"Agregar productos","motivo":"Agregar productos","costo_unitario":"Agregar productos","cantidad":"Agregar productos","costo_total":"Calcular costos"}]';

        return $lineas_desarme; // Type string
    }

    public function resumen_cantidades_facturadas($lineas_registros, $inv_producto_id = null)
    {
        $cantidades_facturadas = collect([]);

        foreach ($lineas_registros as $linea) {

            if (!$this->item_es_un_platillo($linea->inv_producto_id)) {
                continue;
            }

            $cantidades_facturadas->push( [
                'inv_producto_id' => (int)$linea->inv_producto_id,
                'cantidad_facturada' => (float)$linea->cantidad
            ] );
        }

        return $cantidades_facturadas;
    }

    public function item_es_un_platillo($item_id)
    {
        $receta = RecetaCocina::where([
            ['item_platillo_id','=',$item_id]
        ])->get()->first();

        if ($receta == null) {
            return false;
        }

        return true;
    }

    public function create_document_making( $lineas_registros, $bodega_default_id, $fecha, $descripcion)
    {
        $parametros_config_inventarios = config('inventarios');

        $movimiento = $this->get_lineas_registros_desarme($lineas_registros, $bodega_default_id, $parametros_config_inventarios, $fecha);

        if ( gettype($movimiento) == "integer" )
        {
            return 0;
        }

        $request = $this->built_ObjRequets_desarme($parametros_config_inventarios, $bodega_default_id, $movimiento, $fecha, $descripcion);

        $obj_inv_docum_line_serv = new InvDocumentsLinesService();
        $lineas_registros = $obj_inv_docum_line_serv->preparar_array_lineas_registros( $bodega_default_id, $request->movimiento, null);

        $obj_inv_docum_head_serv = new InvDocumentsService();
        $obj_inv_docum_head_serv->store_document($request, $lineas_registros, self::INV_DOC_HEADER_MODEL_NAME);
        
        return 1;
    }

    public function built_ObjRequets_desarme( $parametros_config_inventarios, $bodega_default_id, $movimiento, $fecha, $descripcion)
    {
        $request = new Request;
        $user = Auth::user();
        $request["core_empresa_id"] = $user->empresa_id;
        $request["core_tipo_transaccion_id"] = (int)$parametros_config_inventarios['core_tipo_transaccion_id'];
        $request["core_tipo_doc_app_id"] = (int)$parametros_config_inventarios['core_tipo_doc_app_id'];
        $request["fecha"] = $fecha;
        $request["descripcion"] = $descripcion;
        $request["core_tercero_id"] = (int)$parametros_config_inventarios['core_tercero_id'];
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