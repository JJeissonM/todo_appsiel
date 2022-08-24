<?php

namespace App\Http\Controllers\FacturacionElectronica;

use App\FacturacionElectronica\DATAICO\DocSoporte as DATAICODocSoporte;
use App\Http\Controllers\Controller;

use Redirect;

use App\FacturacionElectronica\Factura;

use App\FacturacionElectronica\DATAICO\FacturaGeneral;
use App\FacturacionElectronica\DocSoporte;

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
                $documento_electronico = new FacturaGeneral( $encabezado_doc, $tipo_operacion );
                break;

            case 'support_doc':
                $encabezado_doc = DocSoporte::find( $doc_encabezado_id );
                $documento_electronico = new DATAICODocSoporte( $encabezado_doc, $tipo_operacion );
                break;
            
            default:
                // code...
                break;
        }
            

        $pdf_url = $documento_electronico->consultar_documento()->pdf_url;
    	
        return Redirect::away( $pdf_url );
    }

}
