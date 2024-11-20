<?php

namespace App\Http\Controllers\CxC;

use Illuminate\Http\Request;


use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;

use App\Ventas\VtasDocEncabezado;
use App\Matriculas\FacturaAuxEstudiante;

use App\CxC\CxcMovimiento;
use App\CxC\CxcDocEncabezado;
use App\CxC\CxcAbono;
use App\CxC\Services\AccountingServices;

use App\Tesoreria\TesoRecaudosLibreta;
use App\Tesoreria\TesoDocEncabezadoRecaudo;

use App\Contabilidad\ContabMovimiento;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class DocCruceController extends TransaccionController
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
        $id_transaccion = 16;// 16 = Cruce de documentos

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

        return view( 'cxc.documento_cruce.create', compact( 'form_create','id_transaccion','miga_pan' ) );
    }

    /**
     * Store a newly created resource in storage.
     * // Este método es llamado desde ModeloController@store
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $obj_accou_serv = new AccountingServices();
      $cxc_move_note_id = $obj_accou_serv->create_accounting_note_doc( $request->tabla_documentos_a_cancelar, $request->fecha, $request->core_tercero_id );

      $doc_encabezado = $this->crear_encabezado_documento($request, $request->url_id_modelo);

      // esta tabla contiene documentos de cartera y de saldo_a_favor
      $tabla_documentos_a_cancelar = json_decode( $request->tabla_documentos_a_cancelar );

      // Se recorre la tabla enviada en el request, descartando la última fila
      // En este recorrido se va actualizando la tabla cxc_movimientos, el movimiento contable y se crean dos arrays: $vector_cartera y $vector_afavor con estos dos arrays luego se crearan registros en la tabla cxc_abonos
      $j = 0;
      $valor_total = 0;
      $cant = count( $tabla_documentos_a_cancelar ) - 1;
      for ($i=0; $i < $cant; $i++) 
      {
            $cxc_movimiento_id = (int)$tabla_documentos_a_cancelar[$i]->cxc_movimiento_id;
            if ( $cxc_movimiento_id == -1 )
            {
              $cxc_movimiento_id = $cxc_move_note_id;
            }

            $valor_aplicar = (float)$tabla_documentos_a_cancelar[$i]->valor_aplicar;

            // Se llenan dos array de acuerdo al tipo de documento cartera o afavor
            // Luego se ejecutará un proceso, pues a un documento de cartera lo pueden afectar varios documentos afavor
            if ($valor_aplicar > 0) 
            {
              $vector_cartera[ $cxc_movimiento_id ] = $valor_aplicar;
              $valor_total += $valor_aplicar;
            }else{                  
                $vector_afavor[ $j ][ 'cxc_movimiento_id' ] = $cxc_movimiento_id;
                $vector_afavor[ $j ][ 'valor_aplicar' ] = $valor_aplicar * -1; // se pasa a positivo
                $j++;
            }

          /* LA CONTABILIDAD se almacena al momento de crear los abonos */       
          
      }

      $doc_encabezado->valor_total = $valor_total;
      $doc_encabezado->save();

      $this->datos = array_merge( $request->all(), [ 'consecutivo' => $doc_encabezado->consecutivo ] );

      //dd( [$vector_cartera,$vector_afavor] );

      // Se crean los registros que relacionan cada documento de cartera con el recaudo por el cual fue cancelado
      DocCruceController::creacion_abonos_cxc($doc_encabezado, $vector_cartera, $vector_afavor);

      // se llama la vista de DocCruceController@show
      return redirect( 'doc_cruce/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }


    // Esta funcion crea registros en la tabla cxc_abonos para cruzar todos los documentos de cartera con los documentos a favor (notas y anticipos) seleccionados
    public function creacion_abonos_cxc($doc_encabezado, $vector_cartera, $vector_afavor)
    {
      $detalle_operacion = $doc_encabezado->descripcion;

      // Se recorre el vector de carteras (cxc_movimiento_id y valor_aplicar)
      foreach ($vector_cartera as $key_cartera => $value_cartera) 
      {
        $movimiento_cartera = CxcMovimiento::find( $key_cartera );
        $valor_cxc = $value_cartera;

        // Por cada documento de cartera se recorre el vector_afavor
        // y se va TOMANDO el valor que tiene cada item del vector_afavor
        // para aplicar a cada documento de cartera, luego se va disminuyendo el valor 
        // TOMADO para que ya no esté disponible.
        $cant = count($vector_afavor);
        for($j=0; $j < $cant; $j++) 
        {
          $valor_afavor = $vector_afavor[$j]['valor_aplicar'];

          if ( $valor_afavor !=0 ) 
          {
            $movimiento_afavor = CxcMovimiento::find( $vector_afavor[$j]['cxc_movimiento_id'] );

            // Si el item del valor de recaudo puede pagar todo el documento de cartera
            if ( $valor_afavor >= $valor_cxc ) 
            {
              $this->crear_transacciones_bd( $doc_encabezado, $movimiento_cartera, $movimiento_afavor, $valor_cxc, $detalle_operacion );

              // Se disminuye el valor del item afavor 
              $vector_afavor[$j]['valor_aplicar'] = $vector_afavor[$j]['valor_aplicar'] - $valor_cxc;

              // El item de cartera se deja en cero pues se pagó todo
              $vector_cartera[$key_cartera] = 0;

              // Se termina el recorrido de los documentos a favor y paso al siguiente registro de cartera
              break;

            }else{ // El documento de cartera se debe pagar con varios documentos de recaudos

              $this->crear_transacciones_bd( $doc_encabezado, $movimiento_cartera, $movimiento_afavor, $valor_afavor, $detalle_operacion );

              // Se disminuye el valor del item de cartera
              $valor_cxc = $valor_cxc - $valor_afavor;//2.000

              // El item del recaudo se deja en cero pues se usó todo para pagar
              // el documento de cartera
              $vector_afavor[$j]['valor_aplicar'] = 0; 
            }
          } // Fin si valor_aplicar != 0
        } // Fin FOR cada doc de saldo_a_favor
      }// Fin for cada doc de cartera
    }

    public function crear_transacciones_bd( $doc_encabezado, $movimiento_cartera, $movimiento_afavor, $valor_abono, $detalle_operacion )
    {
      $this->almacenar_abono_cxc($doc_encabezado, $movimiento_cartera, $movimiento_afavor, $valor_abono);

      // disminuir saldo pendiente y actualizar estados en los movimientos de cxc
      CxcMovimiento::actualizar_valores_doc_cxc( $movimiento_cartera, $valor_abono );
      CxcMovimiento::actualizar_valores_doc_cxc( $movimiento_afavor, $valor_abono * -1); // negativo

      $this->contabilizar_debito( $movimiento_afavor, $valor_abono, $detalle_operacion);
      $this->contabilizar_credito( $movimiento_cartera, $valor_abono, $detalle_operacion);

      // Para facturas de Libretas de Pago
      if ( Schema::hasTable( 'sga_facturas_estudiantes' ) )
      {
        $doc_encabezado_recaudo = TesoDocEncabezadoRecaudo::where([
                                                                    [ 'core_tipo_transaccion_id','=', $movimiento_afavor->core_tipo_transaccion_id ],
                                                                    [ 'core_tipo_doc_app_id','=', $movimiento_afavor->core_tipo_doc_app_id ],
                                                                    [ 'consecutivo','=', $movimiento_afavor->consecutivo ]
                                                                ])->get()->first();
        if ( is_null( $doc_encabezado_recaudo) )
        {
          return 0;
        }
        
        $this->registrar_recaudo_cartera_estudiante( $doc_encabezado_recaudo, $movimiento_cartera, $valor_abono );
      }

    }

    // La fecha del abono se registra con la fecha del documento que se está creando ( Cruce, Recaudo, Nota crédito, etc. )
    public function almacenar_abono_cxc($doc_encabezado, $movimiento_cartera, $movimiento_afavor, $abono)
    {
      // Almacenar registro de abono
      $datos = ['core_tipo_transaccion_id' => $movimiento_afavor->core_tipo_transaccion_id]+
                  ['core_tipo_doc_app_id' => $movimiento_afavor->core_tipo_doc_app_id]+
                  ['consecutivo' => $movimiento_afavor->consecutivo]+
                  ['fecha' => $doc_encabezado->fecha]+
                  ['core_empresa_id' => $movimiento_afavor->core_empresa_id]+
                  ['core_tercero_id' => $movimiento_afavor->core_tercero_id]+
                  ['modelo_referencia_tercero_index' => $movimiento_cartera->modelo_referencia_tercero_index]+
                  ['referencia_tercero_id' => $movimiento_cartera->referencia_tercero_id]+
                  ['doc_cxc_transacc_id' => $movimiento_cartera->core_tipo_transaccion_id]+
                  ['doc_cxc_tipo_doc_id' => $movimiento_cartera->core_tipo_doc_app_id]+
                  ['doc_cxc_consecutivo' => $movimiento_cartera->consecutivo]+
                  ['doc_cruce_transacc_id' => $doc_encabezado->core_tipo_transaccion_id]+
                  ['doc_cruce_tipo_doc_id' => $doc_encabezado->core_tipo_doc_app_id]+
                  ['doc_cruce_consecutivo' => $doc_encabezado->consecutivo]+
                  ['abono' => $abono]+
                  ['creado_por' => $doc_encabezado->creado_por];

      CxcAbono::create( $datos );
    }

    public function contabilizar_debito( $movimiento_afavor, $valor_aplicar, $detalle_operacion)
    {
      // Contabilizar MOVIMIENTO DEBITO (AFAVOR)
      $array_wheres = [
                        'core_empresa_id' => $movimiento_afavor->core_empresa_id, 
                        'core_tipo_transaccion_id' => $movimiento_afavor->core_tipo_transaccion_id,
                        'core_tipo_doc_app_id' => $movimiento_afavor->core_tipo_doc_app_id,
                        'consecutivo' => $movimiento_afavor->consecutivo,
                        'core_tercero_id' => $movimiento_afavor->core_tercero_id
                      ];
      $contab_cuenta_id = ContabMovimiento::where($array_wheres)->where( 'valor_debito', 0 )->value('contab_cuenta_id');
      if ($contab_cuenta_id == null) {
        $contab_cuenta_id = (int)config('contabilidad.cta_anticipo_clientes_default');
      }
      $valor_debito = $valor_aplicar;      
      $valor_credito = 0;          
      $this->contabilizar_registro( $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);
    }

    public function contabilizar_credito( $movimiento_cartera, $valor_aplicar, $detalle_operacion)
    {
      // Contabilizar MOVIMIENTO CREDITO (CARTERA)
      $array_wheres = [
                      'core_empresa_id' => $movimiento_cartera->core_empresa_id, 
                      'core_tipo_transaccion_id' => $movimiento_cartera->core_tipo_transaccion_id,
                      'core_tipo_doc_app_id' => $movimiento_cartera->core_tipo_doc_app_id,
                      'consecutivo' => $movimiento_cartera->consecutivo,
                      'core_tercero_id' => $movimiento_cartera->core_tercero_id
                    ];
      $contab_cuenta_id = ContabMovimiento::where($array_wheres)->where( 'valor_credito', 0 )->value('contab_cuenta_id');
      if ($contab_cuenta_id == null) {
        $contab_cuenta_id = (int)config('contabilidad.cta_cartera_default');
      }
      $valor_debito = 0;
      $valor_credito = $valor_aplicar;
      $this->contabilizar_registro( $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);
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
      $id_transaccion = $this->transaccion->id;
      $empresa = $this->empresa;

      $doc_encabezado = CxcDocEncabezado::get_registro_impresion( $id );
      $documento_vista = $this->vista_preliminar_doc_cruce($doc_encabezado);

      $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

      $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

      return view( 'cxc.documento_cruce.show',compact('botones_anterior_siguiente','doc_encabezado','miga_pan','id_transaccion','empresa','id','documento_vista','registros_contabilidad') );
        
    }

      // Generar vista para SHOW o IMPRIMIR
      public function vista_preliminar_doc_cruce($doc_encabezado)
      {        
        $registros_cruce = DB::table('cxc_abonos')
                          ->where('doc_cruce_transacc_id', $doc_encabezado->core_tipo_transaccion_id )
                          ->where('doc_cruce_tipo_doc_id', $doc_encabezado->core_tipo_doc_app_id )
                          ->where('doc_cruce_consecutivo', $doc_encabezado->consecutivo )
                          ->where('core_tercero_id', $doc_encabezado->core_tercero_id )
                          ->get();

                          //dd( $registros_cruce );

        $i = 0;
        $registros = [];
        foreach ($registros_cruce as $registro) 
        {
          // DOC CARTERA
          $array_wheres_cartera = [
            'core_tipo_transaccion_id' => $registro->doc_cxc_transacc_id, 
            'core_tipo_doc_app_id' => $registro->doc_cxc_tipo_doc_id, 
            'consecutivo' => $registro->doc_cxc_consecutivo
          ];

          if ( $registro->core_tipo_transaccion_id != 14 ) // Doc. Nomina
          {
            $array_wheres_cartera = array_merge( $array_wheres_cartera, [ 'core_tercero_id' => $registro->core_tercero_id ] );
          }
          $transaccion = TipoTransaccion::find( $registro->doc_cxc_transacc_id );
          $doc_cartera = app( $transaccion->modelo_encabezados_documentos )
                          ->where( $array_wheres_cartera )
                          ->get()
                          ->first();
            
          $doc_app_cartera = 'nulo';
          $fecha_doc_app_cartera = '';
          if($doc_cartera != null)
          {
              $doc_app_cartera = TipoDocApp::where( 'id', $doc_cartera->core_tipo_doc_app_id )->value('prefijo').' '.$doc_cartera->consecutivo;
              $fecha_doc_app_cartera = $doc_cartera->fecha;
          }

          // DOC RECAUDO
          $array_wheres_recaudos = [
            'core_tipo_transaccion_id' => $registro->core_tipo_transaccion_id, 
            'core_tipo_doc_app_id' => $registro->core_tipo_doc_app_id, 
            'consecutivo' => $registro->consecutivo
          ];

          if ( $registro->core_tipo_transaccion_id != 14 ) // Doc. Nomina
          {
            $array_wheres_recaudos = array_merge( $array_wheres_recaudos, [ 'core_tercero_id' => $registro->core_tercero_id ] );
          }

          $transaccion = TipoTransaccion::find( $registro->core_tipo_transaccion_id );
          $doc_recaudo = app( $transaccion->modelo_encabezados_documentos )
                          ->where( $array_wheres_recaudos )
                          ->get()
                          ->first();
          $doc_app_recaudo = TipoDocApp::where('id', $doc_recaudo->core_tipo_doc_app_id)->value('prefijo').' '.$doc_recaudo->consecutivo;

          $registros[$i]['cartera'] = [ $doc_app_cartera, $fecha_doc_app_cartera ];
          $registros[$i]['recaudo'] = [ $doc_app_recaudo, $doc_recaudo->fecha ];
          $registros[$i]['valor_pagado'] = $registro->abono;
          $i++;
        }

        return View::make('cxc.documento_cruce.documento_vista', compact('registros') )->render();
    }


    public function imprimir($id)
    {
      $this->set_variables_globales();

      $id_transaccion = $this->transaccion->id;
      $empresa = $this->empresa;

      $doc_encabezado = CxcDocEncabezado::get_registro_impresion( $id );
      $documento_vista = $this->vista_preliminar_doc_cruce($doc_encabezado);

      $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

      $view_pdf = View::make( 'cxc.documento_cruce.formatos_impresion.'.Input::get('formato_impresion_id'), compact( 'doc_encabezado', 'documento_vista', 'registros_contabilidad', 'empresa', 'id_transaccion' ) )->render();
       
      // Se prepara el PDF
      $orientacion='portrait';
      $tam_hoja='Letter';

      $pdf = App::make('dompdf.wrapper');
      //$pdf->set_option('isRemoteEnabled', TRUE);
      $pdf->loadHTML( $view_pdf )->setPaper($tam_hoja,$orientacion);

      //echo $view_pdf;
      return $pdf->stream('documento_cruce.pdf');
    }


    // Obtiene el movimiento de cartera de un documento según su ID de transacción y el ID del encabezado, puede ser un documento de cartera, recaudo, etc.
    function get_cxc_movimiento_documento($transaccion_id, $encabezado_documento_id)
    {
      $documento = $this->get_encabezado_documento($transaccion_id, $encabezado_documento_id);

      return CxcMovimiento::where('core_empresa_id',$documento->core_empresa_id)
          ->where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
          ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
          ->where('consecutivo', $documento->consecutivo)
          ->where('core_tercero_id', $documento->core_tercero_id)
          ->get()[0];
    }


    // Obtiene el registro del encabezado de un documento según su ID de transacción y el ID del encabezado
    function get_encabezado_documento($transaccion_id, $encabezado_documento_id)
    {
      $transaccion = TipoTransaccion::find($transaccion_id);
      
      return app($transaccion->modelo_encabezados_documentos)->find($encabezado_documento_id);
    }    

    // AJAX Se obtiene la cartera positiva y negativa del tercero
    public function get_cartera_tercero($tercero_id, $fecha_doc)
    {
      // 1ro. Buscar documentos de cartera
      $movimiento_cxc = CxcMovimiento::get_documentos_tercero($tercero_id, $fecha_doc);
      //dd($movimiento_cxc);
      $view_1 = View::make('cxc.incluir.docs_cruce_cartera', compact('movimiento_cxc') );

      $vista = 'formulario';
      $view_2 = View::make('cxc.incluir.docs_cruce_afavor', compact('movimiento_cxc','vista') );

      $resultado = $view_1.'a3p0'.$view_2;

      return $resultado;
    }
    
    public function revisar_anticipos($tercero_id, $fecha_doc = null)
    {
      if ($fecha_doc == null) {
        $fecha_doc = date('Y-m-d');
      }

      // 1ro. Buscar documentos de cartera
      $movimiento_cxc = CxcMovimiento::get_documentos_tercero($tercero_id, $fecha_doc);
      
      $vista = 'show';
      return View::make('cxc.incluir.docs_cruce_afavor', compact('movimiento_cxc','vista') );
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

    // ANULAR DOC DE CRUCE
    public function anular_doc_cruce($id)
    {      
      $documento = CxcDocEncabezado::find($id);

      // 1ro. Borrar registros contables
      $obj_accou_serv = new AccountingServices();
      $obj_accou_serv->delete_accounting_move( $documento->core_empresa_id, $documento->core_tipo_transaccion_id, $documento->core_tipo_doc_app_id, $documento->consecutivo );

      // 2do. Por cada abono, se reversa el valor que el doc. cruce descontó en el movimiento cartera y se elimina el abono
      $array_wheres = [
                      'core_empresa_id' => $documento->core_empresa_id, 
                      'core_tercero_id' => $documento->core_tercero_id, 
                      'doc_cruce_transacc_id' => $documento->core_tipo_transaccion_id,
                      'doc_cruce_tipo_doc_id' => $documento->core_tipo_doc_app_id,
                      'doc_cruce_consecutivo' => $documento->consecutivo
                    ];
      $documentos_abonados = CxcAbono::where($array_wheres)->get();
      // En un documento cruce se actualizan los movimientos de cartera tanto por el documento de cartera, como por el documento de recaudo
      foreach ($documentos_abonados as $linea)
      {

        // Se debe actualizar el movimiento de cxc tanto para la cartera como para el documento a favor
        // Se verifica si cada documento abonado aún tiene saldo pendiente por pagar
        $mov_documento_cartera = CxcMovimiento::where('core_tipo_transaccion_id', $linea->doc_cxc_transacc_id)
                                          ->where('core_tipo_doc_app_id', $linea->doc_cxc_tipo_doc_id)
                                          ->where('consecutivo', $linea->doc_cxc_consecutivo)
                                          ->where('core_tercero_id', $linea->core_tercero_id)
                                          ->get()
                                          ->first();
      
        $this->actualizar_mov_cxc( $mov_documento_cartera, $linea, 'cartera', $linea->abono );

        // Para el docuento a favor del abono
        // Se verifica si cada documento abonado aún tiene saldo pendiente por pagar
        $mov_documento_afavor = CxcMovimiento::where('core_tipo_transaccion_id', $linea->core_tipo_transaccion_id)
                                                    ->where('core_tipo_doc_app_id', $linea->core_tipo_doc_app_id)
                                                    ->where('consecutivo', $linea->consecutivo)
                                                    ->where('core_tercero_id', $linea->core_tercero_id)
                                                    ->get()
                                                    ->first();

        $this->actualizar_mov_cxc( $mov_documento_afavor, $linea, 'afavor', $linea->abono ); // Para saldo afavor el movimiento es negativo, los abonos también deben ser negativos

        $this->anular_recaudo_cartera_estudiante( $mov_documento_afavor, $linea->abono );

        $obj_accou_serv->anular_nota_contable_ajuste( $linea );

        // Se elimina el abono
        $linea->delete();
      }

      // 3ro. Se marca como anulado el encabezado del documento cruce
      $documento->update( [ 'estado' => 'Anulado', 'modificado_por' => Auth::user()->email ] );

      return redirect( 'doc_cruce/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Documento de cruce anulado correctamente.');
    }

    public function anular_recaudo_cartera_estudiante( $movimiento_afavor, $abono )
    { 
      if ($movimiento_afavor == null ) {
        return 0;
      }
         
      // Si es el Recaudo de una o varias facturas asociadas a un registro de Plan de Pagos de una libreta de pagos
      $recaudo_libreta = TesoRecaudosLibreta::where([
                                                  ['core_tipo_transaccion_id','=',$movimiento_afavor->core_tipo_transaccion_id],
                                                  ['core_tipo_doc_app_id','=',$movimiento_afavor->core_tipo_doc_app_id],
                                                  ['consecutivo','=',$movimiento_afavor->consecutivo],
                                                  ['valor_recaudo','=',$abono]
                                              ])->get()->first();
      if( !is_null($recaudo_libreta) )
      {
          $recaudo_libreta->anular();
      }
    }

    // valor_abono siempre es positivo
    public function actualizar_mov_cxc( $linea_movimiento, $linea_abono, $tipo_movimiento, $valor_abono )
    {
      $valor_abonos_aplicados = 0;

      if ($linea_movimiento == null) {
        return 0;
      }

      if ( $linea_movimiento->estado == 'Pagado' )
        {
            // Se halla el total de todos los pagos que halla tenido (incluido el abono realizado por este pago)
            // Ahi que diferenciar por el tercero
          if ( $tipo_movimiento == 'cartera')
          {
            // Otro abonos realizados SOBRE el documento de cartera
            $valor_abonos_aplicados = CxcAbono::where('doc_cxc_transacc_id',$linea_abono->doc_cxc_transacc_id)
                                              ->where('doc_cxc_tipo_doc_id',$linea_abono->doc_cxc_tipo_doc_id)
                                              ->where('doc_cxc_consecutivo',$linea_abono->doc_cxc_consecutivo)
                                              ->where('core_tercero_id',$linea_abono->core_tercero_id)
                                              ->sum('abono');

            //      $75.000        =          $90.000                   -         $20.360         +      $5.360
            $nuevo_saldo_pendiente = $linea_movimiento->valor_documento - $valor_abonos_aplicados + $valor_abono;
            //      $15.000     =           $20.360       -     $5.360
            $nuevo_valor_pagado = $valor_abonos_aplicados - $valor_abono; // el valor_abonos_aplicados es como mínimo el valor de $valor_abono
          }else{
            // Otro abonos realizados POR el documento afavor, restando el del abono actual
            //      $0              =         $5.360          -       $5.360
            $valor_abonos_aplicados = CxcAbono::where('core_tipo_transaccion_id',$linea_abono->core_tipo_transaccion_id)
                                            ->where('core_tipo_doc_app_id',$linea_abono->core_tipo_doc_app_id)
                                            ->where('consecutivo',$linea_abono->consecutivo)
                                            ->where('core_tercero_id',$linea_abono->core_tercero_id)
                                            ->sum('abono') - $valor_abono;
            //        5.360        =             5.360                   +         0 
            $nuevo_saldo_pendiente = $linea_movimiento->valor_documento + $valor_abonos_aplicados;
            $nuevo_valor_pagado = $valor_abonos_aplicados * -1; // el valor_abonos_aplicados es como mínimo el valor de $valor_abono
          }

        }else{
          // Si la linea_movimiento aún tiene saldo pendiente
          if ( $tipo_movimiento == 'cartera')
          {
            $nuevo_saldo_pendiente = $linea_movimiento->saldo_pendiente + $valor_abono;
            $nuevo_valor_pagado = $linea_movimiento->valor_pagado - $valor_abono;
          }else{
            $nuevo_saldo_pendiente = $linea_movimiento->saldo_pendiente - $valor_abono;
            $nuevo_valor_pagado = $linea_movimiento->valor_pagado + $valor_abono;
          }
        }

        $linea_movimiento->valor_pagado = $nuevo_valor_pagado;
        $linea_movimiento->saldo_pendiente = $nuevo_saldo_pendiente;
        $linea_movimiento->estado = 'Pendiente';
        $linea_movimiento->save();
    }

    public function registrar_recaudo_cartera_estudiante( $doc_encabezado_recaudo, $registro_cxc_pendiente, $abono  )
    {
        $factura = VtasDocEncabezado::where([
                                                [ 'core_tipo_transaccion_id','=', $registro_cxc_pendiente->core_tipo_transaccion_id ],
                                                [ 'core_tipo_doc_app_id','=', $registro_cxc_pendiente->core_tipo_doc_app_id ],
                                                [ 'consecutivo','=', $registro_cxc_pendiente->consecutivo ]
                                            ])->get()->first();

        if ( is_null($factura) )
        {
            return false;
        }

        $aux_factura = FacturaAuxEstudiante::where('vtas_doc_encabezado_id', $factura->id )->get()->first();

        if ( is_null($aux_factura) )
        {
            return false;
        }

        if ($aux_factura->cartera_estudiante == null) {
          return false;
        }
        
        $recaudo = TesoRecaudosLibreta::create( [
                                    'core_tipo_transaccion_id' => (int)$doc_encabezado_recaudo->core_tipo_transaccion_id,
                                    'core_tipo_doc_app_id' => (int)$doc_encabezado_recaudo->core_tipo_doc_app_id,
                                    'consecutivo' => $doc_encabezado_recaudo->consecutivo,
                                    'id_libreta' => $aux_factura->cartera_estudiante->id_libreta,
                                    'id_cartera' => $aux_factura->cartera_estudiante_id,
                                    'concepto' => $aux_factura->cartera_estudiante->inv_producto_id,
                                    'fecha_recaudo' => $doc_encabezado_recaudo->fecha,
                                    'teso_medio_recaudo_id' => $doc_encabezado_recaudo->teso_medio_recaudo_id,
                                    'cantidad_cuotas' => 1,
                                    'valor_recaudo' => $abono,
                                    'creado_por' => $doc_encabezado_recaudo->creado_por
                                ] );

        $recaudo->registro_cartera_estudiante->sumar_abono_registro_cartera_estudiante( $abono );
        $recaudo->libreta->actualizar_estado();
    }
}