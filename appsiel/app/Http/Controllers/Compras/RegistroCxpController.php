<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;


use Spatie\Permission\Models\Permission;

use App\Http\Controllers\Sistema\CrudController;
use App\Http\Controllers\Sistema\EmailController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

// Modelos
use App\Core\Empresa;
use App\Core\Tercero;

use App\Compras\Proveedor;

use App\Contabilidad\ContabMovimiento;


class RegistroCxpController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request['core_tipo_transaccion_id'] = $request->url_id_transaccion;

        // Se crea un nuevo registro
        $registro = CrudController::crear_nuevo_registro( $request, $request->url_id_modelo );

        // Contabilizar

        $detalle_operacion = 'Creación CxP Directa. Documento proveedor: '.$request->doc_proveedor_prefijo.'-'.$request->doc_proveedor_consecutivo;
        
        // Cuenta Contrapartida (DB)

        ContabilidadController::contabilizar_registro( array_merge( $request->all(), [ 'consecutivo' => $registro->consecutivo ] ), $request->cta_contrapartida_id, $detalle_operacion, $request->saldo_pendiente, 0);

        // Cta. Por Pagar (CR)
        $cxp_id = Proveedor::get_cuenta_por_pagar( $request->proveedor_id );
        ContabilidadController::contabilizar_registro( array_merge( $request->all(), [ 'consecutivo' => $registro->consecutivo ] ), $cxp_id, $detalle_operacion, 0, $request->saldo_pendiente);

        return redirect( 'web/create?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with( 'flash_message','Registro CREADO correctamente.' );
    }

}