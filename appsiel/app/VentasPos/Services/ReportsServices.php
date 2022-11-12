<?php 

namespace App\VentasPos\Services;

use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoEntidadFinanciera;
use App\Tesoreria\TesoMovimiento;
use App\VentasPos\Movimiento;
use App\VentasPos\Pdv;

class ReportsServices
{    
    public function resumen_ventas_arqueo_caja($fecha, $teso_caja_id)
    {
        $pdv = Pdv::where('caja_default_id',$teso_caja_id)->get()->first();

        if ($pdv == null) {
            return (object)[
                'status' => 'error',
                'message' => 'La caja no está asociada a ningún Punto de Ventas.',
            ];
        }

        $movimientos_pdv = Movimiento::get_movimiento_ventas($fecha, $fecha, null,'Contabilizado');

        $total_credito = 0;
        $arr_consecutivos = [];
        foreach ($movimientos_pdv as $movimiento) {

            if ($movimiento->pdv_id != $pdv->id) {
                continue;
            }

            if ($movimiento->forma_pago == 'credito') {
                $total_credito += $movimiento->precio_total;
            }

            $arr_consecutivos[] = $movimiento->consecutivo;
            $core_tipo_transaccion_id = $movimiento->core_tipo_transaccion_id;
            $core_tipo_doc_app_id = $movimiento->core_tipo_doc_app_id;
        }
        
        $movimientos_tesoreria_para_pdv = TesoMovimiento::where([
                            ['core_tipo_transaccion_id', '=', $core_tipo_transaccion_id ],
                            ['core_tipo_doc_app_id', '=', $core_tipo_doc_app_id ]
                        ])
                        ->whereIn('consecutivo',$arr_consecutivos)
                        ->get();

        $total_contado = 0;
        foreach ($movimientos_tesoreria_para_pdv as $movimiento) {
            if ($movimiento->teso_caja_id == $teso_caja_id) {
                $total_contado += $movimiento->valor_movimiento;
            }
        }

        $cuentas_bancarias = TesoCuentaBancaria::where('estado','Activo')->get();

        $totales_cuentas_bancarias = [];
        foreach ($cuentas_bancarias as $key => $cuenta_bancaria) {
            if ($cuenta_bancaria->id == 0) {
                continue;
            }

            $total_cuenta_bancaria = 0;
            foreach ($movimientos_tesoreria_para_pdv as $movimiento) {
                if ($movimiento->teso_cuenta_bancaria_id == $cuenta_bancaria->id) {
                    $total_cuenta_bancaria += $movimiento->valor_movimiento;
                }
            }

            $totales_cuentas_bancarias[] = [
                'label' => TesoEntidadFinanciera::find($cuenta_bancaria->entidad_financiera_id)->descripcion . " - Nro. " . $cuenta_bancaria->descripcion,
                'total' => $total_cuenta_bancaria
            ];
        }
        
        return (object)[
            'status' => 'success',
            'total_contado' => $total_contado,
            'total_credito' => $total_credito,
            'totales_cuentas_bancarias' => $totales_cuentas_bancarias
        ];
    }        
}