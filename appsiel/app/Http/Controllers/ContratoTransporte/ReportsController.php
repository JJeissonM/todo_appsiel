<?php

namespace App\Http\Controllers\ContratoTransporte;

use App\Contratotransporte\Services\FuecServices;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get_fuecs_list(Request $request)
    {
        $user = Auth::user();

        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $contracts = (new FuecServices())->get_listado_fuecs_entre_fechas( $user, $fecha_desde, $fecha_hasta );

        //dd( (int)$request->vehiculo_id, $contracts->toArray(), $contracts->where('vehiculo_id', (int)$request->vehiculo_id ) );

        if ( (int)$request->vehiculo_id != 0 ) {
             $contracts = $contracts->where('vehiculo_id', (int)$request->vehiculo_id );
        }

        $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/'.$user->empresa->imagen;

        $vista = View::make( 'contratos_transporte.reports.fuecs_list', compact( 'contracts', 'url', 'fecha_desde', 'fecha_hasta' ) )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
   
        return $vista;
    }
}