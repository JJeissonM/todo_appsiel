<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Compras\ComprasPivotItemXml;
use App\Compras\ComprasDocRegistro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ProductoXmlMapeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Guarda el mapeo nombre_xml → inv_producto_id, incluyendo
     * el factor de conversión, la operación y el precio
     * unitario final ajustado.
     *
     * Actualiza tres cosas en el diagrama E-R:
     *   1. compras_pivot_items_xml.inv_producto_id     → inv_productos.id
     *   2. compras_pivot_items_xml.factor_conversion   → factor para futuras facturas
     *   3. compras_pivot_items_xml.tipo_factor         → operación (multiplicación/división)
     *   4. compras_doc_registros.inv_producto_id       → inv_productos.id
     *   5. compras_doc_registros.precio_unitario       → precio convertido/ajustado
     *   6. compras_doc_registros.cantidad              → cantidad convertida
     *
     * POST /compras/mapeo-productos-xml
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'compras_doc_encabezado_id'        => 'required|integer',
            'proveedor_id'                     => 'required|integer',
            'mapeos'                           => 'required|array|min:1',
            'mapeos.*.pivot_id'                => 'required|integer',
            'mapeos.*.compras_doc_registro_id' => 'required|integer',
        ]);

        $guardados = 0;

        foreach ($request->mapeos as $mapeo) {
            $inv_producto_id    = $mapeo['inv_producto_id'] ?? null;
            $factor_conversion  = isset($mapeo['factor_conversion']) && (float)$mapeo['factor_conversion'] > 0
                ? (float)$mapeo['factor_conversion']
                : 1;
            $tipo_factor = isset($mapeo['tipo_factor']) && in_array($mapeo['tipo_factor'], ['multiplicacion', 'division'])
                ? $mapeo['tipo_factor']
                : 'division';
            $cantidad_convertida = isset($mapeo['cantidad_convertida']) && is_numeric($mapeo['cantidad_convertida']) && (float)$mapeo['cantidad_convertida'] > 0
                ? (float)$mapeo['cantidad_convertida']
                : null;
            $precio_unitario_final = isset($mapeo['precio_unitario_final']) && is_numeric($mapeo['precio_unitario_final'])
                ? (float)$mapeo['precio_unitario_final']
                : null;

            $doc_registro = ComprasDocRegistro::find((int)$mapeo['compras_doc_registro_id']);
            if (!$doc_registro) {
                continue;
            }

            // 1. Actualizar/Crear compras_pivot_items_xml (Memoria por proveedor)
            // Usamos la misma lógica que el SyncFacturaCompraService para la "llave" de memoria:
            // Si hay código (SKU), se usa; si no, se usa la descripción del producto.
            $codigo_key = !empty($doc_registro->xml_codigo) ? $doc_registro->xml_codigo : $doc_registro->xml_producto;

            $pivot = null;
            if (isset($mapeo['pivot_id']) && (int)$mapeo['pivot_id'] > 0) {
                $pivot = ComprasPivotItemXml::find((int)$mapeo['pivot_id']);
            }

            if (!$pivot) {
                $pivot = ComprasPivotItemXml::where('proveedor_id', (int)$request->proveedor_id)
                    ->where('codigo_item_xml', $codigo_key)
                    ->first();
            }

            if (!$pivot) {
                $pivot = new ComprasPivotItemXml();
                $pivot->proveedor_id = (int)$request->proveedor_id;
                $pivot->codigo_item_xml = $codigo_key;
            }

            $pivot->inv_producto_id = $inv_producto_id ?: null;
            $pivot->factor_conversion = $factor_conversion;
            $pivot->nombre_item_xml = $doc_registro->xml_producto;
            $pivot->referencia = $doc_registro->xml_codigo; // Guardamos el SKU real para referencia visual

            if (Schema::hasColumn('compras_pivot_items_xml', 'tipo_factor')) {
                $pivot->tipo_factor = $tipo_factor;
            }

            $pivot->save();

            // 2. Actualizar compras_doc_registros (El documento actual)
            $registro_data = [
                'inv_producto_id' => $inv_producto_id ?: 0,
            ];

            if (Schema::hasColumn('compras_doc_registros', 'xml_cantidad') && isset($mapeo['xml_cantidad']) && is_numeric($mapeo['xml_cantidad'])) {
                $registro_data['xml_cantidad'] = (float)$mapeo['xml_cantidad'];
            }
            if (Schema::hasColumn('compras_doc_registros', 'xml_precio_unitario') && isset($mapeo['xml_precio_unitario']) && is_numeric($mapeo['xml_precio_unitario'])) {
                $registro_data['xml_precio_unitario'] = (float)$mapeo['xml_precio_unitario'];
            }

            if ($cantidad_convertida !== null) {
                $registro_data['cantidad'] = $cantidad_convertida;
            }

            if ($precio_unitario_final !== null) {
                $registro_data['precio_unitario'] = $precio_unitario_final;
            } else {
                $cantidad_para_calculo = $cantidad_convertida !== null ? $cantidad_convertida : (float)$doc_registro->cantidad;
                if ($cantidad_para_calculo > 0) {
                    $registro_data['precio_unitario'] = round(((float)$doc_registro->precio_total) / $cantidad_para_calculo, 6);
                }
            }

            $doc_registro->update($registro_data);

            if ($inv_producto_id > 0) {
                $guardados++;
            }
        }

        $variables_url = '?id=' . $request->url_id
            . '&id_modelo=' . $request->url_id_modelo
            . '&id_transaccion=' . $request->url_id_transaccion;

        $msj = "Se ha guardado el progreso del mapeo.";
        if ($guardados > 0) {
            $msj = "{$guardados} producto(s) vinculado(s) correctamente.";
        }

        return redirect(
            'compras/' . $request->compras_doc_encabezado_id . $variables_url
        )->with('flash_message', $msj);
    }
}