<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\CrudController;

use Auth;
use View;
use Input;
use Form;


class FacturaEntradaPendienteController extends CompraController
{
    protected $doc_encabezado;
    protected $empresa, $app, $modelo, $transaccion, $variables_url;
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store( Request $request )
    {
        $datos = $request->all();

        $doc_encabezado = CrudController::crear_nuevo_registro( $request, $request->url_id_modelo );

        $lineas_registros = json_decode( $request->lineas_registros );

        CompraController::crear_lineas_registros_compras( $datos, $doc_encabezado, $lineas_registros );

        return redirect('compras/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

}