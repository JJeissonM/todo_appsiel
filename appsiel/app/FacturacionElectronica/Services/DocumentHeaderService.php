<?php 

namespace App\FacturacionElectronica\Services;

use App\Contabilidad\ContabMovimiento;
use App\Core\EncabezadoDocumentoTransaccion;
use App\Core\TipoDocApp;
use App\CxC\CxcAbono;
use App\CxC\CxcMovimiento;

use App\Http\Controllers\Ventas\VentaController;

use App\Inventarios\RemisionVentas;
use App\Tesoreria\RegistrosMediosPago;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\Cliente;
use App\Ventas\Services\TreasuryServices;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\VentasPos\FacturaPos;
use App\VentasPos\Movimiento;
use Illuminate\Http\Request;
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
        $new_consecutivo = TipoDocApp::get_consecutivo_actual($original_document_header->core_empresa_id, $fe_document_type_id_default) + 1;

        // Se incementa el consecutivo para ese tipo de documento y la empresa
        TipoDocApp::aumentar_consecutivo($original_document_header->core_empresa_id, $fe_document_type_id_default);

        // Cambiar Tipo de Transacción, tipo de documento y consecutivo Para las tablas de movimientos relacionadas
        $contab_movim = ContabMovimiento::where( $array_wheres )->get();
        foreach ($contab_movim as $line_movin) {
            $line_movin->core_tipo_transaccion_id = $fe_transaction_type_id_default;
            $line_movin->core_tipo_doc_app_id = $fe_document_type_id_default;
            $line_movin->consecutivo = $new_consecutivo;
            $line_movin->modificado_por = $modificado_por;
            $line_movin->save();
        }
        
        $cxc_movim = CxcMovimiento::where( $array_wheres )->get()->first();
        if ($cxc_movim != null) {
            $cxc_movim->core_tipo_transaccion_id = $fe_transaction_type_id_default;
            $cxc_movim->core_tipo_doc_app_id = $fe_document_type_id_default;
            $cxc_movim->consecutivo = $new_consecutivo;
            $cxc_movim->modificado_por = $modificado_por;
            $cxc_movim->save();
        }

        // Cambiar abonos de CxC
        $array_wheres2 = [
            'core_empresa_id' => $original_document_header->core_empresa_id,
            'doc_cxc_transacc_id' => $original_document_header->core_tipo_transaccion_id,
            'doc_cxc_tipo_doc_id' => $original_document_header->core_tipo_doc_app_id,
            'doc_cxc_consecutivo' => $original_document_header->consecutivo
        ];
        $cxc_abono = CxcAbono::where( $array_wheres2 )->get();
        foreach ($cxc_abono as $cxc_line_abono) {
            $cxc_line_abono->doc_cxc_transacc_id = $fe_transaction_type_id_default;
            $cxc_line_abono->doc_cxc_tipo_doc_id = $fe_document_type_id_default;
            $cxc_line_abono->doc_cxc_consecutivo = $new_consecutivo;
            $cxc_line_abono->modificado_por = $modificado_por;
            $cxc_line_abono->save();
        }
        
        // Movimiento de Tesoreria
        $teso_movim = TesoMovimiento::where( $array_wheres )->get();
        foreach ($teso_movim as $teso_line_movin) {
            $teso_line_movin->core_tipo_transaccion_id = $fe_transaction_type_id_default;
            $teso_line_movin->core_tipo_doc_app_id = $fe_document_type_id_default;
            $teso_line_movin->consecutivo = $new_consecutivo;
            $teso_line_movin->modificado_por = $modificado_por;
            $teso_line_movin->save();
        }

        // Tablas POS
        $original_document_header->core_tipo_transaccion_id = $fe_transaction_type_id_default;
        $original_document_header->core_tipo_doc_app_id = $fe_document_type_id_default;
        $original_document_header->consecutivo = $new_consecutivo;
        $original_document_header->modificado_por = $modificado_por;
        $original_document_header->estado = 'Contabilizado - Sin enviar';
        $original_document_header->save();
        
        $pos_movim = Movimiento::where( $array_wheres )->get();
        foreach ($pos_movim as $line_pos_movim) {
            $line_pos_movim->core_tipo_transaccion_id = $fe_transaction_type_id_default;
            $line_pos_movim->core_tipo_doc_app_id = $fe_document_type_id_default;
            $line_pos_movim->consecutivo = $new_consecutivo;
            $line_pos_movim->modificado_por = $modificado_por;
            $line_pos_movim->save();
        }

        // Crear encabezado y lineas de registros en en Vtas estandar
        $data = $original_document_header->toArray();
        
        unset($data['id']);
        $data['core_tipo_transaccion_id'] = $fe_transaction_type_id_default;
        $data['core_tipo_doc_app_id'] = $fe_document_type_id_default;
        $data['consecutivo'] = $new_consecutivo;
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
        $vtas_movim = VtasMovimiento::where( $array_wheres )->get();
        foreach ($vtas_movim as $line_vtas_movim) {
            $line_vtas_movim->core_tipo_transaccion_id = $fe_transaction_type_id_default;
            $line_vtas_movim->core_tipo_doc_app_id = $fe_document_type_id_default;
            $line_vtas_movim->consecutivo = $new_consecutivo;
            $line_vtas_movim->modificado_por = $modificado_por;
            $line_vtas_movim->save();
            //$line_vtas_movim->update( $new_data );
        }

        return (object)[
            'status'=>'flash_message',
            'message'=>'El documento ' . $original_document_label  . ' fue convertido en Factura electrónica exitosamente.',
            'new_document_header_id'=> $vtas_document_header->id
        ];
    }

    public function store_invoice( Request $request, $remision_doc_encabezado_id )
    {
        $lineas_registros = json_decode( $request->lineas_registros );

        // Crear documento de Ventas
        $request['remision_doc_encabezado_id'] = $remision_doc_encabezado_id;
        $request['estado'] = 'Contabilizado - Sin enviar';

        if ( !isset($request['creado_por']) ) {
            $request['creado_por'] = Auth::user()->email;
        }

        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );

        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

        // 3ra. Crear Registro del documento de ventas
        $request['creado_por'] = Auth::user()->email;
        $request['registros_medio_pago'] = (new RegistrosMediosPago())->get_datos_ids( $request->lineas_registros_medios_recaudo, $lineas_registros, 0, 'ventas' );
        VentaController::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        return $doc_encabezado;
    }

    public function validar_datos_tercero($tercero)
    {
        $status = 'success';
        $message = '';

        /*
        if ( $tercero->direccion1 == '' || strlen( $tercero->direccion1 ) < 2 )
        {
            $status = 'error';
            $message .= ' - Revisar dirección';
        }
        */

        if ( $tercero->email == '' || gettype( filter_var($tercero->email, FILTER_VALIDATE_EMAIL) ) != 'string' )
        {
            $status = 'error';
            $message .= ' - Revisar email - ';
        }

        /*
        if ( $tercero->telefono1 == '' || !is_numeric( $tercero->telefono1 ) )
        {
            $status = 'error';
            $message .= ' - Revisar teléfono - ';
        }

        if ( $tercero->tipo == 'Persona natural' )
        {
            if ( $tercero->nombre1 == '' || strlen( $tercero->nombre1 ) < 2 )
            {
                $status = 'error';
                $message .= ' - Revisar nombre completo. No tiene asignado el primer nombre. Debe tener mas de 2 caracteres.';
            }
        }
        */

        return (object)[
            'status' => $status,
            'message' => $message
        ];
    }

    public function get_cliente( $vtas_doc_encabezado )
    {
        $cliente = $vtas_doc_encabezado->cliente;

        if ( (int)config('facturacion_electronica.enviar_facturas_clientes_internos') && $cliente->tercero->tipo == 'Interno' ) {
            $cliente_consumidor_final = Cliente::find( (int)config('facturacion_electronica.cliente_consumidor_final_id') );
            if ( $cliente_consumidor_final != null ) {
               $cliente = $cliente_consumidor_final;
            }
        }

        return $cliente;
    }
        
}