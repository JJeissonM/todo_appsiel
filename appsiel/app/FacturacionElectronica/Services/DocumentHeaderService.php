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
use App\VentasPos\Movimiento;
use Illuminate\Support\Facades\Auth;

class DocumentHeaderService
{
    // Only For POS
    public function convert_to_electronic_invoice( $document_header_id )
    {
        $original_document_header = FacturaPos::find( $document_header_id );

        $original_document_label = $original_document_header->get_label_documento();

        $array_wheres = [
            'core_empresa_id' => $original_document_header->core_empresa_id,
            'core_tipo_transaccion_id' => $original_document_header->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $original_document_header->core_tipo_doc_app_id,
            'consecutivo' => $original_document_header->consecutivo
        ];

        // Verificar si la factura tiene abonos, si tiene no se puede convertir
        $cantidad = CxcAbono::where( $array_wheres )
                            ->count();
        
        if ($cantidad != 0) {
            return (object)[
                'status'=>'mensaje_error',
                'message'=>'Factura ' . $original_document_label  . ' NO puede ser convertida. Se le han hecho Recaudos de CXC (Tesorería).'
            ];
        }

        $modificado_por = Auth::user()->email;
        $fe_document_type_id_default = config('facturacion_electronica.document_type_id_default');
        $fe_transaction_type_id_default = config('facturacion_electronica.transaction_type_id_default');

        // Calcular consecutivo para nueva factura electronica
        // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
        $consecutivo = TipoDocApp::get_consecutivo_actual($original_document_header->core_empresa_id, $fe_document_type_id_default) + 1;

        // Se incementa el consecutivo para ese tipo de documento y la empresa
        TipoDocApp::aumentar_consecutivo($original_document_header->core_empresa_id, $fe_document_type_id_default);

        // Cambiar Tipo de Transacción, tipo de documento y consecutivo Para las tablas de movimientos relacionadas
        $new_data = [
            'core_tipo_transaccion_id' => $fe_transaction_type_id_default,
            'core_tipo_doc_app_id' => $fe_document_type_id_default,
            'consecutivo' => $consecutivo,
            'modificado_por' => $modificado_por
        ];

        $contab_movim = ContabMovimiento::where( $array_wheres )->get()->first();
        if ($contab_movim != null) {
            $contab_movim->update( $new_data );
        }
        
        $cxc_movim = CxcMovimiento::where( $array_wheres )->get()->first();
        if ($cxc_movim != null) {
            $cxc_movim->update( $new_data );
        }
        
        $teso_movim = TesoMovimiento::where( $array_wheres )->get()->first();
        if ($teso_movim != null) {
            $teso_movim->update( $new_data );
        }

        // Tablas POS
        $original_document_header->update( array_merge( $new_data, [ 'estado' => 'Contabilizado - Sin enviar'] ) );
        
        $pos_movim = Movimiento::where( $array_wheres )->get()->first();
        if ($pos_movim != null) {
            $pos_movim->update( $new_data );
        }

        // Crear encabezado y lineas de registros en en Vtas estandar
        $data = $original_document_header->toArray();
        unset($data['id']);
        $data['core_tipo_transaccion_id'] = $fe_transaction_type_id_default;
        $data['core_tipo_doc_app_id'] = $fe_document_type_id_default;
        $data['consecutivo'] = $consecutivo;
        $data['estado'] = 'Contabilizado - Sin enviar';
        $vtas_document_header = VtasDocEncabezado::create( $original_document_header->toArray() );

        $lineas_registros = $original_document_header->lineas_registros;

        foreach ($lineas_registros as $linea) {
            $line_data = $data + $linea->toArray();
            unset($line_data['id']);
            $line_data['vtas_doc_encabezado_id'] = $vtas_document_header->id;
            VtasDocRegistro::create($line_data);
        }

        // Mover movimiento de ventas
        $vtas_movim = VtasMovimiento::where( $array_wheres )->get()->first();
        if ($vtas_movim != null) {
            $vtas_movim->update( $new_data );
        }

        return (object)[
            'status'=>'flash_message',
            'message'=>'El documento ' . $original_document_label  . ' fue convertido en Factura electrónica exitosamente.',
            'new_document_header_id'=> $vtas_document_header->id
        ];
    }
        
}