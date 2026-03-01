<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Compras\Services\RetencionFuenteService;

class RetencionFuenteController extends Controller
{
    public function activas(RetencionFuenteService $service)
    {
        return response()->json($service->get_retenciones_activas());
    }
}
