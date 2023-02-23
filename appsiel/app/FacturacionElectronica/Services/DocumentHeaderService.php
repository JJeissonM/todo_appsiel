<?php 

namespace App\FacturacionElectronica\Services;

use App\Contabilidad\ContabMovimiento;
use App\Core\TipoDocApp;
use App\CxC\CxcAbono;
use App\CxC\CxcMovimiento;

use App\Tesoreria\TesoMovimiento;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\VentasPos\FacturaPos;
use Illuminate\Support\Facades\Auth;

class DocumentHeaderService
{
    public function convert_to_electronic_invoice( $document_header_id , $parent_transaction_id)
    {
        switch ($parent_transaction_id) {
            case '23': // Factura de ventas estándar
                $document_header = VtasDocEncabezado::find( $document_header_id );
                break;
            
            case '47': // Factura POS
                $document_header = FacturaPos::find( $document_header_id );
                break;
                
            default:
                return (object)[
                    'status'=>'mensaje_error',
                    'message'=>'El tipo de documento actual no puede ser convertido en Factura Electrónica.'
                ];
                break;
        }

        $document_label_old = $document_header->get_label_documento();

        $array_wheres = ['core_empresa_id'=>$document_header->core_empresa_id,
            'core_tipo_transaccion_id' => $document_header->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $document_header->core_tipo_doc_app_id,
            'consecutivo' => $document_header->consecutivo];

        // Verificar si la factura tiene abonos, si tiene no se puede convertir
        $cantidad = CxcAbono::where('doc_cxc_transacc_id',$document_header->core_tipo_transaccion_id)
                            ->where('doc_cxc_tipo_doc_id',$document_header->core_tipo_doc_app_id)
                            ->where('doc_cxc_consecutivo',$document_header->consecutivo)
                            ->count();
        
        if ($cantidad != 0) {
            return (object)[
                'status'=>'mensaje_error',
                'message'=>'Factura ' . $document_header->get_label_documento()  . ' NO puede ser convertida. Se le han hecho Recaudos de CXC (Tesorería).'
            ];
        }

        $modificado_por = Auth::user()->email;

        // Calcular consecutivo para nueva factura electronica
        // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
        $consecutivo = TipoDocApp::get_consecutivo_actual($document_header->core_empresa_id, config('facturacion_electronica.document_type_id_default')) + 1;

        // Se incementa el consecutivo para ese tipo de documento y la empresa
        TipoDocApp::aumentar_consecutivo($document_header->core_empresa_id, config('facturacion_electronica.document_type_id_default'));

        // Cambiar Tipo de Transacción, tipo de documento y consecutivo Para las tablas de movimientos relacionadas
        ContabMovimiento::where($array_wheres)->update([
            'core_tipo_transaccion_id' => config('facturacion_electronica.transaction_type_id_default'),
            'core_tipo_doc_app_id' => config('facturacion_electronica.document_type_id_default'),
            'consecutivo' => $consecutivo,
            'modificado_por' => $modificado_por
        ]);

        CxcMovimiento::where($array_wheres)->update([
            'core_tipo_transaccion_id' => config('facturacion_electronica.transaction_type_id_default'),
            'core_tipo_doc_app_id' => config('facturacion_electronica.document_type_id_default'),
            'consecutivo' => $consecutivo,
            'modificado_por' => $modificado_por
        ]);

        TesoMovimiento::where($array_wheres)->update([
            'core_tipo_transaccion_id' => config('facturacion_electronica.transaction_type_id_default'),
            'core_tipo_doc_app_id' => config('facturacion_electronica.document_type_id_default'),
            'consecutivo' => $consecutivo,
            'modificado_por' => $modificado_por
        ]);

        $document_header->update([
            'core_tipo_transaccion_id' => config('facturacion_electronica.transaction_type_id_default'),
            'core_tipo_doc_app_id' => config('facturacion_electronica.document_type_id_default'),
            'consecutivo' => $consecutivo,
            'modificado_por' => $modificado_por,
            'estado' => 'Contabilizado - Sin enviar'
        ]);

        switch ($parent_transaction_id) {
            case '23': // Factura de ventas estándar
                $vtas_document_header = $document_header;
                break;
            
            case '47': // Factura POS
                // Crear encabezado y lineas de registros en en Vtas estandar
                $data = $document_header->toArray();
                $data['core_tipo_transaccion_id'] = config('facturacion_electronica.transaction_type_id_default');
                $data['core_tipo_doc_app_id'] = config('facturacion_electronica.document_type_id_default');
                $data['consecutivo'] = $consecutivo;
                $data['estado'] = 'Contabilizado - Sin enviar';
                $vtas_document_header = VtasDocEncabezado::create($document_header->toArray());

                $lineas_registros = $document_header->lineas_registros;

                foreach ($lineas_registros as $linea) {
                    $line_data = $data + $linea->toArray();
                    $line_data['vtas_doc_encabezado_id'] = $vtas_document_header->id;
                    VtasDocRegistro::create($line_data);
                }

                break;
                
            default:
                return (object)[
                    'status'=>'mensaje_error',
                    'message'=>'El tipo de documento actual no puede ser convertido en Factura Electrónica.'
                ];
                break;
        }

        // Mover movimiento de ventas
        VtasMovimiento::where($array_wheres)->update([
            'core_tipo_transaccion_id' => config('facturacion_electronica.transaction_type_id_default'),
            'core_tipo_doc_app_id' => config('facturacion_electronica.document_type_id_default'),
            'consecutivo' => $consecutivo,
            'modificado_por' => $modificado_por
        ]);

        return (object)[
            'status'=>'flash_message',
            'message'=>'El documento ' . $document_label_old  . ' fue convertido en Factura electrónica exitosamente.',
            'new_document_header_id'=> $vtas_document_header->id
        ];
    }
        
}