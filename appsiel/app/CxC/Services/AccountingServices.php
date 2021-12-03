<?php 

namespace App\CxC\Services;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Core\EncabezadoDocumentoTransaccion;
use App\Sistema\ValueObjects\TransactionPrimaryKeyVO;

use App\Contabilidad\Services\DocumentHeaderService;
use App\Contabilidad\Services\DocumentLinesService;
use App\Contabilidad\Services\AccountingMovingService;
use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabMovimiento;

use App\CxC\CxcMovimiento;

class AccountingServices
{
    const CONTAB_DOC_HEADER_MODEL_ID = 47;

    public function create_accounting_note_doc( $tabla_documentos_a_cancelar, $fecha, $core_tercero_id )
    {
        $array_tabla = json_decode( $tabla_documentos_a_cancelar, true);
        $fila_nota_contable = $array_tabla[ count($array_tabla) - 2 ]; // LA PENULTIMA LINEA

        if ( (int)$fila_nota_contable['cxc_movimiento_id'] != -1 )
        {
            return 0;
        }

        $valor_aplicar = (float)$fila_nota_contable['valor_aplicar'];

        $parametros_config = config('contabilidad');

        $document_lines = $this->built_document_lines( $parametros_config, $fila_nota_contable, $fecha, $core_tercero_id );

        $request = $this->built_ObjRequets( $parametros_config, $fecha, $core_tercero_id, abs($valor_aplicar) );

        $obj_document_header = new EncabezadoDocumentoTransaccion( self::CONTAB_DOC_HEADER_MODEL_ID );
        $document_head = $obj_document_header->crear_nuevo( $request->all() );

        $obj_document_lines = new DocumentLinesService();
        $obj_document_lines->store_lines_and_accounting( $document_head, $document_lines );

        // Devolver el ID del movimiento de cartera creado
        // Nota: Algo muy particular de este Service
        return CxcMovimiento::where('core_empresa_id', $document_head->core_empresa_id)
                            ->where('core_tipo_transaccion_id', $document_head->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id', $document_head->core_tipo_doc_app_id)
                            ->where('consecutivo', $document_head->consecutivo)
                            ->get()->first()->id;
    }

    function built_document_lines( $parametros_config, $fila_nota_contable, $fecha, $core_tercero_id )
    {
        $valor_aplicar = (float)$fila_nota_contable['valor_aplicar'];

        if( $valor_aplicar < 0 )
        {
            // El cliente paga menos
            $cta_db = (int)$parametros_config['cta_gastos_default'] . '- Cuenta GASTO';
            $cta_cr = (int)$parametros_config['cta_anticipo_clientes_default'] . '- Cuenta ANTICIPO CLIENTES';
            $tipo_causacion_db = 'causacion';
            $tipo_causacion_cr = 'crear_anticipo_cxc';
        }else{
            // El cliente paga más
            $cta_db = (int)$parametros_config['cta_cartera_default'] . '- Cuenta CARTERA';
            $cta_cr = (int)$parametros_config['cta_ingresos_default'] . '- Cuenta INGRESOS';
            $tipo_causacion_db = 'crear_cxc';
            $tipo_causacion_cr = 'causacion';
        }

        $json_db = [ 
                        "fecha_vencimiento" => $fecha,
                        "documento_soporte_tercero" => "",
                        "tipo_transaccion" => $tipo_causacion_db,
                        "Cuenta" => $cta_db,
                        "Tercero" => (int)$core_tercero_id . '- Tercero del movimiento',
                        "Detalle" => "Creado desde CxC",
                        "debito" => "$  " . abs($valor_aplicar),
                        "credito" => "$  0" 
                    ];

        $json_cr = [ 
                        "fecha_vencimiento" => $fecha,
                        "documento_soporte_tercero" => "",
                        "tipo_transaccion" => $tipo_causacion_cr,
                        "Cuenta" => $cta_cr,
                        "Tercero" => (int)$core_tercero_id . '- Tercero del movimiento',
                        "Detalle" => "Creado desde CxC",
                        "debito" => "$  0",
                        "credito" => "$  " . abs($valor_aplicar) 
                    ];
        
        return json_encode( [ $json_db, $json_cr ] );
    }

    public function built_ObjRequets( $parametros_config, $fecha, $core_tercero_id, $valor_total )
    {
        $request = new Request;
        $user = Auth::user();
        $request["core_empresa_id"] = $user->empresa_id;
        $request["core_tipo_transaccion_id"] = (int)$parametros_config['transaction_type_id_default'];
        $request["core_tipo_doc_app_id"] = (int)$parametros_config['document_type_id_default'];
        $request["consecutivo"] = "";
        $request["fecha"] = $fecha;
        $request["core_tercero_id"] = $core_tercero_id;
        $request["descripcion"] = "Generada desde cruce de CxC.";
        $request["documento_soporte"] = "";
        $request["valor_total"] = $valor_total;
        $request["creado_por"] = $user->email;
        $request["modificado_por"] = "0";
        $request["estado"] = "Activo";

        return $request;
    }

    public function contabilizar_registro( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cta_bancaria_id = 0 )
    {
        ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $teso_caja_id]  + 
                            [ 'teso_cta_bancaria_id' => $teso_cta_bancaria_id] 
                        );
    }

    public function delete_accounting_move( $core_empresa_id, $core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo )
    {
        $obj_accou_movin_serv = new AccountingMovingService();
        $obj_accou_movin_serv->delete_move( new TransactionPrimaryKeyVO( $core_empresa_id, $core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo ) );
    }

    /**
     * Determinar si el Doc. Cruce generó alguna nota de contabilidad y anularla
     * 
     */
    public function anular_nota_contable_ajuste( $linea_abono_cxc )
    {
        // Anular movimiento de CxC de la nota
        $mov_cxc = CxcMovimiento::where('core_empresa_id',$linea_abono_cxc->core_empresa_id)
                                ->where('core_tipo_transaccion_id', $linea_abono_cxc->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $linea_abono_cxc->core_tipo_doc_app_id)
                                ->where('consecutivo', $linea_abono_cxc->consecutivo)
                                ->get()->first();
        if( !is_null($mov_cxc) )
        {
            $mov_cxc->delete();
        }

        // Determinar si hay alguna nota de contabilidad con alguno de los documentos involucrados en el cruce: cartera o anticipo
        
        // El valor total de la nota de contabilidad es el mismo del valor del abono

        // Validar Si la nota se creo como un anticipo
        $array_wheres = [
                            [ 'core_tipo_transaccion_id','=',$linea_abono_cxc->core_tipo_transaccion_id],
                            [ 'core_tipo_doc_app_id','=',$linea_abono_cxc->core_tipo_doc_app_id],
                            [ 'consecutivo','=',$linea_abono_cxc->consecutivo]
                        ];
        $doc_encabezado = ContabDocEncabezado::where( $array_wheres )->get()->first();
      
        if( !is_null($doc_encabezado) )
        {
            $obj_docu_head_serv = new DocumentHeaderService();
            $obj_docu_head_serv->cancel_document( $doc_encabezado  );
            return 1;
        }

        // Validar si la nota se creo como un documento de Cartera
        $array_wheres = [
                        [ 'core_tipo_transaccion_id','=',$linea_abono_cxc->doc_cxc_transacc_id],
                        [ 'core_tipo_doc_app_id','=',$linea_abono_cxc->doc_cxc_tipo_doc_id],
                        [ 'consecutivo','=',$linea_abono_cxc->doc_cxc_consecutivo]
                    ];
        $doc_encabezado = ContabDocEncabezado::where( $array_wheres )->get()->first();

        if( !is_null($doc_encabezado) )
        {
            $obj_docu_head_serv = new DocumentHeaderService();
            $obj_docu_head_serv->cancel_document( $doc_encabezado  );
        }

    }
}