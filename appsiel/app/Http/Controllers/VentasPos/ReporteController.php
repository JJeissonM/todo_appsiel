<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use DB;
use View;
use Lava;
use Input;
use Cache;


use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Sistema\ModeloController;


// Modelos
use App\VentasPos\Pdv;
use App\VentasPos\FacturaPos;


use App\Core\Empresa;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\ConsecutivoDocumento;
use App\Core\Tercero;
use App\Sistema\Aplicacion;

use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoRecaudosLibreta;
use App\Tesoreria\TesoCarteraEstudiante;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoEntidadFinanciera;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMovimiento;

class ReporteController extends Controller
{

    public function get_saldos_caja_pdv( $pdv_id, $fecha_desde, $fecha_hasta )
    {
        $pdv = Pdv::find( $pdv_id );

        //dd( $pdv );

        $encabezados_documentos = FacturaPos::where('pdv_id',$pdv_id)->where('estado','Pendiente')->get();

        $total_contado = $encabezados_documentos->where('forma_pago','contado')->sum('valor_total');
        $total_credito = $encabezados_documentos->where('forma_pago','credito')->sum('valor_total');

        $resumen_ventas = View::make( 'ventas_pos.resumen_ventas', compact( 'total_contado', 'total_credito' ) )->render();
        
        $vista_movimiento = $this->teso_movimiento_caja_pdv( $fecha_desde, $fecha_hasta, $pdv->caja_default_id );

        return $resumen_ventas . '<br><br>' . $vista_movimiento;

    }

    public function consultar_documentos_pendientes( $pdv_id, $fecha )
    {
        $pdv = Pdv::find( $pdv_id );

        $encabezados_documentos = FacturaPos::consultar_encabezados_documentos( $pdv_id, $fecha, 'Pendiente' );

        dd( $encabezados_documentos );

        $resumen_ventas = View::make( 'ventas_pos.resumen_ventas', compact( 'total_contado', 'total_credito' ) )->render();
        
        $vista_movimiento = $this->teso_movimiento_caja_pdv( $fecha_desde, $fecha_hasta, $pdv->caja_default_id );

        return $resumen_ventas . '<br><br>' . $vista_movimiento;

    }

    public function teso_movimiento_caja_pdv( $fecha_desde, $fecha_hasta, $teso_caja_id )
    {
        $teso_cuenta_bancaria_id = 0;

        $caja = TesoCaja::find( $teso_caja_id );
        $mensaje = $caja->descripcion;

        $saldo_inicial = TesoMovimiento::get_saldo_inicial( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde );

        $movimiento = TesoMovimiento::get_movimiento( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $fecha_hasta );

        $vista = View::make('tesoreria.reportes.movimiento_caja_bancos', compact( 'fecha_desde', 'saldo_inicial', 'movimiento', 'mensaje'))->render();

        return $vista;
    }
}
