<?php

namespace App\Http\Controllers\CxC;
use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use View;
use Lava;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use NumerosEnLetras;
use Form;
use Storage;


use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;

// Modelos
use App\Core\Empresa;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Tercero;

use App\Tesoreria\TesoCuentaBancaria;

use App\CxC\CxcMovimiento;
use App\CxC\CxcDocEncabezado;
use App\CxC\CxcDocRegistro;
use App\CxC\CxcServicio;
use App\CxC\CxcEstadoCartera;
use App\CxC\CxcTransaccion;

use App\Contabilidad\ContabMovimiento;
use App\PropiedadHorizontal\Propiedad;



class CxCController extends TransaccionController
{
  protected $datos = [];
  protected $encabezado_doc;
  protected $inmueble;
  protected $tercero;
  protected $id_transaccion = 15;// 15 = Cuenta de cobro

  /*public function __construct()
  {
      $this->middleware('auth');
  }*/

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    $this->set_variables_globales();

    $select_crear = $this->get_boton_select_crear( $this->app );

    $opciones = Empresa::all();
    foreach ($opciones as $empresa) {
        $empresas[$empresa->id] = $empresa->descripcion;
    }

    // PROCESO QUE ACTUALIZA LAS CARTERAS CON EL ESTADO Vencida
    //Actualizar las cartera con fechas inferior a hoy y con estado distinto a Pagada
    //CxcEstadoCartera::actualizar_estado_cartera(date('Y-m-d'));

    if (null!==Input::get('e1')) {
        $e1 = Input::get('e1');
        $e2 = Input::get('e2');
        $e3 = Input::get('e3');
        $e4 = Input::get('e4');
        $empresa_id = Input::get('empresa_id');
    }else{
        $e1 = 15;
        $e2 = 30;
        $e3 = 45;
        $e4 = 60;
        $empresa_id = Auth::user()->empresa_id;
    }

    // LA EDAD DE LA CARTERA SON LOS DIAS QUE TIENE DESPUES DE LA FECHA DE VENCIMIENTO
    $edades = ['0',$e1,$e2,$e3,$e4,'999'];

    // Creación de gráfico de Torta MATRICULAS
    $stocksTable1 = Lava::DataTable();
    
    $stocksTable1->addStringColumn('Edad')
                  ->addNumberColumn('Valor');

    // obtención de cartera x edades

    /*$cartera_vencida = CxCMovimiento::where([
                                            ['estado','=','Pendiente'],
                                            ['core_empresa_id','=',$empresa_id]
                                          ])
                                    ->get();
             dd($cartera_vencida);*/
    $hasta = count($edades);
    for($i=1;$i<$hasta;$i++)
    {
        $min = (int)$edades[$i-1];
        $max = (int)$edades[$i];

        // Obtención de datos, esto arroja un array de objetos
        $cartera = DB::select(DB::raw("SELECT edad,saldo_pendiente FROM (SELECT fecha_vencimiento,saldo_pendiente, IF( CURRENT_DATE() > cxc_movimientos.fecha_vencimiento,DATEDIFF(CURRENT_DATE(),fecha_vencimiento),0) AS edad FROM `cxc_movimientos` WHERE estado='Pendiente' AND core_empresa_id = ".$empresa_id.") AS edades WHERE edad > ".$min." AND edad <= ".$max));
        //dd($cartera);
        // Convertir a array de arrays
        $cartera = json_decode(json_encode($cartera), true);
        // Agregar campo a la torta
        if ($max!=999) {
            $lbl = ($min+1)." - ".$max." días";
        }else{
            $lbl = " > ".$min." días";
        }

        $cartera_edades[$i]['lbl'] = $lbl;
        $cartera_edades[$i]['saldo_pendiente'] = array_sum(array_column($cartera, 'saldo_pendiente'));
        
        $stocksTable1->addRow([$lbl, (float)$cartera_edades[$i]['saldo_pendiente']]);
    }
      

    $chart1 = Lava::PieChart('torta_cartera', $stocksTable1,[
            'is3D'                  => True,
            'pieSliceText'          => 'value'
        ]);

    $miga_pan = [
            ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=>'CxC']
        ];

    return view('cxc.index',compact('cartera_edades','miga_pan','edades','empresas','empresa_id','select_crear'));
  }

    /**
     * Formulario para crear Cuenta de cobro.
     *
     */
    public function create()
    {
      // Se obtiene el modelo según la variable modelo_id  de la url
      $modelo = Modelo::find(Input::get('id_modelo'));

      $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
      $cantidad_campos = count($lista_campos);

      $tipo_transaccion = TipoTransaccion::find($this->id_transaccion);

      $lista_campos = ModeloController::personalizar_campos('no_aplica', $tipo_transaccion,$lista_campos, $cantidad_campos, 'create');

      $form_create = [
                      'url' => $modelo->url_form_create,
                      'campos' => $lista_campos
                  ];

      $registros = CxcServicio::where('core_empresa_id',Auth::user()->empresa_id)
                         ->orderBy('descripcion')
                         ->get();
        $servicios[''] = '';
        foreach ($registros as $fila) {
            $servicios[$fila->id] = $fila->descripcion; 
        }

      $terceros[''] = '';
        foreach ($opciones as $opcion)
        {
            if ($opcion->codigo != 0) {
              $terceros[$opcion->core_tercero_id.'a3p0'.$opcion->id] = $opcion->codigo.' - '.$opcion->descripcion;
            }else{
              $terceros[$opcion->core_tercero_id.'a3p00'] = $opcion->descripcion;
            }
            
        }

      $miga_pan = [
              ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=>'CxC'],
              ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
              ['url'=>'NO','etiqueta' => 'Crear nuevo' ]
          ];

      // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
      $tabla = new TablaIngresoLineaRegistros( CxcTransaccion::get_datos_tabla_ingreso_lineas_registros( $tipo_transaccion, $servicios) );


      return view( 'cxc.create', compact( 'form_create',['id_transaccion' => $this->id_transaccion],'miga_pan','tabla' ) );
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

      $reg_anterior = CxcDocEncabezado::where('id', '<', $id)
          ->whereIn('cxc_doc_encabezados.core_tipo_transaccion_id', [5, 15] )->where('core_empresa_id', Auth::user()->empresa_id)
                  ->max('id');
      $reg_siguiente = CxcDocEncabezado::where('id', '>', $id)
                  ->whereIn('cxc_doc_encabezados.core_tipo_transaccion_id', [5, 15] )->where('core_empresa_id', Auth::user()->empresa_id)
                  ->min('id');

      $view_pdf = $this->vista_preliminar_cxc($id,'show');

      $miga_pan = [
                ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=>'CxC'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Consulta' ]
            ];

      // Verificar los documentos aplicados para cancelar la cartera
      $documentos_recaudos = DB::table('cxc_documento_tiene_recaudos')->leftJoin('cxc_movimientos','cxc_movimientos.id','=','cxc_documento_tiene_recaudos.cxc_movimiento_id')
                                ->where('cxc_movimientos.documento_cartera_id', $id )
                                ->select('cxc_documento_tiene_recaudos.fecha_registro','cxc_documento_tiene_recaudos.transaccion_origen_doc_recaudo_id','cxc_documento_tiene_recaudos.recaudo_documento_id','cxc_documento_tiene_recaudos.valor_pagado')
                                ->get();

        if ( !empty($documentos_recaudos) )
        {
        
          $i=1;
          $tabla2 = '<table  class="tabla_registros" style="margin-top: -4px;">
                          <tr>
                              <td colspan="4" align="center">
                                 <b> Movimiento de documentos aplicados</b>
                              </td>
                          </tr>
                          <tr class="encabezado">
                              <td>
                                 Documento
                              </td>
                              <td>
                                 Fecha
                              </td>
                              <td>
                                 Detalle
                              </td>
                              <td>
                                 Valor pagado
                              </td>
                          </tr>';

          foreach ($documentos_recaudos as $registro) {

              // Obtener la table segun el ID de la transacción que hizo el recaudo
            // Puede ser una transacción hecha desde Tesorería o desde CxC
              $tipo_transaccion = TipoTransaccion::find( $registro->transaccion_origen_doc_recaudo_id );
              //$any_registro = New $tipo_transaccion->modelo_encabezados_documentos;
              //$nombre_tabla = $any_registro->getTable();

              
              $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",'.$nombre_tabla.'.consecutivo) AS documento_recaudo';

              $documento_recaudo = app($tipo_transaccion->modelo_encabezados_documentos)->leftJoin('core_tipos_docs_apps','core_tipos_docs_apps.id','=',$nombre_tabla.'.core_tipo_doc_app_id')
                        ->where($nombre_tabla.'.id', $registro->doc_recaudo_id )
                        ->select(DB::raw($select_raw),$nombre_tabla.'.descripcion AS detalle')
                        ->get()[0];

              $tabla2.='<tr  class="fila-'.$i.'" >
                              <td>
                                 '.$documento_recaudo->documento_recaudo.'
                              </td>
                              <td>
                                 '.$registro->fecha_registro.'
                              </td>
                              <td>
                                 '.$documento_recaudo->detalle.'
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
          $tabla2.='</table>';
      }else{
        $tabla2='';
      }

      return view( 'cxc.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id','tabla2') ); 

    }



  // ELIMINAR Cuenta de cobro
  public function eliminar_cxc($id)
  {      
    $documento = CxcDocEncabezado::find($id);

    // >>> Validaciones inciales
    $cxc_movimiento_doc = CxcMovimiento::where('core_empresa_id',$documento->core_empresa_id)
        ->where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
        ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
        ->where('consecutivo', $documento->consecutivo)
        ->get();

    if ( !is_null($cxc_movimiento_doc) )
    {
      $cxc_movimiento_doc = $cxc_movimiento_doc[0];
    }else{
      $cxc_movimiento_doc = (object)array('id'=>0);
    }

    // Está en un documento cruce de cartera?, es decir, tiene pagos aplicados?
    $cantidad = DB::table('cxc_documento_tiene_recaudos')
                    ->where('cxc_movimiento_id', $cxc_movimiento_doc->id )
                    ->count();
    if($cantidad != 0){
        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Cuenta de cobro NO puede ser eliminada. Tiene pagos aplicados.');
    }

    // 1ro. Borrar registros contables
    ContabMovimiento::where('core_empresa_id',$documento->core_empresa_id)
        ->where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
        ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
        ->where('consecutivo', $documento->consecutivo)
        ->delete();

    // 2do. Borrar movimiento de cartera
    if ( $cxc_movimiento_doc->id != 0)
    {
      $cxc_movimiento_doc->delete();

      // También se elimina del estado de cartera
      CxcEstadoCartera::where( 'cxc_movimiento_id', $cxc_movimiento_doc->id)->delete();
    }
    

    // 2ro. Borrar registros del documento
    CxcDocRegistro::where( 'cxc_doc_encabezado_id', $documento->id)->delete();

    // 4to. Se elimina el documento de cruce
    $documento->delete();

    return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('flash_message','Cuenta de cobro eliminada correctamente.');
  }

  public function cxc_print($id)
  {
    $view_pdf = $this->vista_preliminar_cxc($id,'imprimir');

    $tam_hoja = 'Letter';
    $orientacion='portrait';
    $pdf = \App::make('dompdf.wrapper');
    $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);
    return $pdf->download('cuenta_de_cobro_'.$this->encabezado_doc->documento_app.'.pdf');
  }

  // Muestra el formulario para REIMPRIMIR
  public static function form_reimprimir_cxc()
  {
    
    $tipo_docs_app = DB::table('core_transaccion_tiene_documento')->leftJoin('core_tipos_docs_apps','core_tipos_docs_apps.id','=','core_transaccion_tiene_documento.core_tipo_doc_id')->whereIn('core_tipo_transaccion_id', [15, 7, 9, 10, 12])->get();

    foreach ($tipo_docs_app as $fila) {
        $tipos_documentos[$fila->id]=$fila->prefijo." - ".$fila->descripcion; 
    }

    $miga_pan = [
            ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=>'CxC'],
            ['url'=>'NO','etiqueta'=>'Reimpresión de documentos']
        ];

    return view( 'cxc.reimprimir_cxc',compact( 'tipos_documentos','miga_pan') );
}

// Respuesta para REIMPRIMIR
public static function ajax_reimprimir_cxc( Request $request)
{
  $empresa_id = Auth::user()->empresa_id;

  $tbody = '';
  for($consecutivo=$request->consecutivo_desde;$consecutivo<=$request->consecutivo_hasta;$consecutivo++)
  {
    // A través del tipo de documento y consecutivo se obtiene el ID del encabezado del documento
      $doc_cxc_encabezado_id = CxcDocEncabezado::where('core_empresa_id', $empresa_id)->where('core_tipo_doc_app_id', $request->core_tipo_doc_app_id)->where('consecutivo', $consecutivo)->value('id');

      if ( $doc_cxc_encabezado_id > 0) 
      {
        $encabezado_doc =  CxcDocEncabezado::get_un_registro($doc_cxc_encabezado_id);

        $tbody.='<tr>
                    <td>'.$encabezado_doc->codigo_inmueble.'</td>
                    <td>'.$encabezado_doc->descripcion.'</td>
                    <td> <a href="'.url('cxc/'.$encabezado_doc->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo).'" target="_blank" title="Vista previa">'.$encabezado_doc->documento_app.'</a> </td>
                    <td>'.number_format($encabezado_doc->valor_total, 0, ',', '.').'</td>
                </tr>';
      }else{
        $tipo_documento = TipoDocApp::find($request->core_tipo_doc_app_id);
        $tbody.='<tr>
                    <td colspan="4"> El documento de cartera <b>'.$tipo_documento->prefijo.' '.$consecutivo.'</b> NO existe.</td>
                </tr>';
      }
        
  }

  $thead = '<tr>
                <th>Propiedad</th>
                <th>Propietario</th>
                <th>Documento</th>
                <th>Valor total</th>
            </tr>';

  return [$thead, $tbody, 0, 0, 0, $empresa_id, $request->core_tipo_doc_app_id, $request->consecutivo_desde, $request->consecutivo_hasta];
}

/*
  ** Enviar por email un documento
*/
  public function cxc_enviar_por_email($id)
  {
    $view_pdf = $this->vista_preliminar_cxc($id,'imprimir');

    $tam_hoja = 'Letter';
    $orientacion='portrait';
    $pdf = \App::make('dompdf.wrapper');
    $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja, $orientacion);

    $nombrearchivo = 'cuenta_de_cobro_'.$id.'.pdf';
    Storage::put('pdf_email/'.$nombrearchivo, $pdf->output());      
    
    if ( $this->enviar_email($id, $nombrearchivo) ) {
      return redirect('cxc/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('flash_message','Correo enviado correctamente.');
    } else {
      return redirect('cxc/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('mensaje_error','Correo no pudo ser enviado.');
    }

  }

  public function imprimir_lote($empresa_id,$core_tipo_doc_app_id,$consec_desde,$consec_hasta)
  {
      $view_pdf = '';
      $vista = 'imprimir';
      for($consecutivo=$consec_desde;$consecutivo<=$consec_hasta;$consecutivo++)
      {
        // A través del tipo de documento y consecutivo se obtiene el ID del encabezado del documento
          $doc_cxc_encabezado_id = CxcDocEncabezado::where('core_empresa_id', $empresa_id)->where('core_tipo_doc_app_id', $core_tipo_doc_app_id)->where('consecutivo', $consecutivo)->value('id');

          if ( $doc_cxc_encabezado_id > 0) {
            $view_pdf.=$this->vista_preliminar_cxc($doc_cxc_encabezado_id,'imprimir');
          }else{
            $tipo_documento = TipoDocApp::find($core_tipo_doc_app_id);
            $view_pdf.='<p> El documento de cartera <b>'.$tipo_documento->prefijo.' '.$consecutivo.'</b> NO existe. <div class="page-break"></div> </p>';
          }
            
      }
      
      $tam_hoja = 'Letter';//array(0, 0, 612.00, 396.00);//

      $orientacion='portrait';
      $pdf = \App::make('dompdf.wrapper');
      $pdf->loadHTML( $view_pdf )->setPaper($tam_hoja,$orientacion);
      return $pdf->download('cuenta_de_cobro.pdf');
          
  }

  public function enviar_email_lote($empresa_id,$core_tipo_doc_app_id,$consec_desde,$consec_hasta)
  {
      $view_pdf = '';
      $vista = 'imprimir';

      $tabla = '<table class="table table-bordered table-striped" id="myTable">
                    <thead>
                      <tr>
                          <th>
                             Inmueble
                          </th>
                          <th>
                             Propietario
                          </th>
                          <th>
                             E-mail
                          </th>
                          <th>
                             Estado envío
                          </th>
                      </tr>
                    </thead>
                    <tbody>';

      for($consecutivo=$consec_desde;$consecutivo<=$consec_hasta;$consecutivo++)
      {
          // A través del tipo de documento y consecutivo se obtiene el ID del encabezado del documento
        $doc_cxc_encabezado = CxcDocEncabezado::where('core_empresa_id', $empresa_id)->where('core_tipo_doc_app_id', $core_tipo_doc_app_id)->where('consecutivo', $consecutivo)->get()[0];

        $view_pdf = $this->vista_preliminar_cxc($doc_cxc_encabezado->id,'imprimir');

        $tam_hoja = 'Letter';
        $orientacion='portrait';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);

        $nombrearchivo = 'cuenta_de_cobro_'.$doc_cxc_encabezado->id.'.pdf';

        Storage::put('pdf_email/'.$nombrearchivo, $pdf->output());

        //$inmueble = Propiedad::find($doc_cxc_encabezado->codigo_referencia_tercero);

        $tercero = Tercero::find($doc_cxc_encabezado->core_tercero_id);

        if ( $this->enviar_email($doc_cxc_encabezado->id, $nombrearchivo) ) 
        {
          $estado_envio = '<span>Enviado</span>';
        }else{
          $estado_envio = '<span style="background: #FD845D;">NO Enviado</span>';
        }
        
        $tabla .= '<tr>
                    <td>'.$this->inmueble->codigo.'</td>
                    <td>'.$tercero->descripcion.'</td>
                    <td>'.$this->inmueble->email.'</td>
                    <td>'.$estado_envio.'</td>
                    </tr>';

      }

      $tabla .= '</tbody></table>';
      
      //echo $tabla;
      return redirect()->to( app('url')->previous() )->with('flash_message','Email(s) enviado(s) correctamente.');
  }

  // Generar vista para SHOW o IMPRIMIR
  public function vista_preliminar_cxc($id,$vista)
  {

    $this->encabezado_doc =  CxcDocEncabezado::get_un_registro($id);

    // REGISTROS DEL DOCUMENTO

    // Se crea una tabla con los registros
    $registros = CxcDocRegistro::registros_del_encabezado($id);

    $total_1=0;
    $i=1;
    $tabla2 = '<table  class="tabla_registros" style="margin-top: 1px;">
                    <tr class="encabezado">
                        <td>
                           Concepto
                        </td>
                        <td>
                           Valor
                        </td>
                    </tr>';

    foreach ($registros as $registro) {
        
        $tabla2 .= $this->crear_fila($i, $registro->descripcion_servicio, $registro->valor_unitario);

        $i++;
        if ($i==3) {
            $i=1;
        }
        $total_1+=$registro->valor_unitario;
    }

    $tabla2.=$this->crear_fila($i, '<b>Total documento</b>', $total_1);
    $i++;
        if ($i==3) {
            $i=1;
        }

    // LINEA DE SALDOS PENDIENTES
    $saldo_pendiente = 0;
    $saldo_pendiente = CxcMovimiento::where('codigo_referencia_tercero',$this->encabezado_doc->codigo_referencia_tercero)->where('saldo_pendiente','>',0)->where('id','<>',$id)->where('fecha','<',$this->encabezado_doc->fecha)->sum('saldo_pendiente');

    //if ($saldo_pendiente>0) {

      $tabla2.=$this->crear_fila($i, 'Saldo anterior', $saldo_pendiente);

      $i++;
        if ($i==3) {
            $i=1;
        }
        $total_1+=$saldo_pendiente;
    //}

    // MOSTRAR ANTICIPOS
    $anticipos = CxcMovimiento::where('codigo_referencia_tercero',$this->encabezado_doc->codigo_referencia_tercero)->where('saldo_pendiente','<',0)->where('id','<>',$id)->where('fecha','<',$this->encabezado_doc->fecha)->sum('saldo_pendiente');

    if ( $anticipos < 0 ) {
      $tabla2.=$this->crear_fila($i, 'Saldo a favor', $anticipos);
      $i++;
        if ($i==3) {
            $i=1;
        }
        $total_1+=$anticipos;
    }

    $tabla2.=$this->crear_fila($i, '<b>TOTAL a pagar</b>', $total_1);

    $tabla2.='<tr>
        <td colspan="2">
           Son '.NumerosEnLetras::convertir($total_1,'pesos',false).'
        </td>
    </tr>';
    $tabla2.='</table>';

    // DATOS ADICIONALES
    $tipo_doc_app = TipoDocApp::find($this->encabezado_doc->core_tipo_doc_app_id);
    $descripcion_transaccion = $tipo_doc_app->descripcion;

    $elaboro = $this->encabezado_doc->creado_por;
    $empresa = Empresa::find($this->encabezado_doc->core_empresa_id);
    $ciudad = DB::table('core_ciudades')
            ->where('id','=',$empresa->codigo_ciudad)
            ->value('descripcion');

    $encabezado_doc = $this->encabezado_doc;

    // Para la impresión incluir firmas
    $datos_cuenta = TesoCuentaBancaria::get_cuenta_por_defecto();
    
    $cuenta = 'Cuenta '.$datos_cuenta['tipo_cuenta'].' '.$datos_cuenta['entidad_financiera'].' No. '.$datos_cuenta['descripcion'];

    $lbl_formato_cxc = config('gestion_de_cobros.lbl_formato_cxc');
    
    $etiqueta = str_replace(["datos_cuenta_bancaria", "nombre_empresa", "email_empresa"], ['<b>'.$cuenta.'</b>', '<b>'.$empresa->descripcion.'</b>', '<b>'.$empresa->email.'</b>'], $lbl_formato_cxc);

    if ( $vista != "show" ) {
      $firmas = '<table class="con_borde" width="100%" style="margin-top: 3px; font-size: 12px;">
                  <tr>
                      <td width="65%">
                          '.$etiqueta.'
                      </td>
                      <td>
                          RECIBI CONFORME:
                          <br/><br/>
                          _____________________________________
                          <br/>
                          C.C.:
                      </td>
                  </tr>
              </table>';
    }else{
      $firmas = '';
    }
            
    $view_1 = View::make('cxc.incluir.encabezado_transaccion',compact('encabezado_doc','descripcion_transaccion','empresa','vista','ciudad','total_1') )->render();

    $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla2.$firmas.'<div class="page-break"></div>';
      
      return $view_pdf;
  }

  public function imprimir_cartera_una_edad($min,$max,$empresa_id)
    {
        if ($min<0) {
            // la edad mínima es negativa
            // Obtención de datos, esto arroja un array de objetos
            $cartera = DB::select(DB::raw("SELECT * FROM (SELECT *, IF( CURRENT_DATE() > cxc_movimientos.fecha_vencimiento,DATEDIFF(CURRENT_DATE(),fecha_vencimiento),0) AS edad FROM `cxc_movimientos` WHERE estado='Vencido' AND core_empresa_id = ".$empresa_id.") AS edades WHERE edad > ".$max." ORDER BY codigo_referencia_tercero"));
            $min=0;
        }else{
            // Obtención de datos, esto arroja un array de objetos
            $cartera = DB::select(DB::raw("SELECT * FROM (SELECT *, IF( CURRENT_DATE() > cxc_movimientos.fecha_vencimiento,DATEDIFF(CURRENT_DATE(),fecha_vencimiento),0) AS edad FROM `cxc_movimientos` WHERE estado='Vencido' AND core_empresa_id = ".$empresa_id.") AS edades WHERE edad > ".$min." AND edad <= ".$max." ORDER BY codigo_referencia_tercero,fecha"));
        }
            
        // Convertir a array de arrays
        $cartera = json_decode(json_encode($cartera), true);

        $vista = 'imprimir';

        $view = View::make( 'cxc.formatos.cartera_una_edad_1', compact( 'cartera','min','max','empresa_id','vista' ) )->render();
        
        $tam_hoja = 'Letter';
        $orientacion='portrait';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja,$orientacion);
        return $pdf->download('cartera_por_edad.pdf');
        /*echo $view;*/
    }

    // Se obtiene la cartera para el tercero enviado y la empresa asociada al user que hace la petición
    public static function get_cartera_tercero($core_tercero_id, $vista = null)
    {

      $movimiento_cxc = CxcMovimiento::get_movimiento_tercero($core_tercero_id, Auth::user()->empresa_id);
      
      if ($vista == null) {
        $view = View::make('cxc.incluir.lista_cxc_tabla',compact('movimiento_cxc'));
      }else{
        // Vista para la APP Mi Conjunto 
        $view = View::make('cxc.incluir.lista_cxc_tabla_2',compact('movimiento_cxc'));
      }

      return $view;
    }

// Se obtiene la cartera para el ID del inmueble
public static function get_cartera_inmueble($ph_propiedad_id, $fecha, $vista = null)
{
  $movimiento_cxc = CxcMovimiento::documentos_pendientes_inmueble($ph_propiedad_id, $fecha, '<=');
  
  if ($vista == null) {
    // Para cuando se están elaborando documentos de transacciones

    //print_r( $movimiento_cxc );

    $view = View::make('cxc.incluir.cartera_inmuebles',compact('movimiento_cxc','fecha'));
  }else{
    // Para Mostrar La Cartera al residente (App Mi Conjunto)
    $view = View::make('cxc.incluir.lista_cxc_tabla_2',compact('movimiento_cxc'));
  }

  return $view;
}

    //
    // AJAX: enviar fila para el ingreso de registros al elaborar documento
    public static function cxc_get_fila( $id_fila )
    {
        $registros = CxcServicio::where('core_empresa_id',Auth::user()->empresa_id)
                         ->orderBy('descripcion')
                         ->get();
        $servicios[''] = '';
        foreach ($registros as $fila) {
            $servicios[$fila->id] = $fila->descripcion; 
        }


        $opciones = DB::table('core_terceros')->where('core_terceros.core_empresa_id',Auth::user()->empresa_id)->select('core_terceros.id as core_tercero_id','core_terceros.descripcion')->get();

        $terceros[''] = '';
        foreach ($opciones as $opcion)
        {
            if ($opcion->codigo != 0) {
              $terceros[$opcion->core_tercero_id.'a3p0'.$opcion->id] = $opcion->codigo.' - '.$opcion->descripcion;
            }else{
              $terceros[$opcion->core_tercero_id.'a3p00'] = $opcion->descripcion;
            }
            
        }

        $btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar pull-right'><i class='glyphicon glyphicon-trash'></i></button>";
        $btn_confirmar = "<button type='button' class='btn btn-success btn-xs btn_confirmar pull-right'><i class='glyphicon glyphicon-ok'></i></button>";

        $tr = '<tr>
                    <td style="display: none;"></td>
                    <td style="display: none;"></td>
                    <td style="display: none;"></td>
                    <td>
                        '.Form::select( 'campo_servicio', $servicios, null, [ 'id' => 'combobox_servicios', 'class' => 'lista_desplegable' ] ).'
                    </td>
                    <td>
                        '.Form::select( 'campo_tercero', $terceros, null, [ 'id' => 'combobox_terceros', 'class' => 'lista_desplegable' ] ).'
                    </td>
                    <td> '.Form::text( 'valor', null, [ 'id' => 'col_valor', 'class' => 'caja_texto' ] ).' </td>
                    <td>'.$btn_confirmar.$btn_borrar.'</td>
                </tr>';

        return $tr;
    }

    // Muestra el formulario para generar el reporte
    public static function estados_de_cuentas()
    {

        /*$registros = Propiedad::where('core_empresa_id',Auth::user()->empresa_id)
                         ->orderBy('codigo')->get();
        */
        $registros = Propiedad::leftJoin('core_terceros', 'core_terceros.id', '=', 'ph_propiedades.core_tercero_id')
                  ->where('ph_propiedades.core_empresa_id',Auth::user()->empresa_id)
                  ->orderBy('ph_propiedades.codigo')
                  ->select('ph_propiedades.id','ph_propiedades.codigo','ph_propiedades.nomenclatura','core_terceros.descripcion')
                  ->get();

        $opciones[''] = '';
        foreach ($registros as $fila) {
            $opciones[$fila->id]=$fila->codigo." - ".$fila->descripcion; 
        }
        $propiedades = $opciones;

        $registros_c = Tercero::where('core_empresa_id',Auth::user()->empresa_id)
                         ->get();
        $opciones_c[''] = '';
        foreach ($registros_c as $campo) {
            $opciones_c[$campo->id] = $campo->numero_identificacion." ".$campo->descripcion;
        }
        $terceros = $opciones_c;

        $miga_pan = [
                ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=>'CxC'],
                ['url'=>'NO','etiqueta'=>'Estados de cuentas']
            ];

        return view( 'cxc.estados_de_cuentas',compact( 'propiedades','terceros','miga_pan') );
}

public function cxc_ajax_estados_de_cuentas(Request $request)
{

  //Actualizar las cartera con fechas inferior a hoy y con estado distinto a Pagada
  CxcEstadoCartera::actualizar_estado_cartera($request->fecha_final);

  $fecha_inicial = $request->fecha_inicial;
  $fecha_final = $request->fecha_final;
  $estado = '%'.$request->estado.'%';

  if ( $request->codigo_referencia_tercero == '') {
    $codigo_referencia_tercero = '%'.$request->codigo_referencia_tercero.'%';
    $operador = 'LIKE';
  }else{
    $codigo_referencia_tercero = $request->codigo_referencia_tercero;
    $operador = '=';
  }
    
  $core_tercero_id = '%'.$request->core_tercero_id.'%';
  
  switch ($request->tipo_informe) {
     case 'detallado':
       $movimiento_cxc = CxcEstadoCartera::estados_de_cuentas($fecha_inicial, $fecha_final, $estado, $codigo_referencia_tercero, $operador, $core_tercero_id );
       $informe = 'cxc_ajax_estados_de_cuentas';
       break;

     case 'resumido':
       $movimiento_cxc = CxcMovimiento::estados_de_cuentas_resumido($fecha_inicial, $fecha_final, $estado, $codigo_referencia_tercero, $operador, $core_tercero_id );
       $informe = 'cxc_ajax_estados_de_cuentas_resumido';
       break;
     
     default:
       # code...
       break;
   }   
    
  $view = View::make('cxc.incluir.'.$informe,compact('movimiento_cxc'));

  return $view; 
}

public function cxc_pdf_estados_de_cuentas()
{

  //Actualizar las cartera con fechas inferior a hoy y con estado distinto a Pagada
  CxcEstadoCartera::actualizar_estado_cartera(Input::get('fecha_final'));

  $fecha_inicial = Input::get('fecha_inicial');
  $fecha_final = Input::get('fecha_final');
  $estado = '%'.Input::get('estado').'%';

  if ( Input::get('codigo_referencia_tercero') == '') {
    $codigo_referencia_tercero = '%'.Input::get('codigo_referencia_tercero').'%';
    $operador = 'LIKE';
  }else{
    $codigo_referencia_tercero = Input::get('codigo_referencia_tercero');
    $operador = '=';
  }
    
  $core_tercero_id = '%'.Input::get('core_tercero_id').'%';

  $movimiento_cxc = CxcEstadoCartera::estados_de_cuentas($fecha_inicial, $fecha_final, $estado, $codigo_referencia_tercero, $operador, $core_tercero_id );
  //CxcMovimiento::estados_de_cuentas($fecha_inicial, $fecha_final, $estado, $codigo_referencia_tercero, $operador, $core_tercero_id );

  $empresa = Empresa::find(Auth::user()->empresa_id);
  $vista = 'imprimir';

  $view = View::make('cxc.incluir.cxc_pdf_estados_de_cuentas',compact('movimiento_cxc','empresa','vista'));

  $tam_hoja = 'Letter';
  $orientacion='portrait';
  $pdf = \App::make('dompdf.wrapper');
  $pdf->loadHTML($view)->setPaper($tam_hoja,$orientacion);

  return $pdf->download('estado_de_cuentas.pdf');

}

public function form_modificar_doc_encabezado($cxc_doc_encabezado_id)
  {
      $modelo = Modelo::find(Input::get('id_modelo'));

      // Se obtiene el registro a modificar del modelo
      $registro = app($modelo->name_space)->find($cxc_doc_encabezado_id);

      $lista_campos = ModeloController::get_campos_modelo($modelo,$registro,'edit');

      //Personalización de la lista de campos
      $cant = count($lista_campos);
      for ($i=0; $i < $cant; $i++) {
        switch ( $lista_campos[$i]['name'] ) {
          case 'consecutivo':
            $lista_campos[$i]['tipo'] = 'bsText';
            $lista_campos[$i]['value'] = $registro->consecutivo;
            break;
          case 'modificado_por':
            $lista_campos[$i]['value'] = Auth::user()->email;;
            break;

          case 'core_tipo_doc_app_id':
            $lista_campos[$i]['atributos'] = ['disabled' => 'disabled'];
            break;
          
          default:
            # code...
            break;
        }
          
      }

      // Crear un nuevo campo
      $lista_campos[$i]['tipo'] = 'personalizado';
      $lista_campos[$i]['name'] = 'mensaje_advertencia';
      $lista_campos[$i]['descripcion'] = '';
      $lista_campos[$i]['opciones'] = '';
      $lista_campos[$i]['value'] = '<div class="alert alert-danger">
<strong>¡Advertencia!</strong> <br/> Al modificar el consecutivo pueden quedar dos documentos con igual consecutivo.
</div>';
      $lista_campos[$i]['atributos'] = [];
      $lista_campos[$i]['requerido'] = true;

      $form_create = [
                      'url' => $modelo->url_form_create,
                      'campos' => $lista_campos
                  ];

      $miga_pan = [
                ['url'=>'cxc?id='.Input::get('id'),'etiqueta'=>'CxC'],
                ['url'=>'NO','etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Modificar' ]
            ];


      return view('cxc.edit',compact('form_create','miga_pan','registro'));
  }

public function guardar_doc_encabezado(Request $request, $id)
  {
    $modelo = Modelo::find($request->url_id_modelo);
    // Se obtinene el registro a modificar del modelo
    $registro = app($modelo->name_space)->find($id);

    // Primero, Se modifican los datos  en el movimiento de contabilidad para el cosecutivo del doc
    ContabMovimiento::where('core_tipo_transaccion_id', $registro->core_tipo_transaccion_id)
        ->where('core_tipo_doc_app_id', $registro->core_tipo_doc_app_id)
        ->where('consecutivo', $registro->consecutivo)
        ->where('core_empresa_id', $registro->core_empresa_id)
        ->where('core_tercero_id', $registro->core_tercero_id)
        ->update( ['fecha' => $request->fecha,'consecutivo' => $request->consecutivo,'documento_soporte' => $request->documento_soporte, 'detalle_operacion' => $request->descripcion ] );

    // Luego Se modifican los datos en el movimiento de CARTERA para el cosecutivo del doc
    CxcMovimiento::where('core_tipo_transaccion_id', $registro->core_tipo_transaccion_id)
        ->where('core_tipo_doc_app_id', $registro->core_tipo_doc_app_id)
        ->where('consecutivo', $registro->consecutivo)
        ->where('core_empresa_id', $registro->core_empresa_id)
        ->where('core_tercero_id', $registro->core_tercero_id)
        ->update( ['fecha' => $request->fecha,'fecha_vencimiento' => $request->fecha_vencimiento,'consecutivo' => $request->consecutivo, 'detalle_operacion' => $request->descripcion ] );

    // Se actualizan los datos en el modelo     
    $registro->fill( $request->all() );
    $registro->save();

    return redirect('cxc/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');
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

  function enviar_email($id_doc_cxc, $nombrearchivo = null)
  {
    //$this->encabezado_doc =  CxcDocEncabezado::get_un_registro($id_doc_cxc);

    $empresa = Empresa::find($this->encabezado_doc->core_empresa_id);

    $this->inmueble = Propiedad::find($this->encabezado_doc->codigo_referencia_tercero);

    // Datos requeridos por hostinger
    
    // Email interno. Debe estar creado en Hostinger
    $email_interno = 'info@'.substr( url('/'), 7);
    
    $from = $empresa->descripcion." <".$email_interno."> \r\n";
    $headers = "From:" . $from."CC: ".$empresa->email." \r\n";
    $to = $this->inmueble->email_arrendatario;//"adalberto-77@hotmail.com";//
    
    $subject = "Cuenta de cobro ".$this->encabezado_doc->documento_app.' - '.$this->encabezado_doc->detalle;
    
    // El mensaje

    $fecha = explode("-",$this->encabezado_doc->fecha_vencimiento);
    if( $fecha[2] != '00' )
    {
      $pagar_hasta = "<b> &nbsp; ".$fecha[2]." de ".Form::NombreMes([$fecha[1]])." de ".$fecha[0]."</b>";
    }else{
      $pagar_hasta = "<b>Inmediato</b>";
    }

    $fecha_cxc = explode("-",$this->encabezado_doc->fecha);
    if( $fecha_cxc[2] != '00' )
    {
      $pagar_hasta = "<b> &nbsp; ".$fecha_cxc[2]." de ".Form::NombreMes([$fecha_cxc[1]])." de ".$fecha_cxc[0]."</b>";
    }else{
      $pagar_hasta = "<b>Inmediato</b>";
    }
    
    $htmlContent = "Saludos, <br/>Te hacemos llegar tu cuenta de cobro correspondiente al mes de <b>".Form::NombreMes([$fecha_cxc[1]])." de ".$fecha_cxc[0]."</b> con fecha límite de pago hasta el ".$pagar_hasta.".";

    //headers for attachment
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"=A=G=R=O=\"\r\n\r\n";
  
    
    // Armando mensaje del email
    $message = "--=A=G=R=O=\r\n";
    $message .= "Content-type:text/html; charset=utf-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $htmlContent . "\r\n\r\n";

    //attachment file path
    if ($nombrearchivo != null) 
    {
      //$nombrearchivo = 'cuenta_de_cobro_'.$id_doc_cxc.'.pdf';
      $url = Storage::getAdapter()->applyPathPrefix('pdf_email/'.$nombrearchivo);
      $file = chunk_split(base64_encode(file_get_contents( $url )));
      
      $message .= "--=A=G=R=O=\r\n";
      $message .= "Content-Type: application/octet-stream; name=\"" . $nombrearchivo . "\"\r\n";
      $message .= "Content-Transfer-Encoding: base64\r\n";
      $message .= "Content-Disposition: attachment; filename=\"" . $nombrearchivo . "\"\r\n\r\n";
      $message .= $file . "\r\n\r\n";
      $message .= "--=A=G=R=O=--";
    }

    return mail($to,$subject,$message, $headers);
  }

  function crear_fila($i, $descripcion, $valor)
  {
    return '<tr class="fila-'.$i.'" >
                  <td>
                     '.$descripcion.'
                  </td>
                  <td>
                     $'.number_format($valor, 0, ',', '.').'
                  </td>
              </tr>';
  }
}