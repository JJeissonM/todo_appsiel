<?php 

namespace App\FacturacionElectronica\Services;

use App\FacturacionElectronica\ResultadoEnvioDocumento;

class SendingResultServices
{
    public function get_sending_result( $doc_encabezado_id )
    {
        $result = ResultadoEnvioDocumento::where('vtas_doc_encabezado_id', $doc_encabezado_id)
                    ->get()
                    ->last();
        if ( $result != null ) {
            $result->QRcode = 'NumFac: ' . $result->consecutivoDocumento . '
            FecFac: ' . date('Y-m-d', strtotime($result->fechaAceptacionDIAN)) . '
            HorFac: ' . date('H:i:s', strtotime($result->fechaAceptacionDIAN)) . '
            NitFac: 700085371
            DocAdq: 800199436
            ValFac: 1500000.00
            ValIva: 285000.00
            ValOtroIm: 0.00
            ValTolFac: 1785000.00
            CUFE: ' . $result->cufe . '
            https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey=' . $result->cufe;
        }

        return $result;
    }

}