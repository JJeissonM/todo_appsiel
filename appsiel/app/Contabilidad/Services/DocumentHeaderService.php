<?php 

namespace App\Contabilidad\Services;

use App\Sistema\ValueObjects\TransactionPrimaryKeyVO;

use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;

use Illuminate\Support\Facades\Auth;

class DocumentHeaderService
{
    public function cancel_document( ContabDocEncabezado $document_header )
    {
        // Se borra el movimiento
        $obj_accou_movin_serv = new AccountingMovingService();
        $obj_accou_movin_serv->delete_move( new TransactionPrimaryKeyVO( $document_header->core_empresa_id, $document_header->core_tipo_transaccion_id, $document_header->core_tipo_doc_app_id, $document_header->consecutivo ) );

        // Se marcan como anulados los registros del documento
        ContabDocRegistro::where( 'contab_doc_encabezado_id', $document_header->id )->update( [ 'estado' => 'Anulado' ] );

        // Se marca como anulado el documento
        $document_header->update( [ 'estado' => 'Anulado', 'modificado_por' => Auth::user()->email ] );
        
    }
}