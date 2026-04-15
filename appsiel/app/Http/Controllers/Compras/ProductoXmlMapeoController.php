<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Compras\ComprasPivotItemXml;
use App\Compras\ComprasDocRegistro;
use Illuminate\Http\Request;

class ProductoXmlMapeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Guarda el mapeo nombre_xml → inv_producto_id.
     *
     * Actualiza dos tablas relacionadas en el diagrama E-R:
     *   1. compras_pivot_items_xml.inv_producto_id → inv_productos.id
     *   2. compras_doc_registros.inv_producto_id   → inv_productos.id
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
            if (empty($mapeo['inv_producto_id'])) {
                continue;
            }

            // 1. Actualizar compras_pivot_items_xml
            ComprasPivotItemXml::where('id', $mapeo['pivot_id'])
                ->where('proveedor_id', $request->proveedor_id)
                ->update(['inv_producto_id' => $mapeo['inv_producto_id']]);

            // 2. Actualizar compras_doc_registros
            ComprasDocRegistro::where('id', $mapeo['compras_doc_registro_id'])
                ->where('compras_doc_encabezado_id', $request->compras_doc_encabezado_id)
                ->update(['inv_producto_id' => $mapeo['inv_producto_id']]);

            $guardados++;
        }

        $variables_url = '?id=' . $request->url_id
            . '&id_modelo=' . $request->url_id_modelo
            . '&id_transaccion=' . $request->url_id_transaccion;

        return redirect(
            'compras/' . $request->compras_doc_encabezado_id . $variables_url
        )->with('flash_message', "{$guardados} producto(s) vinculado(s) correctamente.");
    }
}