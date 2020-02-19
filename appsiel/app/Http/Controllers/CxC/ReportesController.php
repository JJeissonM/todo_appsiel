<?php

namespace App\Http\Controllers\CxC;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use Auth;
use Form;
use View;
use Cache;

use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;
use App\Core\Empresa;

use App\CxC\DocumentosPendientes;


class ReportesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function documentos_pendientes(Request $request)
    {
                
        $operador = '=';
        $cadena = $request->core_tercero_id;

        if ( $request->core_tercero_id == '' )
        {
            $operador = 'LIKE';
            $cadena = '%'.$request->core_tercero_id.'%';
        }
    
        $movimiento = DocumentosPendientes::get_documentos_referencia_tercero( $operador, $cadena );

        $vista = View::make( 'cxc.incluir.documentos_pendientes', compact('movimiento') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
   
        return $vista;
    }
}