<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Core\EncabezadoDocumentoTransaccion;

use Auth;
use View;
use Input;
use Form;

use App\Tesoreria\RegistrosMediosPago;

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
        
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

        $lineas_registros = json_decode( $request->lineas_registros );

        $registros_medio_pago = new RegistrosMediosPago;
        $campo_lineas_recaudos = $registros_medio_pago->depurar_tabla_registros_medios_recaudos( $request->all()['lineas_registros_medios_recaudo'] );
        $datos['registros_medio_pago'] = $registros_medio_pago->get_datos_ids( $campo_lineas_recaudos );
        CompraController::crear_lineas_registros_compras( $datos, $doc_encabezado, $lineas_registros );

        return redirect('compras/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

}