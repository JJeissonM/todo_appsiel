<?php

namespace App\Http\Controllers\CxC;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use View;
use Lava;
use Input;
use NumerosEnLetras;


use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Sistema\ModeloController;


// Modelos
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;

use App\Matriculas\Grado;
use App\Matriculas\Estudiante;
use App\Core\Colegio;
use App\Core\Empresa;


use App\CxC\CxcMovimiento;
use App\CxC\CxcDocEncabezado;
use App\CxC\CxcEstadoCartera;

use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoRecaudosLibreta;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoEntidadFinanciera;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoDocEncabezadoRecaudo;
use App\Tesoreria\TesoDocRegistroRecaudo;
use App\Tesoreria\TesoMovimiento;

use App\PropiedadHorizontal\Propiedad;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabCuenta;

class DocCancelacionController extends Controller
{
    protected $datos = [];

    public function __construct()
    {
        $this->middleware('auth');
    }

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
     * Muestra formulario para crear Documento de Cancelación/Anticipo.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $id_transaccion = 18;// 18 = Cancelaciones y anticipos

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

        $miga_pan = [
                ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=>'CxC'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Crear nuevo' ]
            ];

        return view( 'cxc.cancelacion_anticipo_create', compact( 'form_create','id_transaccion','miga_pan' ) );
    }

    /**
     * Store a newly created resource in storage.
     * // Este método es llamado desde ModeloController@store
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response  
     */
    public function store(Request $request,$encabezado_doc_creado)
    {
        // Ya se llenó la tabla *_doc_encabezados* en el ModeloController

        $this->datos = array_merge( $request->all(), 
                    [ 
                        'consecutivo' => $encabezado_doc_creado->consecutivo 
                    ] );

        $tipo_transaccion = TipoTransaccion::find( $request->core_tipo_transaccion_id );
          
          // Si hay datos en la tabla de documentos a cancelar
        // Es decir, el recaudo es un abono de cartera
        if ( $request->tabla_documentos_a_cancelar != 'No') 
        {

            $tabla_documentos_a_cancelar = json_decode($request->tabla_documentos_a_cancelar);

            // 2do. Se Crea un nuevo Estado de catera para cada documento cancelado (cxc_movimientos)
            // Se recorre la tabla enviada en el request, descartando las dos últimas filas
            for ($i=0; $i < count($tabla_documentos_a_cancelar)-2; $i++) 
            { 
                
                // La tabla solo tiene documentos del movimiento de cartera
                // La primera columna de la tabla corresponde al ID del movimiento de cartera del documento que se va a cancelar
                $cxc_movimiento_id = $tabla_documentos_a_cancelar[$i]->cxc_movimiento_id;
                
                // Se Crea un nuevo Estado de catera, con base en el último estado del mismo movimiento
                CxcEstadoCartera::obtener_y_crear($cxc_movimiento_id, $tabla_documentos_a_cancelar[$i]->valor_aplicar, $request->fecha, $request->creado_por, $request->modificado_por);

                // 2.1. A cada documento se le va registrando el movimiento de los recaudos que se le han realizado (cxc_documento_tiene_recaudos)
                $email_usuario = Auth::user()->email;


                // $doc_recaudo_id corresponde al documento con que se cancela (paga) la cartera, en este caso es un documento de cancelación (el que se está creando); también puede ser un documento de tesoreria.
                $doc_recaudo_id = $encabezado_doc_creado->id;

                $transaccion_origen_doc_recaudo_id = $encabezado_doc_creado->core_tipo_transaccion_id;

                DB::table('cxc_documento_tiene_recaudos')->insert( [ 
                                'fecha_registro' => $request->fecha ,
                                'cxc_doc_cruce_id' => 0,
                                'cxc_movimiento_id' =>  $cxc_movimiento_id ,
                                'doc_recaudo_id' =>  $doc_recaudo_id ,
                                'transaccion_origen_doc_recaudo_id' =>  $transaccion_origen_doc_recaudo_id ,
                                'valor_pagado' =>  $tabla_documentos_a_cancelar[$i]->valor_aplicar,
                                'creado_por' =>  $email_usuario ,
                                'modificado_por' =>  $email_usuario 
                            ] );

                $detalle_operacion = $request->descripcion;
                
                // Se crea el movimiento de la cancelación/pago del documento
                $cxc_movimiento = CxcMovimiento::crear( $this->datos, ($tabla_documentos_a_cancelar[$i]->valor_aplicar *-1), 'Pagado', $detalle_operacion );

                /*
                    **  Se contabiliza 
                    DB 1105 = caja general
                */

                // DEBITO: CAJA GENERAL
                //ADVERTENCIA, ADVERTENCIA Esto debe ser automatizado
                // el debito lo debe decidir el usuario (caja o cuenta contable, como el aplicar/crear de SIESA)

                $cuenta_id = 1;

                $valor_debito = $valor_aplicar;
                $valor_credito = 0;

                $this->contabilizar_registro( $cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

                /*
                    **  Determinar la cuenta contable (CARTERA (CR) )
                    ** Esta debe ser la misma cuenta que se generó en el movimiento de cartera
                */

                $cuenta_id = ContabMovimiento::where('core_tipo_transaccion_id',$cxc_movimiento->core_tipo_transaccion_id)
                                    ->where('core_tipo_doc_app_id', $cxc_movimiento->core_tipo_doc_app_id)
                                    ->where('consecutivo', $cxc_movimiento->consecutivo)
                                    ->where('core_empresa_id',Auth::user()->empresa_id)
                                    ->where('valor_credito', 0)
                                ->value('contab_cuenta_id');

                $valor_debito = 0;
                $valor_credito = $valor_aplicar;

                $this->contabilizar_registro( $cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

            }
        }else{

            // Si se trata de un ANTICIPO, se registra el recaudo/doc_cancelacion como un nuevo documento en el movimiento de cartera
            $valor_cartera = $encabezado_doc_creado->valor_total * -1;

            $detalle_operacion = $request->descripcion;

            $cxc_movimiento = CxcMovimiento::crear( $this->datos, $valor_cartera, 'Pendiente', $detalle_operacion );

            // También se agrega el movimiento al estado de cartera
            CxcEstadoCartera::crear($cxc_movimiento->id, $request->fecha, 0, $valor_cartera, 'Pendiente', $request->creado_por, $request->modificado_por);

            /*
                **  Se contabiliza 
                DB 1105 = caja genera
                CR 2805 = Anticipos
            */

            // DEBITO: CAJA GENERAL
            $cuenta_id = 1; //////// ADVERTENCIA, ADVERTENCIA Esto debe ser automatizado

            $valor_debito = $encabezado_doc_creado->valor_total;
            $valor_credito = 0;

            $this->contabilizar_registro( $cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

            // CTA. CREDITO LA DA EL TERCERO
            $cuenta_id = Tercero::find($request->core_tercero_id)->contab_anticipo_cta_id;

            $valor_debito = 0;
            $valor_credito = $encabezado_doc_creado->valor_total;

            $this->contabilizar_registro( $cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);
            
        }

        // se llama la vista de DocCruceController@show
        return redirect( 'cancelacion_anticipo/'.$encabezado_doc_creado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo );
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

        $reg_anterior = CxcDocEncabezado::where('id', '<', $id)->where('core_tipo_transaccion_id', 18 )->where('core_empresa_id', Auth::user()->empresa_id)->max('id');
        $reg_siguiente = CxcDocEncabezado::where('id', '>', $id)->where('core_tipo_transaccion_id', 18 )->where('core_empresa_id', Auth::user()->empresa_id)->min('id');

        $view_pdf = $this->vista_preliminar_doc_cancelacion($id,'show');

        $miga_pan = [
                ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=>'CxC'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Consulta' ]
            ];

        return view( 'cxc.documento_cancelacion.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') );
    }

      // Generar vista para SHOW  o IMPRIMIR
      public function vista_preliminar_doc_cancelacion($id, $vista)
      {
        $encabezado_doc = CxcDocEncabezado::get_un_registro($id);

        if ( $encabezado_doc->tipo_movimiento == 'Cancelación documentos') {
            // Se crear una tabla con los documentos pagados por el recaudo

            $documentos_pagados = DB::table('cxc_documento_tiene_recaudos')
                            ->where('doc_recaudo_id', $id )
                            ->get();

            $i=0;
            $tabla2 = '<table  class="tabla_registros" style="margin-top: -4px;">
                            <tr>
                                <td colspan="3" align="center">
                                   <b>Detalle de documentos cancelados</b>
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
                $i++;
                if ($i==3) {
                    $i=1;
                }
            }


            // Revisar si creó algún anticipo en el documento de Recaudo de cartera
            $valor_cartera = CxCMovimiento::where([
                            'core_tipo_transaccion_id' => $encabezado_doc->core_tipo_transaccion_id,
                            'core_tipo_doc_app_id' => $encabezado_doc->core_tipo_doc_app_id,
                             'consecutivo' => $encabezado_doc->consecutivo 
                            ])->value('valor_cartera');
            
            if ( $valor_cartera < 0) {

                $tabla2.='<tr  class="fila-'.$i.'" >
                                <td>
                                   '.$encabezado_doc->documento.'
                                </td>
                                <td>
                                   Anticipo/Saldo a favor
                                </td>
                                <td>
                                   $'.number_format($valor_cartera * -1, 0, ',', '.').'
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

                }

            $tabla2.='<tr>
                            <td>
                               Anticipo/Saldo a favor '.$fila->detalle_operacion.'
                            </td>
                            <td>
                               $'.number_format($valor, 0, ',', '.').'
                            </td>
                        </tr>';

            $tabla2.='</table>';
        }


        // DATOS ADICIONALES

        $tipo_doc_app = TipoDocApp::find($encabezado_doc->core_tipo_doc_app_id);
        $descripcion_transaccion = $tipo_doc_app->descripcion;

        $elaboro = $encabezado_doc->creado_por;
        $empresa = Empresa::find($encabezado_doc->core_empresa_id);
        $ciudad = DB::table('core_ciudades')
                ->where('id','=',$empresa->codigo_ciudad)
                ->value('descripcion');

        $firmas = View::make('common.firmas_elaboro_reviso',compact('elaboro') )->render();

        $total_1 = 0;
                
        $view_1 = View::make('cxc.incluir.encabezado_transaccion',compact('encabezado_doc','descripcion_transaccion','empresa','vista','ciudad','total_1') )->render();

        $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla2.$firmas.'<div class="page-break"></div>';
        
        return $view_pdf;
    }


    public function cancelacion_anticipo_print($id)
    {
      $view_pdf = $this->vista_preliminar_doc_cancelacion($id,'imprimir');
       
      // Se prepara el PDF
      $orientacion='portrait';
      $tam_hoja='Letter';

      $pdf = \App::make('dompdf.wrapper');
      //$pdf->set_option('isRemoteEnabled', TRUE);
      $pdf->loadHTML( $view_pdf )->setPaper($tam_hoja,$orientacion);

      //echo $view_pdf;
      return $pdf->download('doc_cancelacion_anticipo.pdf');
    }

    function contabilizar_registro($contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cuenta_bancaria_id = 0)
    {
        ContabMovimiento::create( $this->datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id]
                        );
    }

}