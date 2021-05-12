<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use View;
use Lava;
use Input;
use NumerosEnLetras;
use Form;


use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

// Objetos 
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos

use App\Sistema\TipoTransaccion;
use App\Sistema\Modelo;
use App\Core\Tercero;

use App\Core\Empresa;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\ControlCheque;
use App\Tesoreria\TesoEntidadFinanciera;

use App\Tesoreria\RegistroDeEfectivo;
use App\Tesoreria\RegistroDeTransferenciaConsignacion;
use App\Tesoreria\RegistroDeTarjetaDebito;
use App\Tesoreria\RegistroDeTarjetaCredito;
use App\Tesoreria\RegistroDeCheque;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\Retencion;
use App\Contabilidad\RegistroRetencion;

use App\CxP\CxpMovimiento;
use App\CxP\DocumentosPendientes;
use App\CxP\CxpAbono;

class PagoCxpController extends TransaccionController
{
    protected $datos = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $id_transaccion = 33;// 33 = Pagos de CxP

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create');

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $motivos = [''];
        $medios_recaudo = TesoMedioRecaudo::opciones_campo_select();
        $cajas = TesoCaja::opciones_campo_select();
        $cuentas_bancarias = TesoCuentaBancaria::opciones_campo_select();
        $retenciones = Retencion::opciones_campo_select();

        $tipos_operaciones = [ '' => '', 'Pago proveedores' => 'Pago proveedores (CxP)', 'Anticipo proveedor' => 'Anticipo proveedor (CxP a favor)', 'Otros pagos' => 'Otros pagos', 'Pago anticipado' => 'Préstamo financiero (Crear CxC)'];

        $terceros = [''];

        $entidades_financieras = TesoEntidadFinanciera::opciones_campo_select();

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Crear nuevo' ]
            ];

        return view('tesoreria.pagos_cxp.create', compact( 'form_create','id_transaccion','motivos','miga_pan','medios_recaudo','cajas','cuentas_bancarias', 'terceros', 'entidades_financieras', 'retenciones', 'tipos_operaciones' ) );
    }

    /**
     * Este método almacena el Encabezado documento de Pago creado. Este tipo de documentos no maneja líneas de registros.
     * En lugar de líneas de registros, se llena la tabla cxp_documentos_abonos donde se realaciona cada documento de pago
     * con el (los) documento(s) de CxP  
     * // Este método es llamado desde ModeloController@store
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Crear encabezado desde TransaccionController
        $doc_encabezado = $this->crear_encabezado_documento( $request, $request->url_id_modelo);

        $total_abonos_cxc = $this->almacenar_registros_cxp( $request, $doc_encabezado );
        
        $retenciones = new RegistroRetencion();
        $retenciones->almacenar_nuevos_registros( $request->lineas_registros_retenciones, $doc_encabezado, $total_abonos_cxc, 'practicada' );

        $efectivo = new RegistroDeEfectivo();
        $efectivo->almacenar_registros( $request->lineas_registros_efectivo, $doc_encabezado );

        $transferencia_consignacion = new RegistroDeTransferenciaConsignacion();
        $transferencia_consignacion->almacenar_registros( $request->lineas_registros_transferencia_consignacion, $doc_encabezado );

        $tarjeta_debito = new RegistroDeTarjetaDebito();
        $tarjeta_debito->almacenar_registros( $request->lineas_registros_tarjeta_debito, $doc_encabezado );

        $tarjeta_credito = new RegistroDeTarjetaCredito();
        $tarjeta_credito->almacenar_registros( $request->lineas_registros_tarjeta_credito, $doc_encabezado );

        // $teso_medio_recaudo_id = 6; // Cheque propio
        $cheques = new RegistroDeCheque();
        $cheques->almacenar_registros( $request->lineas_registros_cheques, $doc_encabezado, 6, 'Emitido' );

        $doc_encabezado->actualizar_valor_total();

        // se llama la vista de PagoCxpController@show
        return redirect( 'tesoreria/pagos_cxp/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }

    public function almacenar_registros_cxp( Request $request, $doc_encabezado )
    {
        $lineas_registros = json_decode($request->lineas_registros);

        array_pop($lineas_registros);

        $total_abonos_cxc = 0;
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $abono = (float)$lineas_registros[$i]->abono;
            $registro_documento_pendiente = CxpMovimiento::find( (int)$lineas_registros[$i]->id_doc );
            
            // Almacenar registro de abono
            $datos = ['core_tipo_transaccion_id' => $doc_encabezado->core_tipo_transaccion_id]+
                        ['core_tipo_doc_app_id' => $doc_encabezado->core_tipo_doc_app_id]+
                        ['consecutivo' => $doc_encabezado->consecutivo]+
                        ['core_empresa_id' => $doc_encabezado->core_empresa_id]+
                        ['core_tercero_id' => $doc_encabezado->core_tercero_id]+
                        ['modelo_referencia_tercero_index' => $registro_documento_pendiente->modelo_referencia_tercero_index]+
                        ['referencia_tercero_id' => $registro_documento_pendiente->referencia_tercero_id]+
                        ['fecha' => $doc_encabezado->fecha]+
                        ['doc_cxp_transacc_id' => $registro_documento_pendiente->core_tipo_transaccion_id]+
                        ['doc_cxp_tipo_doc_id' => $registro_documento_pendiente->core_tipo_doc_app_id]+
                        ['doc_cxp_consecutivo' => $registro_documento_pendiente->consecutivo]+
                        ['abono' => $abono ]+
                        ['creado_por' => $doc_encabezado->creado_por];

            CxpAbono::create( $datos );

            // CONTABILIZAR
            $detalle_operacion = 'Abono factura de proveedor '.$registro_documento_pendiente->doc_proveedor_prefijo.' - '.$registro_documento_pendiente->doc_proveedor_consecutivo;

            // MOVIMIENTO DEBITO: Cuenta por pagar. Cada Documento pagado puede tener cuenta por cobrar distinta.
            // Del movimiento contable, Se llama al ID de la cuenta (moviento CR) afectada por el documento CxP para el tercero al que se le está haciendo el pago
            $cuenta_cxp_id = ContabMovimiento::where('core_tipo_transaccion_id',$registro_documento_pendiente->core_tipo_transaccion_id)
                                            ->where('core_tipo_doc_app_id',$registro_documento_pendiente->core_tipo_doc_app_id)
                                            ->where('consecutivo',$registro_documento_pendiente->consecutivo)
                                            ->where('core_tercero_id',$registro_documento_pendiente->core_tercero_id)
                                            ->where('valor_debito',0)
                                            ->value('contab_cuenta_id');

            if( is_null( $cuenta_cxp_id ) )
            {
                $cuenta_cxp_id = config('configuracion.cta_por_pagar_default');
            }

            ContabilidadController::contabilizar_registro2( array_merge( $request->all(), [ 'consecutivo' => $doc_encabezado->consecutivo ] ), $cuenta_cxp_id, $detalle_operacion, $abono, 0);

            // Se diminuye el saldo_pendiente en el documento pendiente, si saldo_pendiente == 0 se marca como pagado
            $registro_documento_pendiente->actualizar_saldos($abono);

            $total_abonos_cxc += $abono;
        }

        return $total_abonos_cxc;
    }

    public function almacenar_cheque( $request, $doc_encabezado )
    {
        $vec_3 = explode("-", $request->teso_medio_recaudo_id);
        if ( $vec_3[1] == 'cheque_propio' || $vec_3[1] == 'cheque_de_tercero' )
        {
            $cheque_id = (int)explode('-', $request->cheque_id)[0];
            if ( $cheque_id != 0 )
            {
                $cheque_activo = ControlCheque::find( $cheque_id );
                $cheque_activo->core_tipo_transaccion_id_consumo = $doc_encabezado->core_tipo_transaccion_id;
                $cheque_activo->core_tipo_doc_app_id_consumo = $doc_encabezado->core_tipo_doc_app_id;
                $cheque_activo->consecutivo_doc_consumo = $doc_encabezado->consecutivo;
                $cheque_activo->estado = 'Gastado';
                $cheque_activo->save();
            }
            
            if ( $cheque_id == 0 )
            {
                $datos = [
                            'fuente' => $vec_3[1],
                            'tercero_id' => $doc_encabezado->core_tercero_id,
                            'fecha_emision' => $request->fecha_emision,
                            'fecha_cobro' => $request->fecha_cobro,
                            'numero_cheque' => $request->numero_cheque,
                            'referencia_cheque' => $request->referencia_cheque,
                            'entidad_financiera_id' => $request->entidad_financiera_id,
                            'valor' => $doc_encabezado->valor_total,
                            'detalle' => $request->detalle,
                            'creado_por' => $doc_encabezado->creado_por,
                            'core_tipo_transaccion_id_origen' => $doc_encabezado->core_tipo_transaccion_id,
                            'core_tipo_doc_app_id_origen' => $doc_encabezado->core_tipo_doc_app_id,
                            'consecutivo' => $doc_encabezado->consecutivo,
                            'teso_caja_id' => $request->teso_caja_id,
                            'estado' => 'Emitido'
                        ];
                ControlCheque::create( $datos );
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );
        $encabezado_documento = TesoDocEncabezado::find( $id );
        
        $id_transaccion = $doc_encabezado->core_tipo_transaccion_id;
        $transaccion = TipoTransaccion::find( $id_transaccion );

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $transaccion, $id );

        // Documentos pagados
        $doc_pagados = CxpAbono::get_documentos_abonados( $doc_encabezado );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $documento_vista = '';

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo]
            ];
        
        return view( 'tesoreria.pagos_cxp.show', compact( 'id', 'botones_anterior_siguiente', 'id_transaccion', 'miga_pan','doc_encabezado','registros_contabilidad','doc_pagados','empresa','documento_vista', 'encabezado_documento') );
    }


    public function imprimir($id)
    {
        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        // Documentos pagados
        $doc_pagados = CxpAbono::get_documentos_abonados( $doc_encabezado );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $documento_vista = View::make( 'tesoreria.pagos_cxp.formatos_impresion.'.Input::get('formato_impresion_id'), compact('doc_encabezado', 'doc_pagados', 'empresa', 'registros_contabilidad' ) )->render();
        
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja = 'Letter';//array(0,0,50,800);//'A4';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }



    public function get_documentos_pendientes_cxp()
    {                
        $operador = '=';
        $cadena = Input::get('core_tercero_id');    

        $movimiento = DocumentosPendientes::get_documentos_referencia_tercero( $operador, $cadena );

        $vista = View::make( 'compras.incluir.ctas_por_pagar', compact('movimiento') )->render();
   
        return $vista;
    }

    public function ajax_get_terceros($tercero_id){
        $registros = Tercero::where('estado','Activo')
                            ->get();
            $opciones='<option value=""></option>';                
        foreach ($registros as $campo) {
            if ( $campo->id == $tercero_id ) {
                $selected = ' selected="selected"';
            }else{
                $selected = '';
            }
            $opciones.= '<option value="'.$campo->id.'"'.$selected.'>'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }

    /*
        Proceso de eliminar PAGO DE CXP
        Se eliminan los registros de:
            - cxp_abonos y su movimiento en contab_movimientos
            - teso_movimientos y su contabilidad. Además se actualiza el estado a Anulado en teso_doc_encabezados

        NOTA: el documento de CxP pagado puede tener registros de terceros diferentes
    */
    public static function anular_pago_cxp($id)
    {        
        $pago = TesoDocEncabezado::find( $id );

        $array_wheres = ['core_empresa_id'=>$pago->core_empresa_id, 
                            'core_tipo_transaccion_id' => $pago->core_tipo_transaccion_id,
                            'core_tipo_doc_app_id' => $pago->core_tipo_doc_app_id,
                            'consecutivo' => $pago->consecutivo];

        // >>> Validaciones inciales

        // Está en un documento cruce de cxp?
        $cantidad = CxpAbono::where($array_wheres)
                            ->where('doc_cruce_transacc_id','<>',0)
                            ->count();

        if($cantidad != 0)
        {
            return redirect( 'tesoreria/pagos_cxp/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Pago NO puede ser anulado. Está en documento cruce de CxP.');
        }
        

        // Se reversan los pagos hecho por este documento: aumenta el saldo_pendiente en el documento de CxP

        $documentos_abonados = CxpAbono::get_documentos_abonados( $pago );

        foreach ($documentos_abonados as $linea)
        {
            // Se verifica si cada documento abonado aún tiene saldo pendiente por pagar
            $documento_cxp_pendiente = CxpMovimiento::where('core_tipo_transaccion_id', $linea->doc_cxp_transacc_id)
                                                        ->where('core_tipo_doc_app_id', $linea->doc_cxp_tipo_doc_id)
                                                        ->where('consecutivo', $linea->doc_cxp_consecutivo)
                                                        ->where('core_tercero_id', $linea->core_tercero_id)
                                                        ->get()
                                                        ->first();

            if ( $documento_cxp_pendiente->estado == 'Pagado' )
            {
                // Se halla el total de todos los pagos que halla tenido (incluido el abono realizado por este pago)
                // Ahi que diferenciar por el tercero
                $valor_abonos_aplicados = CxpAbono::where('doc_cxp_transacc_id',$linea->doc_cxp_transacc_id)
                                                ->where('doc_cxp_tipo_doc_id',$linea->doc_cxp_tipo_doc_id)
                                                ->where('doc_cxp_consecutivo',$linea->doc_cxp_consecutivo)
                                                ->where('referencia_tercero_id',$linea->referencia_tercero_id)
                                                ->sum('abono');

                $nuevo_saldo_pendiente = $documento_cxp_pendiente->valor_documento - $valor_abonos_aplicados + $linea->abono;

                $nuevo_valor_pagado = $valor_abonos_aplicados - $linea->abono; // el valor_abonos_aplicados es como mínimo el valor de $linea->abono

            }else{
                
                $nuevo_saldo_pendiente = $documento_cxp_pendiente->saldo_pendiente + $linea->abono;
                $nuevo_valor_pagado = $documento_cxp_pendiente->valor_pagado - $linea->abono;
            }

            $documento_cxp_pendiente->valor_pagado = $nuevo_valor_pagado;
            $documento_cxp_pendiente->saldo_pendiente = $nuevo_saldo_pendiente;
            $documento_cxp_pendiente->estado = 'Pendiente';
            $documento_cxp_pendiente->save();

            // Se elimina el abono
            $linea->delete();
        }

        // Borrar movimiento de tesorería del pago y su contabilidad. Además actualizar estado del encabezado del documento de pago.
        TesoMovimiento::where('core_tipo_transaccion_id',$pago->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$pago->core_tipo_doc_app_id)
                        ->where('consecutivo',$pago->consecutivo)
                        ->delete();

        // Borrar movimiento contable generado por el documento de pago ( DB: CxP, CR: Caja/Banco )
        ContabMovimiento::where('core_tipo_transaccion_id',$pago->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$pago->core_tipo_doc_app_id)
                        ->where('consecutivo',$pago->consecutivo)
                        ->delete();

        // Este tipo de documento no afecta teso_doc_registros

        // Marcar como anulado el encabezado
        $pago->update( [ 'estado' => 'Anulado', 'modificado_por' => Auth::user()->email ] );

        $this->restablecer_cheque( $pago );

        return redirect( 'tesoreria/pagos_cxp/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Pago de CxP ANULADO correctamente.'); 
    }

    public function restablecer_cheque( $pago )
    {
        $cheque_gastado = ControlCheque::where([
                                                    'core_tipo_transaccion_id_consumo' => $pago->core_tipo_transaccion_id,
                                                    'core_tipo_doc_app_id_consumo' => $pago->core_tipo_doc_app_id,
                                                    'consecutivo_doc_consumo' => $pago->consecutivo
                                                ])
                                        ->get()
                                        ->first();

        if ( !is_null($cheque_gastado) )
        {
            $cheque_gastado->core_tipo_transaccion_id_consumo = 0;
            $cheque_gastado->core_tipo_doc_app_id_consumo = 0;
            $cheque_gastado->consecutivo_doc_consumo = 0;
            $cheque_gastado->estado = 'Recibido';
            $cheque_gastado->save();
        }

        $cheque_emitido = ControlCheque::where([
                                                    'core_tipo_transaccion_id' => $pago->core_tipo_transaccion_id,
                                                    'core_tipo_doc_app_id' => $pago->core_tipo_doc_app_id,
                                                    'consecutivo' => $pago->consecutivo
                                                ])
                                        ->get()
                                        ->first();

        if ( !is_null($cheque_emitido) )
        {
            $cheque_emitido->estado = 'Anulado';
            $cheque_emitido->save();
        }
    }
}