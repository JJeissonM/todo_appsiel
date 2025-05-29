<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;

use NumerosEnLetras;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos
use App\Sistema\Html\BotonesAnteriorSiguiente;


// Modelos
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Tercero;
use App\Core\Empresa;

use App\CxC\CxcDocEncabezado;
use App\CxC\CxcAbono;
use App\CxC\CxcMovimiento;

use App\CxP\CxpMovimiento;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\ContabMovimiento;
use App\Http\Controllers\Sistema\EmailController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class RecaudoController extends TransaccionController
{
    
    protected $datos = [];
    protected $consecutivo;

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
        $id_transaccion = 8;// 8 = Recaudo cartera

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find( Input::get('id_modelo') );

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create');

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $motivos = [''];//RecaudoController::get_motivos($id_transaccion);
        $medios_recaudo = RecaudoController::get_medios_recaudo();
        $cajas = RecaudoController::get_cajas();
        $cuentas_bancarias = RecaudoController::get_cuentas_bancarias();

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Crear nuevo' ]
            ];

        if ( count($cajas) == 0) {
            return redirect('web/create?id=3&id_modelo=45')->with('mensaje_error','No exite ninguna Caja creada. Debe crear al menos UNA.');
        }

        if ( count($cuentas_bancarias) == 0) {
            return redirect('web/create?id=3&id_modelo=33')->with('mensaje_error','No exite ninguna Cuenta bancaria creada. Debe crear al menos UNA.');
        }

        return view('tesoreria.recaudos.create', compact('form_create','id_transaccion','motivos','miga_pan','medios_recaudo','cajas','cuentas_bancarias'));
    }

    /**
     * Store a newly created resource in storage.
     * // Este método es llamado desde ModeloController@store
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $doc_encabezado = $this->crear_encabezado_documento($request, $request->url_id_modelo);

        $this->datos = array_merge( $request->all(), 
                                    [ 
                                        'consecutivo' => $doc_encabezado->consecutivo,
                                        'documento_cartera_id' => $doc_encabezado->id
                                    ] );

        $lineas_registros = json_decode( $request->lineas_registros_medios_recaudo );

        // NOTA: Se registra el movimiento contable, sin importar que la empresa no tenga la APP de Contabilidad
        //      Cuenta              DB          CR
        //    CAJA/BANCO           $$$$
        //    CARTERA/ANTICIPO                 $$$$

        // Ahora Se afectarán varias tablas más en la BD: teso_doc_registros, cxc_movimientos, cxc_documento_tiene_recaudos, teso_movimientos


        // 1ro. MOVIMIENTO DE TESORERIA. se guardan los registros de medios de recaudo (teso_doc_registros)
        // Se recorre la tabla enviada en el request, descartando la primera y las dos últimas filas
        $total_recaudo = 0;
        $cant = count($lineas_registros)-1;

        for ($i=0; $i < $cant; $i++) 
        {
            // Se obtienen las id de los campos que se van a almacenar. Los campos vienen separados por "-" en cada columna de la tabla 
            $vec_1 = explode("-", $lineas_registros[$i]->teso_medio_recaudo_id);
            $teso_medio_recaudo_id = $vec_1[0];

            $vec_2 = explode("-", $lineas_registros[$i]->teso_motivo_id);
            $teso_motivo_id = $vec_2[0];

            $vec_3 = explode("-", $lineas_registros[$i]->teso_caja_id);
            $teso_caja_id = $vec_3[0];

            $vec_4 = explode("-", $lineas_registros[$i]->teso_cuenta_bancaria_id);
            $teso_cuenta_bancaria_id = $vec_4[0];

            // Se les quita la etiqueta de signo peso y unidad de medida a los textos recibidos
            // en la tabla de movimiento
            $valor = (float)substr($lineas_registros[$i]->valor, 1);

            TesoDocRegistro::create(
                            [ 'core_tercero_id' => $this->datos['core_tercero_id'] ] + 
                            [ 'teso_encabezado_id' => $doc_encabezado->id ] + 
                            [ 'teso_medio_recaudo_id' => $teso_medio_recaudo_id ] + 
                            [ 'teso_motivo_id' => $teso_motivo_id] + 
                            [ 'teso_caja_id' => $teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id] + 
                            [ 'valor' => $valor] + 
                            [ 'estado' => 'Activo']
                        );


            // 1.1. Para cada registro del documento de recaudo, se va actualizando el movimiento de tesorería (teso_movimientos)
            TesoMovimiento::create( $this->datos + 
                            [ 'teso_motivo_id' => $teso_motivo_id] + 
                            [ 'teso_caja_id' => $teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id] + 
                            [ 'teso_medio_recaudo_id' => $teso_medio_recaudo_id] + 
                            [ 'valor_movimiento' => $valor]
                        );

            $total_recaudo += $valor;


            /*
                **  Determinar la cuenta contable DB (CAJA O BANCOS)
            */
            if ($teso_caja_id != 0) {
                $sql_contab_cuenta_id = TesoCaja::find($teso_caja_id);
                $contab_cuenta_id = $sql_contab_cuenta_id->contab_cuenta_id;
            }
            if ($teso_cuenta_bancaria_id != 0) {
                $sql_contab_cuenta_id = TesoCuentaBancaria::find($teso_cuenta_bancaria_id);
                $contab_cuenta_id = $sql_contab_cuenta_id->contab_cuenta_id;
            }

            $detalle_operacion = $request->descripcion;
            $valor_debito = $valor;
            $valor_credito = 0;

            $this->contabilizar_registro($contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id, $teso_cuenta_bancaria_id);

            // Como los motivos se ingresaron al momento de registrar cada medio de pago,
            // Si es un Anticipo u Otro Recaudo se contabiliza la contrapartida de cada motivo Inmediatamente
            /*
                **  Determinar la cuenta contable desde el motivo
            */
            $motivo = TesoMotivo::find( $teso_motivo_id );
            $contab_cuenta_id = $motivo->contab_cuenta_id;

            $valor_debito = 0;
            $valor_credito = $valor;

            $this->contabilizar_registro($contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);
        } // Fin for cada medio de recaudo ingresado

        $doc_encabezado->valor_total = $total_recaudo;
        $doc_encabezado->save();
        
        // Solo los anticipos de clientes se guardan en el movimiento de cartera (CxC)
        if ( $request->teso_tipo_motivo == 'anticipo-clientes' )
        {
            $this->datos['valor_documento'] = $total_recaudo * -1;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $total_recaudo * -1;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            $this->datos['detalle'] = $detalle_operacion;
            CxcMovimiento::create( $this->datos );
        }
 
        // Generar CxP porque se utilizó dinero de un agente externo (banco, coopertaiva, tarjeta de crédito).
        if ( $request->teso_tipo_motivo == 'prestamo-recibido' )
        {
            $this->datos['valor_documento'] = $total_recaudo;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $total_recaudo;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            $this->datos['detalle'] = $detalle_operacion;
            CxpMovimiento::create( $this->datos );
        }

        // se llama la vista de RecaudoController@show
        return redirect( 'tesoreria/recaudos/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );
        $doc_registros = TesoDocRegistro::get_registros_impresion( $doc_encabezado->id );
        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        //dd($doc_registros);

        $empresa = $this->empresa;
        $id_transaccion = $this->transaccion->id;
        $documento_vista = '';

        $miga_pan = [
                [ 'url' => $this->app->app.'?id='.Input::get('id'), 'etiqueta' => $this->app->descripcion],
                ['url' => 'web'.$this->variables_url, 'etiqueta' => $this->modelo->descripcion ],
                ['url'=>'NO','etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo ]
            ];

        return view( 'tesoreria.recaudos.show',compact('empresa','botones_anterior_siguiente','doc_encabezado','doc_registros','registros_contabilidad','miga_pan','id','id_transaccion','documento_vista') );
    }


    public function imprimir($id)
    {
        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );        
        
        $documento_vista = $this->generar_documento_vista_print($doc_encabezado, Input::get('formato_impresion_id'));

        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja='Letter';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista )->setPaper($tam_hoja,$orientacion);

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }    

    public function generar_documento_vista_print($doc_encabezado, $formato_impresion_id)
    {
        $doc_registros = TesoDocRegistro::get_registros_impresion( $doc_encabezado->id );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = [];//TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $elaboro = $doc_encabezado->creado_por;
        
        if(Input::get('formato_impresion_id') == 'estandar'){
            $documento_vista = View::make( 'tesoreria.recaudos.documento_imprimir', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad', 'elaboro' ) )->render();
        }
        if(Input::get('formato_impresion_id') == 'estandar2'){
            $documento_vista = View::make( 'tesoreria.recaudos.documento_imprimir2', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad', 'elaboro' ) )->render();
        }
        if(Input::get('formato_impresion_id') == 'pos'){
            $documento_vista = View::make( 'tesoreria.recaudos.pos', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad', 'elaboro' ) )->render();
        }
        if(Input::get('formato_impresion_id') == 'colegio'){
            $documento_vista = View::make( 'tesoreria.recaudos.documento_imprimir_colegio', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad', 'elaboro' ) )->render();
        }

        return $documento_vista;
    }

    public static function vista_preliminar_recaudos($id, $vista)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados_recaudos.consecutivo) AS documento';

        $encabezado_doc = TesoDocEncabezado::get_un_registro($id);

        //$tipo_transaccion = TipoTransaccion::find($encabezado_doc->core_tipo_transaccion_id);

        //$core_app = $tipo_transaccion->core_app;

        $tipo_doc_app = TipoDocApp::find($encabezado_doc->core_tipo_doc_app_id);

        $descripcion_transaccion = $tipo_doc_app->descripcion;

        // Se crea una vista-tabla con los registros de medios de recaudos
        $registros = TesoDocRegistro::leftJoin('teso_medios_recaudo','teso_medios_recaudo.id','=','teso_doc_registros.teso_medio_recaudo_id')
            ->leftJoin('teso_motivos','teso_motivos.id','=','teso_doc_registros.teso_motivo_id')
            ->where('teso_encabezado_id',$encabezado_doc->id)
            ->select('teso_medios_recaudo.descripcion AS medio_recaudo','teso_motivos.descripcion AS motivo','teso_doc_registros.valor')
            ->get();

        $total_recaudo=0;
        $i=0;
        $tabla1 = '<table style="margin-top: -4px; font-size: 0.9em;">
                        <tr>
                            <td colspan="2" align="center" style="border: solid 1px black;">
                               <b>Detalles del recaudo</b>
                            </td>
                        </tr>
                        <tr class="encabezado">
                            <td>
                               Medio de pago
                            </td>
                            <td>
                               Valor
                            </td>
                        </tr>';
        foreach ($registros as $registro) {
            $tabla1.='<tr>
                            <td>
                               '.$registro->medio_recaudo.'
                            </td>
                            <td>
                               $'.number_format($registro->valor_total, 0, ',', '.').'
                            </td>
                        </tr>';
            $total_recaudo+=$registro->valor_total;
            $motivo_descripcion = $registro->motivo;
        }
        $tabla1.='<tr>
                        <td>
                           &nbsp;
                        </td>
                        <td style="border-top: solid 1px black;">
                           $'.number_format($total_recaudo, 0, ',', '.').' ('.NumerosEnLetras::convertir($total_recaudo,'pesos',false).')
                        </td>
                    </tr>
                </table>';

        // Transacciones afectadas por el recaudo
        if ( $encabezado_doc->teso_tipo_motivo == 'recaudo-cartera') 
        {
            // Se crea una tabla con los documentos pagados por el recaudo
            $documentos_pagados = DB::table('cxc_documento_tiene_recaudos')
                            ->where('cxc_movimiento_id', $id )
                            ->where('transaccion_origen_doc_recaudo_id', $encabezado_doc->core_tipo_transaccion_id )
                            ->get();

            $i=0;
            $tabla2 = '<table  class="tabla_registros" style="margin-top: -4px;">
                            <tr>
                                <td colspan="3" align="center">
                                   <b>Detalle de registros del recaudo</b>
                                </td>
                            </tr>
                            <tr class="encabezado">
                                <td>
                                   Documento
                                </td>
                                <td>
                                   Detalle
                                </td>
                                <td>
                                   Valor pagado
                                </td>
                            </tr>';
            $total_pagado = 0;
            foreach ($documentos_pagados as $registro) 
            {
                $cxc_movimiento = CxcMovimiento::find($registro->cxc_movimiento_id);

                $array_wheres = ['core_empresa_id'=>$cxc_movimiento->core_empresa_id, 
                              'core_tipo_transaccion_id' => $cxc_movimiento->core_tipo_transaccion_id,
                              'core_tipo_doc_app_id' => $cxc_movimiento->core_tipo_doc_app_id,
                              'consecutivo' => $cxc_movimiento->consecutivo];
                
                $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo) AS documento_cxc';

                $cxc_doc_encabezado = CxcDocEncabezado::leftJoin('core_tipos_docs_apps','core_tipos_docs_apps.id','=','cxc_doc_encabezados.core_tipo_doc_app_id')
                            ->where($array_wheres)
                            ->select(DB::raw($select_raw),'cxc_doc_encabezados.descripcion')->get()[0];

                $tabla2.='<tr  class="fila-'.$i.'" >
                                <td>
                                   '.$cxc_doc_encabezado->documento_cxc.'
                                </td>
                                <td>
                                   '.$cxc_doc_encabezado->descripcion.'
                                </td>
                                <td>
                                   $'.number_format($registro->valor_pagado, 0, ',', '.').'
                                </td>
                            </tr>';

                $total_pagado += $registro->valor_pagado;

                $i++;
                if ($i==3) {
                    $i=1;
                }
            }


            // Si el total del recaudo es diferente al total de documentos pagados, quiere decir que la diferencia se envió como anticipo
            if ( $total_recaudo != $total_pagado) {

                $tabla2.='<tr  class="fila-'.$i.'" >
                                <td>
                                   '.$encabezado_doc->documento.'
                                </td>
                                <td>
                                   Anticipo
                                </td>
                                <td>
                                   $'.number_format(($total_recaudo - $total_pagado), 0, ',', '.').'
                                </td>
                            </tr>';
            }

            $tabla2.='</table>';

        }else{

            // Si NO es un recaudo de cartera, se llama al movimiento contable
            $movimiento = ContabMovimiento::join('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where([
                            'core_tipo_transaccion_id' => $encabezado_doc->core_tipo_transaccion_id,
                            'core_tipo_doc_app_id' => $encabezado_doc->core_tipo_doc_app_id,
                             'consecutivo' => $encabezado_doc->consecutivo 
                            ])->select('contab_cuentas.codigo','contab_cuentas.descripcion','contab_movimientos.valor_debito','contab_movimientos.valor_credito','contab_movimientos.detalle_operacion')
                            ->get();

            $tabla2 = '<table  class="tabla_registros" style="margin-top: -3px;">
                            <tr>
                                <td colspan="2" align="center">
                                   <b>MOVIMIENTO</b>
                                </td>
                            </tr>
                            <tr class="encabezado">
                                <td>
                                   Detalle
                                </td>
                                <td>
                                   Valor
                                </td>
                            </tr>';

                

                foreach ($movimiento as $fila) {

                    $valor = $fila->valor_debito + $fila->valor_credito;

                    if ($valor < 0) {
                        $valor = $valor * -1;
                    }

                    $i++;
                    if ($i==3) {
                        $i=1;
                    }
                }

            $tabla2.='<tr>
                            <td>
                               Anticipo '.$fila->detalle_operacion.'
                            </td>
                            <td>
                               $'.number_format($valor, 0, ',', '.').'
                            </td>
                        </tr>';

            $tabla2.='</table>';
        }


        $elaboro = $encabezado_doc->creado_por;
        $empresa = Empresa::find($encabezado_doc->core_empresa_id);
        $tercero = Tercero::datos_completos($encabezado_doc->core_tercero_id);

        $view_1 = View::make('tesoreria.incluir.encabezado_transaccion',compact('encabezado_doc','descripcion_transaccion','empresa','vista','tercero') )->render();

        $view_2 = View::make('tesoreria.incluir.firmas',compact('elaboro') )->render();


        $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla1.$tabla2.$view_2;


        return $view_pdf;
    }

    public static function get_medios_recaudo(){
        $registros = TesoMedioRecaudo::all();  
        $vec_m['']=''; 
        foreach ($registros as $fila) {
            $vec_m[$fila->id.'-'.$fila->comportamiento]=$fila->descripcion; 
        }
        
        return $vec_m;
    }

    public static function get_cajas(){
        $vec_m = [];
        $registros = TesoCaja::where([
            ['core_empresa_id', '=', Auth::user()->empresa_id],
            ['estado', '=', 'Activo']
            ])
            ->get();       
        foreach ($registros as $fila) {
            $vec_m[$fila->id]=$fila->descripcion; 
        }
        
        return $vec_m;
    }

    public static function get_cuentas_bancarias(){
        $vec_m = [];
        $registros = TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                    ->where([
                            ['teso_cuentas_bancarias.core_empresa_id', '=', Auth::user()->empresa_id],
                            ['teso_cuentas_bancarias.estado', '=', 'Activo']
                        ])
                        ->select('teso_cuentas_bancarias.id','teso_cuentas_bancarias.descripcion AS cta_bancaria','teso_entidades_financieras.descripcion AS entidad_financiera')
                    ->get();        
        foreach ($registros as $fila) {
            $vec_m[$fila->id] = $fila->entidad_financiera.': '.$fila->cta_bancaria; 
        }
        
        return $vec_m;
    }

    /**
     *      Anular un recaudo (distinto a recaudo de cxc)
     */
    public function anular_recaudo($id)
    {
        $documento = TesoDocEncabezado::find($id);
        $modificado_por = Auth::user()->email;

        $array_wheres = ['core_empresa_id'=>$documento->core_empresa_id, 
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo];

        // >>> Validaciones inciales
        $tabla_existe = DB::select( DB::raw( "SHOW TABLES LIKE 'cxc_abonos'" ) );
        if ( !empty( $tabla_existe ) )
        {
            // Está en un documento cruce de cartera?
            $cantidad = CxcAbono::where($array_wheres)
                                ->where('doc_cruce_transacc_id','<>',0)
                                ->count();

            if($cantidad != 0)
            {
                return redirect( 'tesoreria/recaudos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Recaudo NO puede ser anulado. Está en documento cruce de cartera.');
            }
        }

        // >>> Eliminación

        // 1ro. Borrar registros contables
        ContabMovimiento::where($array_wheres)->delete();

        // 2do. Se elimina el movimimeto de cartera (CxC o CxP)
        CxcMovimiento::where($array_wheres)->delete();
        CxpMovimiento::where($array_wheres)->delete();


        // 3ro. Se elimina el movimiento de tesorería
        TesoMovimiento::where($array_wheres)->delete();

        // 5to. Se eliminan los registros del documento
        TesoDocRegistro::where( 'teso_encabezado_id', $documento->id )->update( [ 'estado' => 'Anulado'] );

        // 4to. Se elimina el documento de cruce
        $documento->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

      return redirect( 'tesoreria/recaudos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Documento de recaudo anulado correctamente.');
    }

    /*
        Enviar por email
    */
    public function enviar_por_email( $id )
    {
        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        $documento_vista = $this->generar_documento_vista_print($doc_encabezado, Input::get('formato_impresion_id'));

        $tercero = Tercero::find( $doc_encabezado->core_tercero_id );

        $asunto = $doc_encabezado->documento_transaccion_descripcion.' No. '.$doc_encabezado->documento_transaccion_prefijo_consecutivo;

        $cuerpo_mensaje = 'Saludos, <br/> Le hacemos llegar su '. $asunto;

        $email_destino = $tercero->email;
        if ( $doc_encabezado->contacto_cliente_id != 0 )
        {
            $email_destino = $doc_encabezado->contacto_cliente->tercero->email;
        }

        $vec = EmailController::enviar_por_email_documento( $doc_encabezado->empresa->descripcion, $email_destino, $asunto, $cuerpo_mensaje, $documento_vista );

        return redirect( 'tesoreria/recaudos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( $vec['tipo_mensaje'], $vec['texto_mensaje'] );
    }
}