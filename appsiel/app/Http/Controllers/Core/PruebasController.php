<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

class PruebasController extends Controller
{

    public function prueba_directa()
    {
        $encabezado_factura = \App\Ventas\VtasDocEncabezado::find(152);

        //$encabezado_factura->actualizar_valor_total();

		//$encabezado_factura->contabilizar_movimiento_debito();
        //$encabezado_factura->contabilizar_movimiento_credito();

       // $encabezado_factura->crear_registro_pago();
    }

}