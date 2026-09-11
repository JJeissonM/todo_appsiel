<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;

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
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocEncabezadoPagoCxp;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoEntidadFinanciera;
use App\Tesoreria\Services\DaviviendaMassPaymentFileService;
use App\Tesoreria\Services\ChequePaymentService;

use App\Tesoreria\RegistroDeEfectivo;
use App\Tesoreria\RegistroDeTransferenciaConsignacion;
use App\Tesoreria\RegistroDescuentoProntoPago;
use App\Tesoreria\RegistroDeTarjetaDebito;
use App\Tesoreria\RegistroDeTarjetaCredito;
use App\Tesoreria\RegistroDeCheque;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\Retencion;
use App\Contabilidad\RegistroRetencion;

use App\Compras\DescuentoProntoPago;

use App\CxP\Services\CxpAccountingAccountResolver;
use App\CxP\CxpMovimiento;
use App\CxP\DocumentosPendientes;
use App\CxP\CxpAbono;
use App\Nomina\NomPagoAutomatico;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use NumerosEnLetras;

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
        if (ChequePaymentService::usaChequera()) {
            try {
                $medioCheque = TesoMedioRecaudo::findOrFail(
                    TesoMedioRecaudo::get_id_por_tipo_registro('cheque_propio')
                );
                $cuentas_bancarias = $medioCheque->opciones_cuentas_bancarias_destino();
            } catch (\Exception $e) {
                $cuentas_bancarias = ['' => ''];
            }
        }
        $retenciones = Retencion::opciones_campo_select();
        $descuentos_pronto_pago = DescuentoProntoPago::opciones_campo_select();

        $tipos_operaciones = [ '' => '', 'pago-proveedores' => 'Pago proveedores (CxP)', 'anticipo-proveedor' => 'Anticipo proveedor (CxP a favor)', 'otros-pagos' => 'Otros pagos', 'prestamo-entregado' => 'Préstamo financiero (Crear CxC)'];

        $terceros = [''];

        $entidades_financieras = TesoEntidadFinanciera::opciones_campo_select();

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Crear nuevo' ]
            ];

        return view('tesoreria.pagos_cxp.create', compact( 'form_create','id_transaccion','motivos','miga_pan','medios_recaudo','cajas','cuentas_bancarias', 'terceros', 'entidades_financieras', 'retenciones', 'tipos_operaciones', 'descuentos_pronto_pago' ) );
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
        try {
            $doc_encabezado = DB::transaction(function () use ($request) {
                $doc = $this->crear_encabezado_documento($request, $request->url_id_modelo);

                $totalAbonos = $this->almacenar_registros_cxp($request, $doc);

                (new RegistroRetencion())->almacenar_nuevos_registros(
                    $request->lineas_registros_retenciones,
                    $doc,
                    $totalAbonos,
                    'practicada'
                );

                (new RegistroDescuentoProntoPago())->almacenar_nuevos_registros(
                    $request->lineas_registros_descuento_pronto_pagos,
                    $doc,
                    'recibido'
                );

                (new RegistroDeEfectivo())->almacenar_registros($request->lineas_registros_efectivo, $doc);
                (new RegistroDeTransferenciaConsignacion())->almacenar_registros($request->lineas_registros_transferencia_consignacion, $doc);
                (new RegistroDeTarjetaDebito())->almacenar_registros($request->lineas_registros_tarjeta_debito, $doc);
                (new RegistroDeTarjetaCredito())->almacenar_registros($request->lineas_registros_tarjeta_credito, $doc);
                (new RegistroDeCheque())->almacenar_registros(
                    $request->lineas_registros_cheques,
                    $doc,
                    'cheque_propio',
                    'Emitido',
                    'propio'
                );

                $this->validar_balance_contable($doc);
                $doc->actualizar_valor_total();

                return $doc;
            });
        } catch (\Exception $e) {
            $mensaje = 'No fue posible registrar el pago: ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $mensaje
                ], 422);
            }

            return redirect('tesoreria/pagos_cxp/create?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)
                ->withInput()
                ->with('mensaje_error', $mensaje);
        }

        $urlDocumento = url('tesoreria/pagos_cxp/' . $doc_encabezado->id)
            . '?id=' . $request->url_id
            . '&id_modelo=' . $request->url_id_modelo
            . '&id_transaccion=' . $request->url_id_transaccion;

        if ($request->ajax()) {
            return response()->json([
                'status' => 'ok',
                'redirect' => $urlDocumento
            ], 200);
        }

        // se llama la vista de PagoCxpController@show
        return redirect($urlDocumento);
    }

    protected function validar_balance_contable($doc_encabezado)
    {
        $filtros = [
            'core_tipo_transaccion_id' => (int)$doc_encabezado->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => (int)$doc_encabezado->core_tipo_doc_app_id,
            'consecutivo' => (int)$doc_encabezado->consecutivo,
            'core_empresa_id' => (int)$doc_encabezado->core_empresa_id
        ];

        $debitos = round((float)ContabMovimiento::where($filtros)->sum('valor_debito'), 2);
        $creditos = round(abs((float)ContabMovimiento::where($filtros)->sum('valor_credito')), 2);

        if (abs($debitos - $creditos) > 0.01) {
            throw new \Exception(
                'El pago no fue guardado porque el asiento contable está descuadrado. ' .
                'Débitos: $' . number_format($debitos, 2, ',', '.') .
                '; créditos: $' . number_format($creditos, 2, ',', '.') . '.'
            );
        }
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
            $registro_movimiento_cxp = CxpMovimiento::find( (int)$lineas_registros[$i]->id_doc );
            
            // Almacenar registro de abono
            $datos = ['core_tipo_transaccion_id' => $doc_encabezado->core_tipo_transaccion_id]+
                        ['core_tipo_doc_app_id' => $doc_encabezado->core_tipo_doc_app_id]+
                        ['consecutivo' => $doc_encabezado->consecutivo]+
                        ['core_empresa_id' => $doc_encabezado->core_empresa_id]+
                        ['core_tercero_id' => $doc_encabezado->core_tercero_id]+
                        ['modelo_referencia_tercero_index' => $registro_movimiento_cxp->modelo_referencia_tercero_index]+
                        ['referencia_tercero_id' => $registro_movimiento_cxp->referencia_tercero_id]+
                        ['fecha' => $doc_encabezado->fecha]+
                        ['doc_cxp_transacc_id' => $registro_movimiento_cxp->core_tipo_transaccion_id]+
                        ['doc_cxp_tipo_doc_id' => $registro_movimiento_cxp->core_tipo_doc_app_id]+
                        ['doc_cxp_consecutivo' => $registro_movimiento_cxp->consecutivo]+
                        ['abono' => $abono ]+
                        ['creado_por' => $doc_encabezado->creado_por];

            CxpAbono::create( $datos );

            // CONTABILIZAR
            $detalle_operacion = 'Abono factura de proveedor '.$registro_movimiento_cxp->doc_proveedor_prefijo.' - '.$registro_movimiento_cxp->doc_proveedor_consecutivo;

            // MOVIMIENTO DEBITO: Cuenta por pagar originalmente causada.
            $cuenta_cxp_id = (new CxpAccountingAccountResolver())->getPayableAccountId($registro_movimiento_cxp);

            if( is_null( $cuenta_cxp_id ) )
            {
                $cuenta_cxp_id = config('configuracion.cta_por_pagar_default');
            }

            ContabilidadController::contabilizar_registro2( array_merge( $request->all(), [ 'consecutivo' => $doc_encabezado->consecutivo ] ), $cuenta_cxp_id, $detalle_operacion, $abono, 0);

            // Se diminuye el saldo_pendiente en el documento pendiente, si saldo_pendiente == 0 se marca como pagado
            $registro_movimiento_cxp->actualizar_saldos($abono);

            $total_abonos_cxc += $abono;
        }

        return $total_abonos_cxc;
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

        $davivienda_archivo = (new DaviviendaMassPaymentFileService())->summary($encabezado_documento);

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo]
            ];
        
        return view( 'tesoreria.pagos_cxp.show', compact( 'id', 'botones_anterior_siguiente', 'id_transaccion', 'miga_pan','doc_encabezado','registros_contabilidad','doc_pagados','empresa','documento_vista', 'encabezado_documento', 'davivienda_archivo') );
    }


    public function imprimir($id)
    {
        if (Input::get('formato_impresion_id') === 'apm') {
            return response()->json($this->build_apm_payload_response($id));
        }

        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        // Documentos pagados
        $doc_pagados = CxpAbono::get_documentos_abonados( $doc_encabezado );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $documento_vista = View::make( 'tesoreria.pagos_cxp.formatos_impresion.'.Input::get('formato_impresion_id'), compact('doc_encabezado', 'doc_pagados', 'empresa', 'registros_contabilidad' ) )->render();
        
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja = 'Letter';//array(0,0,50,800);//'A4';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }

    public function get_apm_payload($id)
    {
        try {
            return response()->json($this->build_apm_payload_response($id));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    protected function build_apm_payload_response($id)
    {
        $encabezado = TesoDocEncabezadoPagoCxp::with([
            'empresa',
            'tipo_documento_app',
            'tercero.tipo_doc_identidad',
            'tercero.ciudad',
            'caja',
            'cuenta_bancaria.entidad_financiera',
            'medio_recaudo'
        ])->findOrFail($id);

        $registros = TesoDocRegistro::get_registros_impresion($id);
        $payload = $this->build_json_egreso_apm($encabezado, $registros);

        return [
            'payload' => $payload,
            'document_meta' => [
                'core_empresa_id' => (int) $encabezado->core_empresa_id,
                'core_tipo_transaccion_id' => (int) $encabezado->core_tipo_transaccion_id,
                'core_tipo_doc_app_id' => (int) $encabezado->core_tipo_doc_app_id,
                'consecutivo' => (int) $encabezado->consecutivo,
                'document_type' => 'comprobante_egreso',
                'document_label' => $encabezado->get_label_documento()
            ]
        ];
    }

    protected function build_json_egreso_apm($encabezado, $registros)
    {
        $printerId = trim((string) config('tesoreria.apm_printer_id_pago_cxp'));

        if ($printerId === '') {
            throw new \RuntimeException('No hay impresora APM configurada para pagos CxP (tesoreria.apm_printer_id_pago_cxp).');
        }

        $device = \App\Ventas\ApmDevice::where('device_id', $printerId)->first();

        if (is_null($device)) {
            throw new \RuntimeException('La impresora APM "' . $printerId . '" no existe en el catalogo de dispositivos (apm_devices).');
        }

        if (trim((string) $device->estado) !== 'Activo') {
            throw new \RuntimeException('La impresora APM "' . $printerId . '" no esta activa en el catalogo de dispositivos.');
        }

        $stationId = 'TESORERIA';
        $dateInfo = $this->build_apm_date_info($encabezado->fecha);
        $cheque = $encabezado->cheques_relacionados_pagos()->first();
        $receiver = $encabezado->tercero;
        $receiverName = is_null($receiver) ? '' : $receiver->descripcion;
        $receiverId = is_null($receiver) ? '' : $receiver->numero_identificacion;
        $city = '';

        if (!is_null($receiver) && !is_null($receiver->ciudad)) {
            $city = $receiver->ciudad->descripcion;
        }

        $bankCode = '';
        if (!is_null($encabezado->cuenta_bancaria) && !is_null($encabezado->cuenta_bancaria->entidad_financiera)) {
            $bankCode = (string) $encabezado->cuenta_bancaria->entidad_financiera->id;
        }

        $conceptLines = $this->build_apm_concept_lines($encabezado, $registros);
        $createdBy = strtoupper(explode('@', (string) $encabezado->creado_por)[0]);

        return [
            'JobId' => 'EGR-' . $encabezado->id . '-' . time(),
            'StationId' => $stationId,
            'PrinterId' => $printerId,
            'DocumentType' => 'comprobante_egreso',
            'Document' => [
                'cheque' => [
                    'COPY' => 'COPIA # 1',
                    'Number' => is_null($cheque) ? '' : (string) $cheque->numero_cheque,
                    'DateInfo' => $dateInfo,
                    'PayTo' => $receiverName,
                    'AmountText' => strtoupper(trim(NumerosEnLetras::convertir((float) $encabezado->valor_total, 'pesos', false))) . ' MCTE.',
                    'Amount' => number_format((float) $encabezado->valor_total, 2, ',', '.'),
                    'City' => strtoupper($city)
                ],
                'egreso' => [
                    'COPY' => 'COPIA # 1',
                    'Number' => $encabezado->get_label_documento(),
                    'DateInfo' => $dateInfo,
                    'BankCode' => $bankCode,
                    'ReceiverName' => $receiverName,
                    'ReceiverId' => (string) $receiverId,
                    'Concept' => $conceptLines,
                    'Description' => trim(strip_tags((string) $encabezado->documento_soporte)),
                    'Items' => $this->build_apm_items($encabezado, $registros),
                    'TotalDebit' => $this->format_apm_money($encabezado->valor_total),
                    'TotalCredit' => $this->format_apm_money($encabezado->valor_total),
                    'CreatedBy' => $createdBy
                ]
            ]
        ];
    }

    protected function build_apm_items($encabezado, $registros)
    {
        $items = [];

        $contab_mov = $encabezado->get_accounting_movement();
        $doc_pagados = CxpAbono::get_documentos_abonados($encabezado);

        $abonosTomados = [];
        foreach ($contab_mov as $registro) {

            $valor_debito = (float) $registro->valor_debito;
            $valor_credito = (float) $registro->valor_credito;

            $reference = $this->find_apm_reference($doc_pagados, $abonosTomados, $valor_debito);

            if (!is_null($reference)) {
                $abonosTomados[] = (int)$reference->id;
            }

            $items[] = [
                'Account' => $registro->cuenta ? (string) $registro->cuenta->codigo : 'Cta Contable null',
                'CO' => '-',
                'ThirdParty' => $registro->tercero ? (string) $registro->tercero->numero_identificacion : 'Tercero null',
                'Reference' => !is_null($reference) ? (string) $reference->documento_prefijo_consecutivo : '-',
                'Debit' => $valor_debito > 0 ? $this->format_apm_money($valor_debito) : $this->format_apm_money(0),
                'Credit' => $valor_credito != 0 ? $this->format_apm_money(abs($valor_credito)) : $this->format_apm_money(0)
            ];
        }

        return $items;
    }

    protected function find_apm_reference($documentosPagados, array $abonosTomados, $valorDebito)
    {
        $valorDebito = (float)$valorDebito;
        if ($valorDebito <= 0) {
            return null;
        }

        return $documentosPagados
            ->filter(function ($abono) use ($abonosTomados, $valorDebito) {
                return !in_array((int)$abono->id, $abonosTomados, true)
                    && abs((float)$abono->abono - $valorDebito) < 0.01;
            })
            ->first();
    }

    protected function build_apm_concept_lines($encabezado, $registros)
    {
        $lines = [];
        $baseConcept = trim(strip_tags((string) $encabezado->descripcion));

        if ($baseConcept !== '') {
            foreach (preg_split("/(\r\n|\n|\r)/", $baseConcept) as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $lines[] = strtoupper($line);
                }
            }
        }

        foreach ($registros as $registro) {
            if (count($lines) >= 4) {
                break;
            }

            $detalle = trim((string) $registro->detalle_operacion);
            if ($detalle !== '') {
                $lines[] = strtoupper($detalle);
            }
        }

        if (empty($lines)) {
            $lines[] = 'PAGO DE CUENTAS POR PAGAR';
        }

        while (count($lines) < 4) {
            $lines[] = '';
        }

        return array_slice($lines, 0, 4);
    }

    protected function build_apm_date_info($fecha)
    {
        $partes = explode('-', substr((string) $fecha, 0, 10));
        if (count($partes) !== 3) {
            return ['Day' => '', 'Month' => '', 'Year' => ''];
        }

        return [
            'Day' => $partes[2],
            'Month' => $partes[1],
            'Year' => $partes[0]
        ];
    }

    protected function format_apm_money($value)
    {
        return '$' . number_format((float) $value, 2, '.', ',');
    }

    public function get_documentos_pendientes_cxp()
    {
        $operador = '=';
        $cadena = Input::get('core_tercero_id');

        $movimiento = DocumentosPendientes::get_documentos_referencia_tercero($operador, $cadena);

        $vista = View::make('compras.incluir.ctas_por_pagar', compact('movimiento'))->render();

        return $vista;
    }

    public function ajax_get_terceros($tercero_id)
    {
        $registros = Tercero::where('estado', 'Activo')->get();
        $opciones = '<option value=""></option>';
        foreach ($registros as $campo) {
            if ($campo->id == $tercero_id) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $opciones .= '<option value="' . $campo->id . '"' . $selected . '>' . $campo->descripcion . '</option>';
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
    public function anular_pago_cxp($id)
    {        
        $pago = TesoDocEncabezado::find( $id );

        if (is_null($pago)) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))
                ->with('mensaje_error', 'El pago no existe.');
        }

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

        DB::beginTransaction();
        try {

        // Se reversan los pagos hecho por este documento: aumenta el saldo_pendiente en el documento de CxP

        $documentos_abonados = CxpAbono::get_documentos_abonados( $pago );
        
        $documentos_cxp_ya_aplicados = [];
        foreach ($documentos_abonados as $registro_abono)
        {
            // Se verifica si cada documento abonado por este pago aún tiene saldo pendiente por pagar
            $documento_cxp_pendiente = CxpMovimiento::where('core_tipo_transaccion_id', $registro_abono->doc_cxp_transacc_id)
                                    ->where('core_tipo_doc_app_id', $registro_abono->doc_cxp_tipo_doc_id)
                                    ->where('consecutivo', $registro_abono->doc_cxp_consecutivo)
                                    ->where('core_tercero_id', $registro_abono->core_tercero_id)
                                    ->whereNotIn('id',$documentos_cxp_ya_aplicados)
                                    ->get()
                                    ->first();

            if ( $documento_cxp_pendiente->estado == 'Pagado' )
            {
                // Se halla el total de todos los pagos que halla tenido (incluido el abono realizado por este pago)
                // Ahi que diferenciar por el tercero
                /*

                    ERROR. CUANDO SE PAGAN VARIOS REGISTROS DEL MISMO DOCUMENTO SOLO REVERSA UN REGISTRO DE CXC
                    EJEMPLO, CONTABILIZACIO DE LA NOMINA: UNA EPS TIENE VARIOS REGISTROS DE CXP CON EL MISMO DOC. 
                    SI HAGO EL PAGO DE CXC DE TODOS LOS REGISTRO Y LUEGO ANULO ESE PAGO, SOLO ME "REVIVE" UN REGISTRO DE CXP

                */
                $array_wheres_abono_cxp = [
                    ['doc_cxp_transacc_id', '=', $registro_abono->doc_cxp_transacc_id],
                    ['doc_cxp_tipo_doc_id' , '=',  $registro_abono->doc_cxp_tipo_doc_id],
                    ['doc_cxp_consecutivo' , '=',  $registro_abono->doc_cxp_consecutivo]
                ];

                if ( $registro_abono->modelo_referencia_tercero_index == '' ) 
                {
                    $array_wheres_abono_cxp = array_merge($array_wheres_abono_cxp, ['core_tercero_id' => $registro_abono->core_tercero_id ]);
                }else{
                    $array_wheres_abono_cxp = array_merge($array_wheres_abono_cxp, ['referencia_tercero_id' => $registro_abono->referencia_tercero_id ]);
                }

                $valor_abonos_aplicados = CxpAbono::where( $array_wheres_abono_cxp )
                                                ->sum('abono');

                if ( $valor_abonos_aplicados > $documento_cxp_pendiente->valor_documento) {
                    $valor_abonos_aplicados = $documento_cxp_pendiente->valor_documento;
                }

                $nuevo_saldo_pendiente = $documento_cxp_pendiente->valor_documento - $valor_abonos_aplicados + $registro_abono->abono;

                $nuevo_valor_pagado = $valor_abonos_aplicados - $registro_abono->abono; // el valor_abonos_aplicados es como mínimo el valor de $registro_abono->abono

            }else{
                
                $nuevo_saldo_pendiente = $documento_cxp_pendiente->saldo_pendiente + $registro_abono->abono;
                $nuevo_valor_pagado = $documento_cxp_pendiente->valor_pagado - $registro_abono->abono;
            }

            $documento_cxp_pendiente->valor_pagado = $nuevo_valor_pagado;
            $documento_cxp_pendiente->saldo_pendiente = $nuevo_saldo_pendiente;
            $documento_cxp_pendiente->estado = 'Pendiente';
            $documento_cxp_pendiente->save();
            
            $documentos_cxp_ya_aplicados[] = $documento_cxp_pendiente->id;

            // Se elimina el abono
            $registro_abono->delete();
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

        // Conserva la trazabilidad del proceso de nómina. Los detalles no se
        // eliminan: el saldo de CxP restaurado vuelve a habilitar al empleado.
        if (Schema::hasTable('nom_pagos_automaticos')) {
            NomPagoAutomatico::where('teso_doc_encabezado_id', $pago->id)
                ->update([
                    'estado' => 'Anulado',
                    'modificado_por' => Auth::user()->email,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        }

        $this->restablecer_cheque( $pago );

        DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect('tesoreria/pagos_cxp/' . $id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))
                ->with('mensaje_error', 'No fue posible anular el pago: ' . $e->getMessage());
        }

        return redirect( 'tesoreria/pagos_cxp/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Pago de CxP ANULADO correctamente.'); 
    }

    public function restablecer_cheque( $pago )
    {
        (new ChequePaymentService())->anularDocumento($pago);
    }
}
