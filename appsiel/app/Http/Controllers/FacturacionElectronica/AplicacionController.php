<?php

namespace App\Http\Controllers\FacturacionElectronica;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Redirect;

use App\FacturacionElectronica\Factura;

use App\FacturacionElectronica\DATAICO\FacturaGeneral;

class AplicacionController extends Controller
{
    public function index()
    {
    	return view('facturacion_electronica.index');
    }

    // SOLO DATAICO
    public function consultar_documentos_emitidos( $doc_encabezado_id, $tipo_operacion )
    {
        switch ( $tipo_operacion )
        {
            case 'factura':
                $encabezado_doc = Factura::find( $doc_encabezado_id );
                break;
            
            default:
                // code...
                break;
        }
            

        $factura_dataico = new FacturaGeneral( $encabezado_doc, $tipo_operacion );
        $pdf_url = $factura_dataico->consultar_documento();
    	
        return Redirect::away( $pdf_url );

        //return view('facturacion_electronica.consultar_documentos_emitidos');
    }

}
