<?php 

namespace App\CxP\Services;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Core\EncabezadoDocumentoTransaccion;
use App\Sistema\ValueObjects\TransactionPrimaryKeyVO;

use App\Contabilidad\Services\DocumentHeaderService;
use App\Contabilidad\Services\DocumentLinesService;
use App\Contabilidad\Services\AccountingMovingService;
use App\Contabilidad\Services\AccountingMovement;
use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabMovimiento;

use App\CxP\CxpMovimiento;

class AccountingServices
{
    const CONTAB_DOC_HEADER_MODEL_ID = 47;

    public function create_accounting_note_doc( $tabla_documentos_a_cancelar, $fecha, $core_tercero_id )
    {
        $array_tabla = json_decode( $tabla_documentos_a_cancelar, true);
        $fila_nota_contable = $array_tabla[ count($array_tabla) - 2 ]; // LA PENULTIMA LINEA

        if ( (int)$fila_nota_contable['movimiento_id'] != -1 )
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
        return CxpMovimiento::where('core_empresa_id', $document_head->core_empresa_id)
                            ->where('core_tipo_transaccion_id', $document_head->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id', $document_head->core_tipo_doc_app_id)
                            ->where('consecutivo', $document_head->consecutivo)
                            ->where('core_tercero_id', $document_head->core_tercero_id)
                            ->get()->first()->id;
    }

    function built_document_lines( $parametros_config, $fila_nota_contable, $fecha, $core_tercero_id )
    {
        $valor_aplicar = (float)$fila_nota_contable['valor_aplicar'];

        if( $valor_aplicar < 0 )
        {
            // Se le paga menos al proveedor (o no se le paga)
            $tipo_causacion_cr = 'causacion';
            $cta_cr = (int)$parametros_config['cta_ingresos_default'] . ' - Cuenta INGRESOS';
            
            $tipo_causacion_db = 'crear_anticipo_cxp';
            $cta_db = (int)$parametros_config['cta_anticipo_proveedores_default'] . ' - Cuenta ANTICIPO/SALDO A FAVOR PROVEEDORES';
        }else{
            // Se le anticipó de más al proveedor (o la factura dio menos)
            $tipo_causacion_cr = 'crear_cxp';
            $cta_cr = (int)$parametros_config['cta_por_pagar_default'] . ' - Cuenta Por Pagar';
            
            $tipo_causacion_db = 'causacion';
            $cta_db = (int)$parametros_config['cta_gastos_default'] . ' - Cuenta GASTO';
        }

        $json_db = [ 
                        "fecha_vencimiento" => $fecha,
                        "documento_soporte_tercero" => "",
                        "tipo_transaccion" => $tipo_causacion_db,
                        "Cuenta" => $cta_db,
                        "Tercero" => (int)$core_tercero_id . '- Tercero del movimiento',
                        "Detalle" => "Creado desde CRUCE DE CxP",
                        "debito" => "$  " . abs($valor_aplicar),
                        "credito" => "$  0" 
                    ];

        $json_cr = [ 
                        "fecha_vencimiento" => $fecha,
                        "documento_soporte_tercero" => "",
                        "tipo_transaccion" => $tipo_causacion_cr,
                        "Cuenta" => $cta_cr,
                        "Tercero" => (int)$core_tercero_id . '- Tercero del movimiento',
                        "Detalle" => "Creado desde CRUCE DE CxP",
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
        $request["descripcion"] = "Generada desde cruce de CxP.";
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
    
    public function create_accounting_movement($accounts_receivables_payment_record)
    {
        $obj_acco_move = new AccountingMovement();
        $model = new ContabMovimiento();
        
        $data = $this->set_data($accounts_receivables_payment_record);
        
        // Accounting Payment
        $data['contab_cuenta_id'] = $this->get_main_account($accounts_receivables_payment_record);
        $obj_acco_move->store($model,$data);

        // Accounting Advance Payments (Anticipos en Cruces)
        if ($accounts_receivables_payment_record->doc_cruce_transacc_id == 0) {
            return 0;
        }
        $obj_acco_move->store($model,$this->set_data_contra($accounts_receivables_payment_record,$data));
    }

    public function set_data($movement)
    {
        $data = $movement->toArray();
        
        if ($movement->doc_cruce_transacc_id != 0) {
            $data['core_tipo_transaccion_id'] = $data['doc_cruce_transacc_id'];
            $data['core_tipo_doc_app_id'] = $data['doc_cruce_tipo_doc_id'];
            $data['consecutivo'] = $data['doc_cruce_consecutivo'];
        }

        $data['id_registro_doc_tipo_transaccion'] = $movement->id;
        $data['valor_operacion'] = 0;

        // En un recaudo normal, el abono es credito
        $valor_debito = 0;
        $valor_credito = $movement->abono * -1;
        
        $data['valor_debito'] = $valor_debito;
        $data['valor_credito'] = $valor_credito;
        $data['valor_saldo'] = $valor_debito + $valor_credito;
        $data['detalle_operacion'] = 'Abono factura de cliente';
        $data['tipo_transaccion'] = 'pago_cxp'; // Nuevo en contabilidad
        $data['inv_producto_id'] = 0;
        $data['cantidad'] = 0;
        $data['tasa_impuesto'] = 0;
        $data['base_impuesto'] = 0;
        $data['valor_impuesto'] = 0;
        $data['fecha_vencimiento'] = '0000-00-00';
        $data['inv_bodega_id'] = 0;

        $data['teso_caja_id'] = 0;
        $data['teso_cuenta_bancaria_id'] = 0;

        $data['codigo_referencia_tercero'] = 0;
        $data['documento_soporte'] = 0;
        $data['estado'] = 'Activo';

        return $data;
    }

    public function get_main_account($movement)
    {
        // Registro Debito de la contabilizacion del documento de cartera.
        $cta_x_cobrar_id = ContabMovimiento::where('core_tipo_transaccion_id',$movement->doc_cxp_transacc_id)
                                            ->where('core_tipo_doc_app_id',$movement->doc_cxp_tipo_doc_id)
                                            ->where('consecutivo',$movement->doc_cxp_consecutivo)
                                            ->where('core_tercero_id',$movement->core_tercero_id)
                                            ->where('valor_credito',0)
                                            ->value('contab_cuenta_id');

        if( is_null( $cta_x_cobrar_id ) )
        {
            $cta_x_cobrar_id = config('configuracion.cta_cartera_default');
        }

        return $cta_x_cobrar_id;
    }

    public function set_data_contra($movement,$data)
    {
        $account_advance_id = ContabMovimiento::where('core_tipo_transaccion_id',$movement->core_tipo_transaccion_id)
                                            ->where('core_tipo_doc_app_id',$movement->core_tipo_doc_app_id)
                                            ->where('consecutivo',$movement->consecutivo)
                                            ->where('core_tercero_id',$movement->core_tercero_id)
                                            ->where('valor_credito',0)
                                            ->value('contab_cuenta_id');

        if( is_null( $account_advance_id ) )
        {
            $account_advance_id = config('configuracion.cta_anticipo_clientes_default');
        }

        $data['contab_cuenta_id'] = $account_advance_id;
        // Se invierten los valores
        $valor_debito = $data['valor_debito'];
        $valor_credito = $data['valor_credito'];
        $data['valor_debito'] = $valor_credito * -1;
        $data['valor_credito'] = $valor_debito * -1;
        $data['valor_saldo'] = $data['valor_saldo'] * -1;

        return $data;
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
    public function anular_nota_contable_ajuste( $linea_abono_cxp )
    {
        $contab_doc_header = ContabDocEncabezado::where('core_empresa_id',$linea_abono_cxp->core_empresa_id)
                            ->where('core_tipo_transaccion_id', $linea_abono_cxp->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id', $linea_abono_cxp->core_tipo_doc_app_id)
                            ->where('consecutivo', $linea_abono_cxp->consecutivo)
                            ->where('core_tercero_id', $linea_abono_cxp->core_tercero_id)
                            ->get()
                            ->first();

        if ($contab_doc_header == null) {
            return 0;
        }

        // Anular movimiento de CxP de la nota
        $mov_cxp = CxpMovimiento::where('core_empresa_id',$linea_abono_cxp->core_empresa_id)
                                ->where('core_tipo_transaccion_id', $linea_abono_cxp->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $linea_abono_cxp->core_tipo_doc_app_id)
                                ->where('consecutivo', $linea_abono_cxp->consecutivo)
                                ->where('core_tercero_id', $linea_abono_cxp->core_tercero_id)
                                ->get()->first();

        if( !is_null($mov_cxp) )
        {
            $mov_cxp->delete();
        }

        // Determinar si hay alguna nota de contabilidad con alguno de los documentos involucrados en el cruce: cartera o anticipo
        
        // El valor total de la nota de contabilidad es el mismo del valor del abono

        // Validar Si la nota se creo como un anticipo
        $array_wheres = [
                            [ 'core_tipo_transaccion_id','=',$linea_abono_cxp->core_tipo_transaccion_id],
                            [ 'core_tipo_doc_app_id','=',$linea_abono_cxp->core_tipo_doc_app_id],
                            [ 'consecutivo','=',$linea_abono_cxp->consecutivo],
                            [ 'core_tercero_id','=',$linea_abono_cxp->core_tercero_id]
                        ];
        $doc_encabezado = ContabDocEncabezado::where( $array_wheres )->get()->first();
      
        if( !is_null($doc_encabezado) )
        {
            $obj_docu_head_serv = new DocumentHeaderService();
            $obj_docu_head_serv->cancel_document( $doc_encabezado  );
        }

        // Validar si la nota se creo como un documento de Cartera
        $array_wheres = [
                        [ 'core_tipo_transaccion_id','=',$linea_abono_cxp->doc_cxp_transacc_id],
                        [ 'core_tipo_doc_app_id','=',$linea_abono_cxp->doc_cxp_tipo_doc_id],
                        [ 'consecutivo','=',$linea_abono_cxp->doc_cxp_consecutivo],
                        [ 'core_tercero_id','=',$linea_abono_cxp->core_tercero_id]
                    ];
        $doc_encabezado = ContabDocEncabezado::where( $array_wheres )->get()->first();

        if( !is_null($doc_encabezado) )
        {
            $obj_docu_head_serv = new DocumentHeaderService();
            $obj_docu_head_serv->cancel_document( $doc_encabezado  );
        }

    }
}