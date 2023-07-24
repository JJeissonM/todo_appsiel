<?php

namespace App\Http\Controllers\VentasPos;

use App\Http\Controllers\Controller;

use App\VentasPos\FacturaPos;

use App\Ventas\VtasMovimiento;

class ProcesosController extends Controller
{
    public function reconstruir_mov_ventas_documento($documento_id)
    {
        $result = $this->reconstruir_movimiento_ventas_un_documento($documento_id);

        if($result->status=='error')
        {
            return redirect( 'pos_factura/' . $documento_id . '?id=20&id_modelo=230&id_transaccion=47' )->with('mensaje_error', 'Factura en estado Pendiente. Aún no tiene movimiento de ventas.');
        }

        return redirect( 'pos_factura/' . $documento_id . '?id=20&id_modelo=230&id_transaccion=47' )->with('flash_message', 'Movimiento de ventas actualizado.');
    }

    public function reconstruir_movimiento_ventas_un_documento($documento_id)
    {
        $documento = FacturaPos::find($documento_id);

        if ($documento->estado == 'Pendiente') {
            return (object)['status'=>'error'];
        }

        // Eliminar movimientos actuales
        VtasMovimiento::where([
                ['core_tipo_transaccion_id','=', $documento->core_tipo_transaccion_id],
                ['core_tipo_doc_app_id','=', $documento->core_tipo_doc_app_id],
                ['consecutivo','=', $documento->consecutivo]
            ])
            ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = $documento->lineas_registros;
        
        $datos = $documento->toArray();
        $total_documento = 0;
        $datos['zona_id'] = $documento->cliente->zona_id;
        $datos['clase_cliente_id'] = $documento->cliente->clase_cliente_id;
        $datos['equipo_ventas_id'] = $documento->cliente->vendedor->equipo_ventas_id; 
        foreach ($registros_documento as $linea)
        {
            VtasMovimiento::create( 
                $datos +
                $linea->toArray()
            );
            $total_documento += $linea->precio_total;
        }

        $documento->valor_total = $total_documento;
        $documento->save();

        return (object)['status'=>'success'];
    }
}