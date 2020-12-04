<?php

namespace App\Http\Controllers\VentasPos;

use App\Http\Controllers\Tesoreria\RecaudoController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use View;

use App\VentasPos\ArchivoPlano;

class ArchivoPlanoController extends Controller
{
    public function procesar_archivo( Request $request )
    {
        $archivo = new ArchivoPlano( file( $request->archivo_plano ) );

        $lineas_archivo_plano = $archivo->validar_estructura_archivo();

        return View::make( 'ventas_pos.tbody_tabla_ingreso_lineas_registros', compact( 'lineas_archivo_plano' ) )->render();
    }
}