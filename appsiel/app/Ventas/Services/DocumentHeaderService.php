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
use Illuminate\Support\Facades\Input;

class DocumentHeaderService
{
    /*
        Proceso de eliminar FACTURA DE VENTAS
        Se eliminan los registros de:
            - cxc_documentos_pendientes (se debe verificar que no tenga un abono, sino se debe eliminar primero el abono) y su movimiento en contab_movimientos
            - inv_movimientos de la REMISIÓN y su contabilidad. Además se actualiza el estado a Anulado en inv_doc_registros e inv_doc_encabezados
            - vtas_movimientos y su contabilidad. Además se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public function cancel_document_by_id( int $document_header_id, bool $cancel_deliveries_notes )
    {
        $document_header = VtasDocEncabezado::find( $document_header_id );

        $array_wheres = ['core_empresa_id'=>$document_header->core_empresa_id,
            'core_tipo_transaccion_id' => $document_header->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $document_header->core_tipo_doc_app_id,
            'consecutivo' => $document_header->consecutivo];

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $abonos = CxcAbono::where('doc_cxc_transacc_id',$document_header->core_tipo_transaccion_id)
                            ->where('doc_cxc_tipo_doc_id',$document_header->core_tipo_doc_app_id)
                            ->where('doc_cxc_consecutivo',$document_header->consecutivo)
                            ->get();

        if (!empty($abonos->toArray())) {
            $lista_abonos = '';
            foreach ($abonos as $abono) {
                $lista_abonos .= ' *** ' . $abono->payment_document_header()->get_label_documento();
            }
            return (object)[
                'status'=>'mensaje_error',
                'message'=>'Factura ' . $document_header->get_label_documento()  . ' NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería): ' . $lista_abonos
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
    
    public function actions_buttos_to_show_view( $doc_encabezado, $docs_relacionados )
    {
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion');

        $actions = [];

        switch ($doc_encabezado-> core_tipo_transaccion_id) {
            case '23':
                if( $doc_encabezado->estado != 'Anulado' && $doc_encabezado->estado != 'Pendiente' )
                {
                    if( !$docs_relacionados[1] )
                    {

                        $actions[] = (object)[
                            'tag_html' => 'a',
                            'target' => null,
                            'id' => null,
                            'url' => url( 'ventas/' . $doc_encabezado->id . '/edit' . $variables_url ),
                            'title' => 'Modificar',
                            'color_bootstrap' => null,
                            'faicon' => 'edit',
                            'size' => null,
                        ];
                        
                        $actions[] = (object)[
                            'tag_html' => 'a',
                            'target' => null,
                            'id' => null,
                            'url' => url('ventas_notas_credito/create?factura_id=' . $doc_encabezado->id . '&id=' . Input::get('id') . '&id_modelo=167&id_transaccion=38'),
                            'title' => 'Nota crédito',
                            'color_bootstrap' => null,
                            'faicon' => 'file-text',
                            'size' => null,
                        ];
                    }
                    
                    $actions[] = (object)[
                        'tag_html' => 'a',
                        'target' => '_blank',
                        'id' => null,
                        'url' => url('tesoreria/recaudos_cxc/create?id=' . Input::get('id') . '&id_modelo=153&id_transaccion=32'),
                        'title' => 'Hacer abono',
                        'color_bootstrap' => null,
                        'faicon' => 'money',
                        'size' => null,
                    ];
                    
                    $actions[] = (object)[
                        'tag_html' => 'button',
                        'target' => null,
                        'id' => 'btn_anular',
                        'url' => url('tesoreria/recaudos_cxc/create?id=' . Input::get('id') . '&id_modelo=153&id_transaccion=32'),
                        'title' => 'Anular',
                        'color_bootstrap' => null,
                        'faicon' => 'close',
                        'size' => null,
                    ];
                    
                    if ( Auth::user()->hasPermissionTo('vtas_recontabilizar') ) {
                        $actions[] = (object)[
                            'tag_html' => 'a',
                            'target' => null,
                            'id' => null,
                            'url' => url( 'ventas_recontabilizar/' . $doc_encabezado->id . $variables_url ),
                            'title' => 'Recontabilizar',
                            'color_bootstrap' => null,
                            'faicon' => 'cog',
                            'size' => null,
                        ];
                    }
                }
                break;
            
            default:
                # code...
                break;
        }        

        return $actions;
    }
}