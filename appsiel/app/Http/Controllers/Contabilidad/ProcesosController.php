<?php

namespace App\Http\Controllers\Contabilidad;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use Auth;
use Form;
use View;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Core\Tercero;
use App\Core\Empresa;

use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabPeriodoEjercicio;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

class ProcesosController extends Controller
{
    public function generar_listado_cierre_ejercicio( Request $request )
    {
        $array_clases_cuentas = [ 4, 5, 6, 7];
        $periodo_ejercicio = ContabPeriodoEjercicio::find( $request->periodo_ejercicio_id );
        foreach ($array_clases_cuentas as $key => $clase_cuenta_id )
        {
            $movimiento_clase_cuenta = ContabMovimiento::get_saldo_movimiento_clase_cuenta($periodo_ejercicio->fecha_desde, $periodo_ejercicio->fecha_hasta, $clase_cuenta_id,  );
            dd( $movimiento_clase_cuenta );
        }
    }
}