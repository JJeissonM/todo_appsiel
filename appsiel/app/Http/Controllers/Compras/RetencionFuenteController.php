<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Compras\ComprasDocEncabezado;
use App\Compras\Services\ContabilidadService;
use App\Compras\Services\RetencionFuenteService;
use Illuminate\Http\Request;

class RetencionFuenteController extends Controller
{
    public function activas(RetencionFuenteService $service)
    {
        return response()->json($service->get_retenciones_activas());
    }

    public function conceptos(Request $request, RetencionFuenteService $service)
    {
        return response()->json($service->conceptos_anuales($request->get('anio')));
    }

    public function preliquidar_documento($id, RetencionFuenteService $service)
    {
        $docEncabezado = ComprasDocEncabezado::findOrFail((int)$id);

        return response()->json($service->liquidar_documento($docEncabezado, false));
    }

    public function aplicar_documento($id, RetencionFuenteService $service)
    {
        $docEncabezado = ComprasDocEncabezado::findOrFail((int)$id);
        $resultado = $service->liquidar_documento($docEncabezado, true);
        (new ContabilidadService())->aplicar_retenciones_por_linea_compras($docEncabezado);

        return response()->json($resultado);
    }
}
