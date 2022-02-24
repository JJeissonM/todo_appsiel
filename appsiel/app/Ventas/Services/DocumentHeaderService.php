<?php 

namespace App\Ventas\Services;

use App\Contabilidad\ContabMovimiento;
use App\CxC\CxcAbono;
use App\CxC\CxcMovimiento;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Inventarios\InvDocEncabezado;
use App\Matriculas\FacturaAuxEstudiante;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use Illuminate\Support\Facades\Auth;

class DocumentHeaderService
{
    /*
        Proceso de eliminar FACTURA DE VENTAS
        Se eliminan los registros de:
            - cxc_documentos_pendientes (se debe verificar que no tenga un abono, sino se debe eliminar primero el abono) y su movimiento en contab_movimientos
            - inv_movimientos de la REMISIÓN y su contabilidad. Además se actualiza el estado a Anulado en inv_doc_registros e inv_doc_encabezados
            - vtas_movimientos y su contabilidad. Además se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public function cancel_document_by_id( $document_header_id, $cancel_deliveries_notes )
    {
        $document_header = VtasDocEncabezado::find( $document_header_id );

        $array_wheres = ['core_empresa_id'=>$document_header->core_empresa_id,
            'core_tipo_transaccion_id' => $document_header->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $document_header->core_tipo_doc_app_id,
            'consecutivo' => $document_header->consecutivo];

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $cantidad = CxcAbono::where('doc_cxc_transacc_id',$document_header->core_tipo_transaccion_id)
                            ->where('doc_cxc_tipo_doc_id',$document_header->core_tipo_doc_app_id)
                            ->where('doc_cxc_consecutivo',$document_header->consecutivo)
                            ->count();
        
        if ($cantidad != 0) {
            return (object)[
                'status'=>'mensaje_error',
                'message'=>'Factura ' . $document_header->get_label_documento()  . ' NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería).'
            ];
        }

        $modificado_por = Auth::user()->email;

        // 1ro. Anular documento asociado de inventarios
        // Obtener las remisiones relacionadas con la factura y anularlas o dejarlas en estado Pendiente
        $ids_documentos_relacionados = explode( ',', $document_header->remision_doc_encabezado_id );
        $cant_registros = count($ids_documentos_relacionados);
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $remision = InvDocEncabezado::find( $ids_documentos_relacionados[$i] );
            if ( !is_null($remision) )
            {
                if ( $cancel_deliveries_notes ) // cancel_deliveries_notes es tipo boolean
                {
                    InventarioController::anular_documento_inventarios( $remision->id );
                }else{
                    $remision->update(['estado'=>'Pendiente', 'modificado_por' => $modificado_por]);
                }    
            }
        }

        // 2do. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el documento del movimimeto de cuentas por cobrar y de tesorería
        CxcMovimiento::where($array_wheres)->delete();
        TesoMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el movimiento de ventas
        VtasMovimiento::where($array_wheres)->delete();
        // 5to. Se marcan como anulados los registros del documento
        VtasDocRegistro::where( 'vtas_doc_encabezado_id', $document_header->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $document_header->update(['estado'=>'Anulado', 'remision_doc_encabezado_id' => '', 'modificado_por' => $modificado_por]);

        // 7mo. Si es una factura de Estudiante
        $factura_estudiante = FacturaAuxEstudiante::where('vtas_doc_encabezado_id',$document_header->id)->get()->first();
        if (!is_null($factura_estudiante))
        {
            $factura_estudiante->delete();
        }

        return (object)[
            'status'=>'flash_message',
            'message'=>'Factura de ventas ' . $document_header->get_label_documento()  . ' ANULADA correctamente.'
        ];
    }
        
}