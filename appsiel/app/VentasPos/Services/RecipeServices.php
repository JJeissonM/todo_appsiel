<?php 

namespace App\VentasPos\Services;

use Illuminate\Http\Request;

use App\Inventarios\Services\ValidacionExistencias;
use App\Inventarios\Services\InvDocumentsService;
use App\Inventarios\Services\InvDocumentsLinesService;

/**
        * !!!!! No se deberia acceder directamente a los modelos de Inventarios
 */
use App\Inventarios\InvProducto;
use App\Inventarios\InvBodega;
use App\Inventarios\InvMotivo;

use App\Inventarios\InvMovimiento;
use App\Inventarios\RecetaCocina;
use App\VentasPos\FacturaPos;
use App\VentasPos\DocRegistro;

/*use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvCostoPromProducto;*/

use View;
use DB;
use Auth;

class RecipeServices
{
    const INV_DOC_HEADER_MODEL_ID = 25; // Documentos de inventario
    const INV_APPLICATION_ID = 8; // Inventarios
    const INV_DOC_HEADER_MODEL_NAME = 'documentos_inventario';

    // item_ingrediente_id: el que se compra
    // item_platillo_id: el que se vende
    public function get_lineas_registros_desarme( $pdv_id, $bodega_default_id, $parametros_config_inventarios, $fecha )
    {
        $motivo_salida = InvMotivo::find( (int)$parametros_config_inventarios['motivo_salida_id'] );
        $motivo_entrada = InvMotivo::find( (int)$parametros_config_inventarios['motivo_entrada_id'] );

        $cantidades_facturadas = $this->resumen_cantidades_facturadas($pdv_id);
        $ids_items_facturados = $cantidades_facturadas->pluck('inv_producto_id')->all();

        $items_con_receta = RecetaCocina::whereIn('item_platillo_id', $ids_items_facturados)->groupBy('item_platillo_id')->get();


        /**
         * $lineas_desarme se puede rerfactorizar, no es necesario elaborar un String, sino un Array, que se convierte más fácil a JSON
         */
        $lineas_desarme = '[{"inv_producto_id":"","Producto":"","motivo":"","costo_unitario":"","cantidad":"","costo_total":""}';

        // Por cada item vendido con parametros de desarme
        $hay_productos = 0;
        foreach ($items_con_receta as $receta_platillo)
        {
            $cantidad_facturada = (float)$cantidades_facturadas->where('inv_producto_id', $receta_platillo->item_platillo_id)->sum('cantidad_facturada');

            $ingredientes = $receta_platillo->ingredientes();
            $costo_total_ingredientes = 0;
            foreach ($ingredientes as $ingrediente) {
                $cantidad_a_sacar = $ingrediente['cantidad_porcion'] * $cantidad_facturada;

                $costo_unitario_ingrediente = $ingrediente['ingrediente']->get_costo_promedio( $bodega_default_id );

                $costo_total_ingredientes += $costo_unitario_ingrediente * $cantidad_a_sacar;

                // Una linea de salida por cada ingrediente
                $lineas_desarme .= ',{"inv_producto_id":"' . $ingrediente['ingrediente']->id . '","Producto":"' . $ingrediente['ingrediente']->id . ' ' . $ingrediente['ingrediente']->descripcion . ' (' . $ingrediente['ingrediente']->unidad_medida1 . ')","motivo":"' . $motivo_salida->id . '-' . $motivo_salida->descripcion . '","costo_unitario":"$' . $costo_unitario_ingrediente . '","cantidad":"' . $cantidad_a_sacar . ' UND","costo_total":"$' . ($cantidad_a_sacar * $costo_unitario_ingrediente) . '"}';

            }

            $costo_unitario_platillo = $costo_total_ingredientes / $cantidad_facturada;

            $lineas_desarme .= ',';
            
            // Un solo registro de entrada para el platillo
            $lineas_desarme .= '{"inv_producto_id":"' . $receta_platillo->item_platillo->id . '","Producto":"' . $receta_platillo->item_platillo->id . ' ' . $receta_platillo->item_platillo->descripcion . ' (' . $receta_platillo->item_platillo->unidad_medida1 . '))","motivo":"' . $motivo_entrada->id . '-' . $motivo_entrada->descripcion . '","costo_unitario":"$' . $costo_unitario_platillo . '","cantidad":"' . $cantidad_facturada . ' UND","costo_total":"$' . ($cantidad_facturada * $costo_unitario_platillo) . '"}';

            $hay_productos++;
        }

        if ( $hay_productos == 0 )
        {
            return 99; // Type integer
        }

        $lineas_desarme .= ',{"inv_producto_id":"","Producto":"00.00","motivo":"$00.00","costo_unitario":""},{"inv_producto_id":"Agregar productos","Producto":"Agregar productos","motivo":"Agregar productos","costo_unitario":"Agregar productos","cantidad":"Agregar productos","costo_total":"Calcular costos"}]';

        dd($lineas_desarme);

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