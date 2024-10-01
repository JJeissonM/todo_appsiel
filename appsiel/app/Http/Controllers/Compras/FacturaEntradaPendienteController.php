<?php

namespace App\Http\Controllers\Compras;

use App\Contabilidad\Impuesto;
use Illuminate\Http\Request;

use App\Core\EncabezadoDocumentoTransaccion;
use App\Inventarios\InvDocRegistro;
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
        
        $datos['registros_medio_pago'] = $registros_medio_pago->get_datos_ids( $request->all()['lineas_registros_medios_recaudo'], null, self::get_total_documento_desde_lineas_registros_desde_entrada( $doc_encabezado, $lineas_registros ), 'compras' );
        
        CompraController::crear_lineas_registros_compras( $datos, $doc_encabezado, $lineas_registros );

        return redirect('compras/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

    
    public static function get_total_documento_desde_lineas_registros_desde_entrada( $doc_encabezado, $lineas_registros )
    {
        $total_documento = 0;
        // Por cada entrada de almacén pendiente
        $cantidad_registros = count( $lineas_registros );

        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_entrada_id = (int)$lineas_registros[$i]->id_doc;

            $registros_entrada = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_entrada_id )->get();

            foreach ($registros_entrada as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad;

                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, $doc_encabezado->proveedor_id, 0 );

                // El costo_unitario se guardó con los descuentos restados
                $precio_unitario = $un_registro->costo_unitario * ( 1 + $tasa_impuesto  / 100 );

                $precio_total = $precio_unitario * $cantidad;                

                $total_documento += $precio_total;

            } // Fin por cada registro de la entrada
        }

        return $total_documento;
    }

}