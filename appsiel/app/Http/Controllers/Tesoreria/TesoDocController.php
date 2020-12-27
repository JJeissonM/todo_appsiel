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


use App\Http\Controllers\Core\ConfiguracionController;
use App\Http\Controllers\Sistema\ModeloController;


// Modelos
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Core\Tercero;

use App\Matriculas\Grado;
use App\Matriculas\Estudiante;
use App\Core\Colegio;
use App\Core\Empresa;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoDocEncabezadoPago;
use App\Tesoreria\TesoDocRegistroPago;
use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabCuenta;

class TesoDocController extends Controller
{

    /*
        WARNING
        En este Controller se debe unificar PagoController y RecaudoController
        Por lo pronto Se están manejando aquellos Controllers por separados

    */
    protected $datos = [];
    protected $consecutivo;

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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $id_transaccion = 17;// 17 = Pagos de tesorería

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

        $motivos = [''];//TesoDocController::get_motivos($id_transaccion);
        $medios_recaudo = TesoDocController::get_medios_recaudo();
        $cajas = TesoDocController::get_cajas();
        $cuentas_bancarias = TesoDocController::get_cuentas_bancarias();

        /*$registros = Tercero::all();
        //$vec_m[''] = ''; // si quito esto queda seleccionado el primer registro por defecto, entonces no tengo que hacer validaciones para evitar que este campo se vaya vacío 
        foreach ($registros as $fila) {
            $vec_m[$fila->id]=$fila->apellido1.' '.$fila->apellido2.' '.$fila->nombre1.' '.$fila->razon_social; 
        }*/        
        $terceros = [''];//$vec_m;

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => $modelo->descripcion ],
                ['url'=>'NO','etiqueta' => 'Crear nuevo' ]
            ];

        return view('tesoreria.pagos.create', compact( 'form_create','id_transaccion','motivos','miga_pan','medios_recaudo','cajas','cuentas_bancarias', 'terceros' ) );
    }

    /**
     * Store a newly created resource in storage.
     * // Este método es llamado desde ModeloController@store
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$registro_encabezado_doc)
    {
        // Ya se llenó la tabla teso_doc_encabezados_recaudos en el ModeloController

        $tabla_registros_documento = json_decode($request->tabla_registros_documento);

          // 1ro. se guardan los registros asociados al encabezado del documento
          // Se recorre la tabla enviada en el request, descartando las DOS últimas filas
          
        for ($i=1; $i < count($tabla_registros_documento)-3; $i++) 
        {
              // Se obtienen las id de los campos que se van a almacenar. Los campos vienen separados por "-" en cada columna de la tabla 
              $vec_1 = explode("-", $tabla_registros_documento[$i]->teso_motivo_id);
              $teso_motivo_id = $vec_1[0];
            $motivo = TesoMotivo::find( $teso_motivo_id );
              //

            $vec_2 = explode("-", $tabla_registros_documento[$i]->linea_tercero_id);
            $core_tercero_id = $vec_2[0];
            if ($core_tercero_id == '') {
                $core_tercero_id = $request->core_tercero_id;
            }

            // Se les quita la etiqueta de signo peso a los textos monetarios recibidos
            // en la tabla de movimiento
            $valor = substr($tabla_registros_documento[$i]->valor, 1);

            $detalle_operacion = $tabla_registros_documento[$i]->detalle;

            TesoDocRegistroPago::create(
                                [ 'teso_encabezado_pago_id' => $registro_encabezado_doc->id ] +
                                [ 'teso_motivo_id' => $teso_motivo_id ] + 
                                [ 'core_tercero_id' => $core_tercero_id ] + 
                                [ 'detalle_operacion' => $detalle_operacion ] +
                                [ 'valor' => $valor ] +
                                [ 'estado' => 'Activo' ] );
            

            // 1.1. Para cada registro del documento de recaudo, se va actualizando el movimiento de tesorería (teso_movimientos)
            $this->datos = array_merge( $request->all(), [ 'consecutivo' => $registro_encabezado_doc->consecutivo ] );

            // Datos la caja o el la cuenta bancaria
            // Tambien se asigna el ID de la cuenta contable para el movimiento CREDITO
            $vec_3 = explode("-", $request->teso_medio_recaudo_id);
            $teso_medio_recaudo_id = $vec_3[0];
            if ( $vec_3[1] == 'Tarjeta bancaria' ) {
                $banco = TesoCuentaBancaria::find($request->teso_cuenta_bancaria_id);
                $contab_cuenta_id = $banco->contab_cuenta_id;
                $teso_caja_id = 0;
                $teso_cuenta_bancaria_id = $banco->id;
            }else{
                $caja = TesoCaja::find($request->teso_caja_id);
                $contab_cuenta_id = $caja->contab_cuenta_id;
                $teso_caja_id = $caja->id;
                $teso_cuenta_bancaria_id = 0;
            }

            if( $motivo->movimiento == 'salida' ) 
            {
                $valor_movimiento = $valor * -1;
            }

            TesoMovimiento::create( $this->datos +  
                            [ 'teso_motivo_id' => $teso_motivo_id] + 
                            [ 'teso_caja_id' => $teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id] + 
                            [ 'valor_movimiento' => $valor_movimiento] +
                            [ 'estado' => 'Activo' ]
                        );

            // 1.2. Para cada registro del documento, también se va actualizando el movimiento de contabilidad
            
            // Para el movimiento contable se guarda en detalle_operacion el detalle del encabezado del documento

            if ( $detalle_operacion == '') {
              $detalle_operacion = $request->descripcion;
            }

            // MOVIMIENTO DEBITO (SEGUN EL MOTIVO)
            $cuenta_id = $motivo->contab_cuenta_id;
            $valor_debito = $valor;
            $valor_credito = 0;

            $this->contabilizar_registro( $cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

            // MOVIMIENTO CREDITO (CAJA/BANCO)
            $cuenta = ContabCuenta::find($contab_cuenta_id);

            $valor_debito = 0;
            $valor_credito = $valor;

            $this->contabilizar_registro( $cuenta->id, $detalle_operacion, $valor_debito, $valor_credito);

          } // FIN FOR CADA LINEA DEL PAGO

        // se llama la vista de TesoDocController@show
        return redirect( 'tesoreria/pagos/'.$registro_encabezado_doc->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reg_anterior = TesoDocEncabezadoPago::where('id', '<', $id)->where('core_empresa_id', Auth::user()->empresa_id)->max('id');
        $reg_siguiente = TesoDocEncabezadoPago::where('id', '>', $id)->where('core_empresa_id', Auth::user()->empresa_id)->min('id');

        $view_pdf = TesoDocController::vista_preliminar($id,'show');

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta' => 'Documentos de pagos' ],
                ['url'=>'NO','etiqueta' => 'Consulta' ]
            ];

        return view( 'tesoreria.pagos.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function imprimir($id)
    {
        $view_pdf = TesoDocController::vista_preliminar($id,'imprimir');
       
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja='Letter';

        $pdf = \App::make('dompdf.wrapper');
        //$pdf->set_option('isRemoteEnabled', TRUE);
        $pdf->loadHTML( $view_pdf )->setPaper($tam_hoja,$orientacion);

        //echo $view_pdf;
        return $pdf->download('recibo_de_caja.pdf');
    }


    public static function vista_preliminar($id,$vista)
    {
        $encabezado_doc = TesoDocEncabezadoPago::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados_pagos.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados_pagos.core_tercero_id')
                    ->leftJoin('teso_medios_recaudo', 'teso_medios_recaudo.id', '=', 'teso_doc_encabezados_pagos.teso_medio_recaudo_id')
                    ->leftJoin('teso_cajas', 'teso_cajas.id', '=', 'teso_doc_encabezados_pagos.teso_caja_id')
                    ->leftJoin('teso_cuentas_bancarias', 'teso_cuentas_bancarias.id', '=', 'teso_doc_encabezados_pagos.teso_cuenta_bancaria_id')
                    ->where('teso_doc_encabezados_pagos.id', $id)
                    ->select(
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados_pagos.consecutivo) AS documento' ),
                                'teso_doc_encabezados_pagos.fecha',
                                'core_terceros.descripcion AS tercero',
                                'teso_doc_encabezados_pagos.descripcion AS detalle','teso_doc_encabezados_pagos.documento_soporte','teso_doc_encabezados_pagos.core_tipo_transaccion_id','teso_doc_encabezados_pagos.core_tipo_doc_app_id','teso_doc_encabezados_pagos.id','teso_doc_encabezados_pagos.creado_por','teso_doc_encabezados_pagos.consecutivo','teso_doc_encabezados_pagos.core_empresa_id','teso_doc_encabezados_pagos.core_tercero_id','teso_doc_encabezados_pagos.teso_tipo_motivo','teso_medios_recaudo.descripcion AS medio_recaudo','teso_cajas.descripcion as caja','teso_cuentas_bancarias.descripcion AS cuenta_bancaria','teso_doc_encabezados_pagos.valor_total AS valor_total')
                    ->get()[0];

        $tipo_transaccion = TipoTransaccion::find($encabezado_doc->core_tipo_transaccion_id);

        //$core_app = $tipo_transaccion->core_app;

        $tipo_doc_app = TipoDocApp::find($encabezado_doc->core_tipo_doc_app_id);

        $descripcion_transaccion = $tipo_doc_app->descripcion;

        // Se crea una tabla con los registros de medios de pagos
        $registros = TesoDocRegistroPago::leftJoin('teso_motivos','teso_motivos.id','=','teso_doc_registros_pagos.teso_motivo_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_registros_pagos.core_tercero_id')
                            ->where('teso_encabezado_pago_id',$encabezado_doc->id)
                            ->select(DB::raw($select_raw2),'teso_motivos.descripcion AS motivo','teso_doc_registros_pagos.detalle_operacion AS detalle_operacion','teso_doc_registros_pagos.valor AS valor')
                            ->get();

        $total_pago=0;
        $i=0;
        $tabla2 = '<table  class="tabla_registros" style="margin-top: -4px;">
                        <tr>
                            <td colspan="4" align="center">
                               <b>Conceptos pagados</b>
                            </td>
                        </tr>
                        <tr class="encabezado">
                            <td>
                               Concepto
                            </td>
                            <td>
                               Tercero
                            </td>
                            <td>
                               Detalle
                            </td>
                            <td>
                               Valor
                            </td>
                        </tr>';

        foreach ($registros as $registro) {

            $tabla2.='<tr  class="fila-'.$i.'" >
                            <td>
                               '.$registro->motivo.'
                            </td>
                            <td>
                               '.$registro->tercero.'
                            </td>
                            <td>
                               '.$registro->detalle_operacion.'
                            </td>
                            <td>
                               $'.number_format($registro->valor, 0, ',', '.').'
                            </td>
                        </tr>';

        }
        $tabla2.='</table>';

        $elaboro = $encabezado_doc->creado_por;
        $empresa = Empresa::find($encabezado_doc->core_empresa_id);

        $view_1 = View::make('tesoreria.incluir.encabezado_pagos',compact('encabezado_doc','descripcion_transaccion','empresa','vista') )->render();

        $view_2 = View::make('tesoreria.incluir.firmas',compact('elaboro') )->render();


        $view_pdf = '<link rel="stylesheet" type="text/css" href="'.asset('assets/css/estilos_formatos.css').'" media="screen" /> '.$view_1.$tabla2.$view_2;


        return $view_pdf;         
    }

    //
    // AJAX: enviar fila para el ingreso de registros al elaborar pago
    public static function ajax_get_fila( $teso_tipo_motivo )
    {
        
        $registros = TesoMotivo::where('teso_tipo_motivo',$teso_tipo_motivo)
                            ->where('estado','Activo')
                            ->where('core_empresa_id',Auth::user()->empresa_id)
                            ->get();
        $motivos[''] = '';
        foreach ($registros as $fila) {
            $motivos[$fila->id] = $fila->descripcion; 
        }

        $registros_2 = Tercero::where('core_empresa_id',Auth::user()->empresa_id)->orderBy('descripcion','ASC')->get();
        $terceros[''] = '';
        foreach ($registros_2 as $fila2) {
            //$terceros[$fila2->id]=$fila2->numero_identificacion." ".$fila2->descripcion; 
            $terceros[$fila2->id] = $fila2->descripcion; 
        }

        $btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>";
        $btn_confirmar = "<button type='button' class='btn btn-success btn-xs btn_confirmar'><i class='glyphicon glyphicon-ok'></i></button>";

        $tr = '<tr>
                    <td>
                        '.Form::select( 'campo_motivos', $motivos, null, [ 'id' => 'combobox_motivos', 'class' => 'lista_desplegable' ] ).'
                    </td>
                    <td>
                        '.Form::select( 'campo_terceros', $terceros, null, [ 'id' => 'combobox_terceros', 'class' => 'lista_desplegable' ] ).'
                    </td>
                    <td> '.Form::text( 'detalle_operacion', null, [ 'id' => 'col_detalle', 'class' => 'caja_texto' ] ).' </td>
                    <td> '.Form::text( 'valor', null, [ 'id' => 'col_valor', 'class' => 'caja_texto' ] ).' </td>
                    <td>'.$btn_confirmar.$btn_borrar.'</td>
                </tr>';

        return $tr;
    }

    public function get_medios_recaudo(){
        $registros = TesoMedioRecaudo::all();  
        $vec_m['']=''; 
        foreach ($registros as $fila) {
            $vec_m[$fila->id.'-'.$fila->comportamiento]=$fila->descripcion; 
        }
        
        return $vec_m;
    }

    public function get_cajas(){
        $registros = TesoCaja::where('core_empresa_id',Auth::user()->empresa_id)->get();       
        foreach ($registros as $fila) {
            $vec_m[$fila->id]=$fila->descripcion; 
        }
        
        return $vec_m;
    }

    public function get_cuentas_bancarias(){
        $registros = TesoCuentaBancaria::leftJoin('teso_entidades_financieras','teso_entidades_financieras.id','=','teso_cuentas_bancarias.entidad_financiera_id')
                    ->where('core_empresa_id',Auth::user()->empresa_id)
                    ->select('teso_cuentas_bancarias.id','teso_cuentas_bancarias.descripcion AS cta_bancaria','teso_entidades_financieras.descripcion AS entidad_financiera')
                    ->get();        
        foreach ($registros as $fila) {
            $vec_m[$fila->id] = $fila->entidad_financiera.': '.$fila->cta_bancaria; 
        }
        
        return $vec_m;
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