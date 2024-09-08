<?php

namespace App\Http\Controllers\FacturacionElectronica;

use App\FacturacionElectronica\DATAICO\DocSoporte as DATAICODocSoporte;
use App\Http\Controllers\Controller;

use App\FacturacionElectronica\Factura;

use App\FacturacionElectronica\DATAICO\FacturaGeneral;
use App\FacturacionElectronica\DocSoporte;
use Illuminate\Support\Facades\Redirect;

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
        
        // Representacion Grafica (PDF)
        $json_dataico = $documento_electronico->get_einvoice_in_dataico();
        $pdf_url = '#';
        if ( isset($json_dataico->invoice) ) {
            $pdf_url = $json_dataico->invoice->pdf_url;
        }       
    	
        return Redirect::away( $pdf_url );
    }

    public function testing()
    {
        $tipo_operacion = 'factura';
        $obj_dataico = new FacturaGeneral(3, $tipo_operacion);

        $label_documento = 'FPL3';
        
        //$tokenPassword = 'd1f0a8fd20c3a7455d63903a8d7c4a48'; // Rey del huevo
        $tokenPassword = 'a565ea23b7a2e32f700ed36a466b056b'; // Provisiones Leon

        $json_doc_electronico_enviado = '{"actions":{"send_dian":true,"send_email":true,"email":"restauranteelpalodemango@gmail.com"},"invoice":{"env":"PRODUCCION","dataico_account_id":"018dfbcb-b9e2-8ec7-a27a-019cad0c4bba","number":4,"issue_date":"07\/09\/2024","payment_date":"07\/09\/2024","invoice_type_code":"FACTURA_VENTA","payment_means_type":"DEBITO","payment_means":"MUTUAL_AGREEMENT","numbering":{"resolution_number":" 18764078952935 ","prefix":"FPL","flexible":true},"notes":["---"],"customer":{"email":"restauranteelpalodemango@gmail.com","phone":"3017477308","party_type":"PERSONA_NATURAL","company_name":"Restaurante palo de mango","first_name":"ZULEIDY","family_name":"TORRES","party_identification":"1065643714","tax_level_code":"SIMPLIFICADO","regimen":"ORDINARIO","department":"20","city":"001","address_line":"calle 17 # 9 -74 barrio centro"},"items":[{"sku":"7","description":"ARROZ SABROSON X 1OOOGR","quantity":13,"price":4100,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"1","description":"ACEITE VIUDA X 3000ML","quantity":1,"price":17000,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"30","description":"ACEITE VIUDA X 900ML","quantity":2,"price":5500,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"20","description":"AZUCAR SUELTA","quantity":3,"price":4200,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"15","description":"HUEVO X 30UND","quantity":1.5,"price":13700,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"8","description":"SAL REFISAL X 500GR","quantity":5,"price":1500,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"9","description":"FIDEO COMARICO X 500GR","quantity":1,"price":3500,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"167","description":"MAGUI AMARILLA CUBO","quantity":16,"price":500,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"2","description":"COLOR REY CHAPETA","quantity":6,"price":1800,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"25","description":"DON GUSTICO X 18GR","quantity":6,"price":1100,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"18","description":"FRIJOL ROSADO X KL","quantity":1.5,"price":9400,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"3","description":"VINAGRETA BARY X 200GR","quantity":4,"price":4300,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"24","description":"MANTEQUILLA RAMA BARRA X 125GR","quantity":1,"price":3300,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"21","description":"CHORIZO PAISA X 500GR X 10UND","quantity":3,"price":11800,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"22","description":"RANCHERA X 5UND","quantity":3,"price":6000,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"11","description":"PANELA OCA\u00d1ERA","quantity":12,"price":2200,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"19","description":"ARVEJA X KL","quantity":1.5,"price":6000,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"17","description":"QUESO SEMIDURO X KL","quantity":1.25,"price":16000,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"10","description":"CAFE SELLO ROJO X 50GR","quantity":2,"price":1800,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"12","description":"DET DERSA X 500GR","quantity":1,"price":5000,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"279","description":"JAB LIQUIDO 123 X 500ML","quantity":2,"price":4800,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"282","description":"ESPONJA BRILLANTE","quantity":2,"price":1200,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"69","description":"AJI BASCO X 100 GR","quantity":8,"price":4200,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"167","description":"MAGUI AMARILLA CUBO","quantity":6,"price":500,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"25","description":"DON GUSTICO X 18GR","quantity":3,"price":1000,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]},{"sku":"14","description":"JABON ENCUERO","quantity":2,"price":1500,"taxes":[{"tax_rate":0,"tax_category":"IVA"}]}],"charges":[]}}';

        $obj_dataico->enviar_documento_electronico( $tokenPassword, $json_doc_electronico_enviado, $label_documento, true );
    }

}
