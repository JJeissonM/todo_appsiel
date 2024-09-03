<?php 

namespace App\VentasPos\Services;

use Illuminate\Http\Request;

use App\Inventarios\Services\InvDocumentsService;
use App\Inventarios\Services\InvDocumentsLinesService;

use App\Inventarios\InvMotivo;
use App\Inventarios\InvMovimiento;
use App\Inventarios\RecetaCocina;
use App\VentasPos\FacturaPos;
use App\VentasPos\DocRegistro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecipeServices
{
    const INV_DOC_HEADER_MODEL_ID = 25; // Documentos de inventario
    const INV_APPLICATION_ID = 8; // Inventarios
    const INV_DOC_HEADER_MODEL_NAME = 'documentos_inventario';

    // item_ingrediente_id: el que se compra
    // item_platillo_id: el que se vende
    public function get_lineas_registros_ensamble( $cantidades_facturadas, $bodega_default_id, $parametros_config_inventarios, $fecha )
    {
        $motivo_salida = InvMotivo::find( (int)$parametros_config_inventarios['motivo_salida_id'] );
        $motivo_entrada = InvMotivo::find( (int)$parametros_config_inventarios['motivo_entrada_id'] );

        $ids_items_facturados = $cantidades_facturadas->pluck('inv_producto_id')->all();

        $items_con_receta = RecetaCocina::whereIn('item_platillo_id', $ids_items_facturados)->get()->groupBy('item_platillo_id');

        /**
         * $lineas_desarme se puede rerfactorizar, no es necesario elaborar un String, sino un Array, que se convierte más fácil a JSON
         */
        $lineas_desarme = '[{"inv_producto_id":"","Producto":"","motivo":"","costo_unitario":"","cantidad":"","costo_total":""}';

        // Por cada item vendido con parametros de desarme
        $hay_productos = 0;
        foreach ($items_con_receta as $item_platillo_id => $lineas_receta_platillo)
        {
            $cantidad_facturada = (float)$cantidades_facturadas->where('inv_producto_id', $item_platillo_id)->sum('cantidad_facturada');
            
            // Verificar las existencias actuales del producto terminado (platillo).
            $existencia_actual = InvMovimiento::get_cantidad_existencia_item( $item_platillo_id, $bodega_default_id, $fecha );
            $cantidad_a_ingresar_platillo_facturado = $cantidad_facturada - $existencia_actual;
            
            if ($cantidad_a_ingresar_platillo_facturado <= 0) {
                continue;
            }

            //$ingredientes = $receta_platillo->ingredientes();
            $costo_total_ingredientes = 0;
            foreach ($lineas_receta_platillo as $linea_receta) {
                $cantidad_a_sacar_ingrediente = $linea_receta->cantidad_porcion * $cantidad_a_ingresar_platillo_facturado;

                // ---------------- Ensambles anidados
                $this->create_document_making_for_ingredient( $linea_receta->item_ingrediente, $bodega_default_id, $fecha, $parametros_config_inventarios, $cantidades_facturadas, $items_con_receta );
                // ----------------

                $costo_unitario_ingrediente = $linea_receta->item_ingrediente->get_costo_promedio( $bodega_default_id );

                $costo_total_ingredientes += $costo_unitario_ingrediente * $linea_receta->cantidad_porcion;

                // Una linea de salida por cada ingrediente
                $lineas_desarme .= ',{"inv_producto_id":"' . $linea_receta->item_ingrediente_id . '","Producto":"' . $linea_receta->item_ingrediente_id . ' ' . $linea_receta->item_ingrediente->descripcion . ' (' . $linea_receta->item_ingrediente->unidad_medida1 . ')","motivo":"' . $motivo_salida->id . '-' . $motivo_salida->descripcion . '","costo_unitario":"$' . $costo_unitario_ingrediente . '","cantidad":"' . $cantidad_a_sacar_ingrediente . ' UND","costo_total":"$' . ($cantidad_a_sacar_ingrediente * $costo_unitario_ingrediente) . '"}';
            }

            $lineas_desarme .= ',';
            
            // Un solo registro de entrada para el platillo
            $item_platillo = $lineas_receta_platillo->first()->item_platillo;

            $lineas_desarme .= '{"inv_producto_id":"' . $item_platillo->id . '","Producto":"' . $item_platillo->id . ' ' . $item_platillo->descripcion . ' (' . $item_platillo->unidad_medida1 . '))","motivo":"' . $motivo_entrada->id . '-' . $motivo_entrada->descripcion . '","costo_unitario":"$' . $costo_total_ingredientes . '","cantidad":"' . $cantidad_a_ingresar_platillo_facturado . ' UND","costo_total":"$' . ($cantidad_a_ingresar_platillo_facturado * $costo_total_ingredientes) . '"}';

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
     * 
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

    public function create_document_making( $cantidades_facturadas, $bodega_default_id, $fecha, $parametros_config_inventarios, $descripcion_encabezado = '' )
    {
        $movimiento = $this->get_lineas_registros_ensamble($cantidades_facturadas, $bodega_default_id, $parametros_config_inventarios, $fecha);

        if ( gettype($movimiento) == "integer" )
        {
            return 0;
        }

        $request = $this->built_ObjRequets_desarme($parametros_config_inventarios, $bodega_default_id, $movimiento, $fecha, $descripcion_encabezado);

        $obj_inv_docum_line_serv = new InvDocumentsLinesService();
        $lineas_registros = $obj_inv_docum_line_serv->preparar_array_lineas_registros( $bodega_default_id, $request->movimiento, null);

        $obj_inv_docum_head_serv = new InvDocumentsService();
        $obj_inv_docum_head_serv->store_document($request, $lineas_registros, self::INV_DOC_HEADER_MODEL_NAME);
        
        return 1;
    }

    public function built_ObjRequets_desarme( $parametros_config_inventarios, $bodega_default_id, $movimiento, $fecha, $descripcion_encabezado)
    {
        $request = new Request;
        $user = Auth::user();
        $request["core_empresa_id"] = $user->empresa_id;
        $request["core_tipo_transaccion_id"] = (int)$parametros_config_inventarios['core_tipo_transaccion_id'];
        $request["core_tipo_doc_app_id"] = (int)$parametros_config_inventarios['core_tipo_doc_app_id'];
        $request["fecha"] = $fecha;
        $request["core_tercero_id"] = (int)$parametros_config_inventarios['core_tercero_id'];
        $request["descripcion"] = $descripcion_encabezado;
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

    public function cambiar_items_con_contornos($lineas_registros)
    {
        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {            
            if ( (int)$lineas_registros[$i]->inv_producto_id == 0 )
            {
                continue;
            }

            $arr_items_contorno_ids = explode(',',$lineas_registros[$i]->lista_oculta_items_contorno_ids);
            
            if ( $arr_items_contorno_ids[0] == '' )
            {
                continue;
            }

            array_push( $arr_items_contorno_ids, $lineas_registros[$i]->inv_producto_id);

            $nuevo_item_id = $this->get_nuevo_item_id( $lineas_registros[$i]->inv_producto_id,$arr_items_contorno_ids);

            if ( $nuevo_item_id == null )
            {
                continue;
            }
            
            $lineas_registros[$i]->inv_producto_id = $nuevo_item_id;
        } // Fin por cada registro

        return $lineas_registros;
    }

    public function get_nuevo_item_id( int $platillo_principal_id, array $arr_items_contorno_ids)
    {
        $matched_ids = RecetaCocina::where([
            ['item_ingrediente_id', '=', $platillo_principal_id]
        ])->get()
            ->pluck('item_platillo_id')
            ->toArray();

        if (empty($matched_ids)) {
            return null;
        }

        $grupos_platillos = RecetaCocina::whereIn('item_platillo_id', $matched_ids)->get()->groupBy('item_platillo_id');
        
        $keys_auxiliary_array = [];
        foreach ($grupos_platillos as $item_platillo_id => $grupo) {
            $key_array = (object)[
                'item_platillo_id' => $item_platillo_id,
                'arr_ids_items_ingredientes' => []
            ];
            
            foreach ($grupo as $linea) {
                $key_array->arr_ids_items_ingredientes[] =  $linea->item_ingrediente_id;
            }

            $keys_auxiliary_array[] = $key_array;
        }

        foreach ($keys_auxiliary_array as $linea) {            

            if ($this->sameElements($linea->arr_ids_items_ingredientes,$arr_items_contorno_ids)) {
                return $linea->item_platillo_id;
            }
        }

        return null;
    }

    public function sameElements($a, $b)
    {
        sort($a);
        sort($b);
        return $a == $b;
    }

    /**
     * Se hace Un (1) ensamble para TODAS las cantidades del ingrediente requeridas en la preparación de TODOS los platillo facturados.
     */
    public function create_document_making_for_ingredient( $item_ingrediente, $bodega_default_id, $fecha, $parametros_config_inventarios, $cantidades_facturadas_platillos, $items_con_receta )
    {
        $lineas_recetas_ingrediente = RecetaCocina::where('item_platillo_id', $item_ingrediente->id)->get()->first();

        // Ingrediente no tiene receta
        if ($lineas_recetas_ingrediente == null) {
            return false;
        }
        
        $cantidades_facturadas = $this->get_obj_cantidades_facturadas_ingrediente( $item_ingrediente->id, $cantidades_facturadas_platillos, $items_con_receta );
        
        return $this->create_document_making( $cantidades_facturadas, $bodega_default_id, $fecha, $parametros_config_inventarios, 'Ensamble de ' . $item_ingrediente->descripcion  . ' para todos los platillos facturados.');
    }

    public function get_obj_cantidades_facturadas_ingrediente( $item_ingrediente_id, $cantidades_facturadas_platillos, $items_con_receta )
    {
        $cantidades2 = collect();

        $cantidad_facturada_ingrediente = 0;
        foreach ($items_con_receta as $item_platillo_id => $lineas_receta_platillo)
        {
            foreach ($cantidades_facturadas_platillos as $linea_cantidades_platillo) {
                if ($item_platillo_id != $linea_cantidades_platillo->inv_producto_id) {
                    continue;
                }

                foreach ($lineas_receta_platillo as $linea_receta) {
                    
                    if ($item_ingrediente_id != $linea_receta->item_ingrediente_id) {
                        continue;
                    }

                    $cantidad_facturada_ingrediente += $linea_receta->cantidad_porcion * $linea_cantidades_platillo->cantidad_facturada;
                }
            }
        }

        $aux_registro = new DocRegistro();
        $aux_registro->cantidad_facturada = $cantidad_facturada_ingrediente;
        $aux_registro->inv_producto_id = $item_ingrediente_id;

        $cantidades2->push( $aux_registro );

        return $cantidades2;
    }
}