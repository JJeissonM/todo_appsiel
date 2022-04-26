<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use View;

use Cache;

use App\Sistema\Aplicacion;

use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvMovimiento;
use App\Ventas\VtasMovimiento;
use App\VentasPos\FacturaPos;
use App\VentasPos\Movimiento;
use App\Compras\ComprasMovimiento;

class ReporteController extends Controller
{
    public function movimiento_con_fecha_distinta_a_su_creacion(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $aplicacion = Aplicacion::find($request->core_app_id);

        $movimientos = [];
        switch ($request->core_app_id) {
            case '3':
                $movimientos = TesoMovimiento::whereBetween( 'created_at', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:00'] )->get();
                break;
                
            case '8':
                $movimientos = InvMovimiento::whereBetween( 'created_at', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:00'] )->get();
                break;

            case '9':
                $movimientos = ComprasMovimiento::whereBetween( 'created_at', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:00'] )->get();
                break;

            case '13':
                $movimientos = VtasMovimiento::whereBetween( 'created_at', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:00'] )->get();
                break;

            case '14':
                $movimientos = ContabMovimiento::whereBetween( 'created_at', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:00'] )->get();
                break;

            case '20':
                $movimientos = Movimiento::whereBetween( 'created_at', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:00'] )->get();
                break;
            
            default:
                # code...
                break;
        }            

        $arr_movin = [];
        foreach ($movimientos as $movimiento) {
            $created_at = explode(" ",$movimiento->created_at)[0];
            
            if($created_at != $movimiento->fecha)
            {
                $arr_movin[] = $movimiento;
            }
        }
        
        $vista = View::make( 'core.reportes.auditoria_movin_fecha_distinta_creacion', compact( 'fecha_desde', 'fecha_hasta', 'arr_movin','aplicacion') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }
}
