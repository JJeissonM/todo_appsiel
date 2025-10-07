<?php

namespace App\Http\Controllers\FacturacionElectronica;

use App\Core\Services\ResolucionFacturacionService;
use App\Core\TipoDocApp;
use App\FacturacionElectronica\DATAICO\DocSoporte as DATAICODocSoporte;
use App\Http\Controllers\Controller;

use App\FacturacionElectronica\Factura;

use App\FacturacionElectronica\DATAICO\FacturaGeneral;
use App\FacturacionElectronica\DocSoporte;
use App\Ventas\VtasDocEncabezado;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class AplicacionController extends Controller
{
    public function index()
    {

        $tipo_doc_app = TipoDocApp::find( (int)config('facturacion_electronica.document_type_id_default') );

        $msj_resolucion_facturacion = (new ResolucionFacturacionService())->validate_resolucion_facturacion($tipo_doc_app, Auth::user()->empresa_id)->message;

    	return view('facturacion_electronica.index', compact('msj_resolucion_facturacion'));
    }

    // SOLO DATAICO
    public function consultar_documentos_emitidos( $doc_encabezado_id, $tipo_operacion )
    {
        switch ( $tipo_operacion )
        {
            case 'support_doc':
                $encabezado_doc = DocSoporte::find( $doc_encabezado_id );
                $documento_electronico = new DATAICODocSoporte( $encabezado_doc, $tipo_operacion );
                break;
            
            default:
                $encabezado_doc = Factura::find( $doc_encabezado_id );
                $documento_electronico = new FacturaGeneral( $encabezado_doc, $tipo_operacion );
                break;
        }
        
        // Representacion Grafica (PDF)
        $json_dataico = $documento_electronico->get_einvoice_in_dataico();

        if ( isset($json_dataico->invoice) ) {
            $pdf_url = $json_dataico->invoice->pdf_url;
        }  

        if ( isset($json_dataico->credit_note) ) {
            $pdf_url = $json_dataico->credit_note->pdf_url;
        }
        
        if ( isset($json_dataico->support_doc) ) {
            $pdf_url = $json_dataico->support_doc->pdf_url;
        }

        if ( !isset($pdf_url) ) {
            return redirect( 'inicio' )->with( 'mensaje_error', '<h5>Documento no encontrado</h5>' . json_encode($json_dataico) );
        }
    	
        return Redirect::away( $pdf_url );
    }

    public function testing()
    {
        $tipo_operacion = 'factura';
        $obj_dataico = new FacturaGeneral(3, $tipo_operacion);

        $label_documento = 'FEV10';
        
        //$tokenPassword = 'd1f0a8fd20c3a7455d63903a8d7c4a48'; // Rey del huevo
        //$tokenPassword = 'a565ea23b7a2e32f700ed36a466b056b'; // Provisiones Leon
        
        $tokenPassword = '24d529763ddaf8265d5fff25afc1afb6'; // Mundo del PVC
        

        $json_doc_electronico_enviado = '{"actions":{"send_dian":true,"send_email":true,"email":"consumidor@gmail.com"},"invoice":{"env":"PRODUCCION","dataico_account_id":"0194ff82-92c4-8128-8ebe-c22e0e16181e","number":10,"issue_date":"18\/02\/2025","payment_date":"18\/02\/2025","invoice_type_code":"FACTURA_VENTA","payment_means_type":"DEBITO","payment_means":"MUTUAL_AGREEMENT","numbering":{"resolution_number":"18764073631207","prefix":"FVE","flexible":true},"notes":["-"],"customer":{"email":"consumidor@gmail.com","phone":"3022788301","party_type":"PERSONA_NATURAL","company_name":"CONSUMIDOR FINAL","first_name":"CONSUMIDOR ","family_name":"FINAL","party_identification":"222222222","party_identification_type":"13","tax_level_code":"SIMPLIFICADO","regimen":"ORDINARIO","department":"20","city":"001","address_line":"Cra 9 # 13A-48"},"items":[{"sku":"123","description":"TORNILLO CAB LENTEJA 1\/2","quantity":1,"price":60,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]}],"charges":[]}}';

        $nota_credito_id = Input::get('nota_credito_id');
        $encabezado_nota_credito = Factura::find( $nota_credito_id );
        
        $nota_credito = new FacturaGeneral( $encabezado_nota_credito, 'nota_credito' );

        
    	$factura_doc_encabezado = VtasDocEncabezado::get_registro_impresion( $encabezado_nota_credito->ventas_doc_relacionado_id ); 

        $mensaje = $nota_credito->procesar_envio_factura( $factura_doc_encabezado );
        
        $tokenPassword = config('facturacion_electronica.tokenPassword');

        $prefijo_resolucion = 'FEV';

        $consecutivo_doc_encabezado = $factura_doc_encabezado->consecutivo;

        $url_emision = config('facturacion_electronica.WSDL');
        
        $url = $url_emision . '?number=' . $prefijo_resolucion . $consecutivo_doc_encabezado;
        
        /*
      try {
         $client = new Client(['base_uri' => $url_emision]);

         $response = $client->get( $url, [
             // un array con la data de los headers como tipo de peticion, etc.
             'headers' => [
                           'content-type' => 'application/json',
                           'auth-token' => $tokenPassword
                        ]
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $response = $e->getResponse();
      }*/
      
      
      $tokenPassword = '24d529763ddaf8265d5fff25afc1afb6'; // Mundo del PVC
      
       $url = 'https://api.dataico.com/dataico_api/v2/invoices?number=FEV9';
     
        $curl = curl_init($url);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
                               'content-type' => 'application/json',
                               'auth-token' => $tokenPassword
                            ]
                    );
                

                
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $image_binary = substr($response, $header_size);
        curl_close($curl);
      
      dd( $url, $response, $header_size, $header, $image_binary );

        //$obj_dataico->enviar_documento_electronico( $tokenPassword, $json_doc_electronico_enviado, $label_documento, true );
    }

}
