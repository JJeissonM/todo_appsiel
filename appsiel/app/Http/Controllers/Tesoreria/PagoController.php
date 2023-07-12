<?php

namespace App\Http\Controllers\Tesoreria;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Sistema\Modelo;
use App\Core\Tercero;
use App\Core\TipoDocApp;
use App\Core\Empresa;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\CxC\CxcMovimiento;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;

use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\ContabMovimiento;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class PagoController extends TransaccionController
{
    protected $datos = [];

    protected $duplicado = false;

    protected $doc_encabezado;

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

        $this->set_variables_globales();
        $id_transaccion = $this->transaccion->id;

        $lista_campos = ModeloController::get_campos_modelo($this->modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$this->transaccion,$lista_campos,$cantidad_campos,'create');

        $form_create = [
                      'url' => json_decode( app( $this->modelo->name_space )->urls_acciones )->store,
                      'campos' => $lista_campos
                  ];

        $miga_pan = [
              [ 'url' => $this->app->app.'?id='.Input::get('id'),'etiqueta' => $this->app->descripcion ],
              [ 'url' => 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'etiqueta' => $this->modelo->descripcion ],
              [ 'url' => 'NO', 'etiqueta' => 'Crear: '.$this->transaccion->descripcion]
          ];

        $archivo_js = app($this->modelo->name_space)->archivo_js;

        /*
        $medios_recaudo = PagoController::get_medios_recaudo();
        $cajas = PagoController::get_cajas();
        $cuentas_bancarias = PagoController::get_cuentas_bancarias();
        'medios_recaudo','cajas','cuentas_bancarias',
        */
        
        $lineas_tabla_ingreso_registros = '';

        $registro = null;
        $linea_num = 0;
        $mensaje_duplicado = '';

        return view('tesoreria.pagos.create', compact( 'form_create','miga_pan', 'registro', 'archivo_js', 'lineas_tabla_ingreso_registros', 'linea_num', 'mensaje_duplicado' ) );
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

        $tabla_registros_documento = json_decode($request->tabla_registros_documento);

        $vec = $this->almacenar_lineas_registros( $request, $tabla_registros_documento, $doc_encabezado );

        // Un solo movimiento contable de (CR) CAJA O BANCO
        // MOVIMIENTO CREDITO (CAJA/BANCO)
        $this->contabilizar_registro( $vec[0], '', 0, $vec[1]);

        // se llama la vista de PagoController@show
        return redirect( 'tesoreria/pagos/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }


    public function almacenar_lineas_registros( $request, $tabla_registros_documento, $doc_encabezado )
    {
        $total_documento = 0;

        // Se recorre la tabla enviada en el request, descartando las TRES últimas filas
        $cant = count($tabla_registros_documento)-3;

        for ($i=0; $i < $cant; $i++) 
        {
            // Se obtienen las id de los campos que se van a almacenar. Los campos vienen separados por "-" en cada columna de la tabla 

            $vec_1 = explode("-", $tabla_registros_documento[$i]->teso_motivo_id);
            $teso_motivo_id = $vec_1[0];
            $motivo = TesoMotivo::find( $teso_motivo_id );

            $vec_2 = explode("-", $tabla_registros_documento[$i]->linea_tercero_id);
            $core_tercero_id = $vec_2[0];
            if ( $core_tercero_id == '') {
                $core_tercero_id = $request->core_tercero_id;
            }

            $this->datos = array_merge( $request->all(), [ 'consecutivo' => $doc_encabezado->consecutivo, 'core_tercero_id' => $core_tercero_id ] );

            $vec_3 = explode( '-', $request->teso_medio_recaudo_id );
            $teso_medio_recaudo_id = $vec_3[0];

            if ( $vec_3[1] == 'Tarjeta bancaria' ) 
            {
                $banco = TesoCuentaBancaria::find($request->teso_cuenta_bancaria_id);
                $contab_cuenta_id = $banco->contab_cuenta_id;
                $teso_caja_id = 0;
                $this->datos['teso_caja_id'] = 0;
                $teso_cuenta_bancaria_id = $banco->id;
            }else{
                $caja = TesoCaja::find($request->teso_caja_id);
                $contab_cuenta_id = $caja->contab_cuenta_id;
                $teso_caja_id = $caja->id;
                $teso_cuenta_bancaria_id = 0;
                $this->datos['teso_cuenta_bancaria_id'] = 0;
            }

            // Se les quita la etiqueta de signo peso a los textos monetarios recibidos
            // en la tabla de movimiento
            $valor = (float)substr($tabla_registros_documento[$i]->valor, 1);

            $detalle_operacion = $tabla_registros_documento[$i]->detalle;

            TesoDocRegistro::create(
                                [ 'teso_encabezado_id' => $doc_encabezado->id ] +
                                [ 'teso_medio_recaudo_id' => $teso_medio_recaudo_id ] + 
                                [ 'teso_caja_id' => $teso_caja_id ] + 
                                [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id ] + 
                                [ 'teso_motivo_id' => $teso_motivo_id ] + 
                                [ 'core_tercero_id' => $core_tercero_id ] + 
                                [ 'detalle_operacion' => $detalle_operacion ] +
                                [ 'valor' => $valor ] +
                                [ 'estado' => 'Activo' ] );
            

            // 1.1. Para cada registro del documento, se va actualizando el movimiento de tesorería (teso_movimientos)
            

            // Los pagos son movimiento de salida, se registran con signo negativo en el movimiento
            $valor_movimiento = $valor * -1; // No se tiene en cuenta el motivo

            TesoMovimiento::create( $this->datos +  
                            [ 'teso_motivo_id' => $teso_motivo_id] + 
                            [ 'teso_caja_id' => $teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id] + 
                            [ 'teso_medio_recaudo_id' => $teso_medio_recaudo_id] + 
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

            // Generar CxP a favor. Saldo negativo por pagar (a favor de la empresa)
            if ( $motivo->teso_tipo_motivo == 'Anticipo proveedor' )
            {
                $this->almacenar_cxp( $this->datos, $valor );
            }

            // Generar CxC por algún dinero prestado o anticipado a trabajadores o clientes.
            if ( $motivo->teso_tipo_motivo == 'Pago anticipado' )
            {
                $this->almacenar_cxc( $this->datos, $valor );
            }

            $total_documento += $valor;

        } // FIN FOR - CADA LINEA DEL PAGO

        return [ $contab_cuenta_id, $total_documento];
    }

    public function almacenar_cxp( $datos, $valor )
    {
        $datos['valor_documento'] = $valor * -1;
        $datos['valor_pagado'] = 0;
        $datos['saldo_pendiente'] = $valor * -1;
        $datos['fecha_vencimiento'] = $datos['fecha'];
        $datos['estado'] = 'Pendiente';
        CxpMovimiento::create( $datos );
    }

    public function almacenar_cxc( $datos, $valor )
    {
        $datos['valor_documento'] = $valor;
        $datos['valor_pagado'] = 0;
        $datos['saldo_pendiente'] = $valor;
        $datos['fecha_vencimiento'] = $datos['fecha'];
        $datos['estado'] = 'Pendiente';
        CxcMovimiento::create( $datos );
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
        $id_transaccion = $this->transaccion->id;

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        $encabezado_documento = TesoDocEncabezado::find( $id );

        $doc_registros = TesoDocRegistro::get_registros_impresion( $doc_encabezado->id );

        $empresa = $this->empresa;

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $documento_vista = '';//View::make( 'tesoreria.pagos.documento_vista', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad' ) )->render();
        $id_transaccion = $doc_encabezado->core_tipo_transaccion_id;

        $miga_pan = [
                [ 'url' => $this->app->app.'?id='.Input::get('id'),'etiqueta' => $this->app->descripcion ],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $this->modelo->descripcion ],
                ['url'=>'NO','etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo]
            ];
        
        return view( 'tesoreria.pagos.show', compact( 'id', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan','doc_encabezado','doc_registros','registros_contabilidad','empresa', 'encabezado_documento') );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $this->set_variables_globales();

        // Se obtiene el registro a modificar del modelo
        $registro = app($this->modelo->name_space)->find($id);

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, $registro,'edit');

        $cantidad_campos = count( $lista_campos );
        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, $cantidad_campos,'edit');

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );

        if ( !$this->duplicado )
        {
            if( !$this->verificar_permitir_editar( $doc_registros ) )
            {
               return redirect( 'tesoreria/pagos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Los documentos que tienen MOTIVOS que afectan movimientos de CxP o CxC no pueden ser modificados.');
            }
        }

        $tercero_encabezado_numero_identificacion = $doc_encabezado->numero_identificacion;

        $lineas_tabla_ingreso_registros = View::make( 'tesoreria.pagos.lineas_tabla_ingreso_registros', compact('doc_registros', 'tercero_encabezado_numero_identificacion') )->render();

        $linea_num = count( $doc_registros->toArray() );

        $form_create = [
                        'url' => str_replace( 'id_fila', $registro->id, json_decode( app( $this->modelo->name_space )->urls_acciones )->update ),
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Modificar: '.$doc_encabezado->documento_transaccion_prefijo_consecutivo );

        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $mensaje_duplicado = '';
        if ( $this->duplicado )
        {
            $mensaje_duplicado = '<div class="alert alert-success">
                                      <strong> ¡Documento duplicado correctamente! </strong>
                                    </div>

                                    <div class="alert alert-warning">
                                      <strong> ¡Nota! </strong> Debe guardar el documento para agregar los registros al movimiento de tesorería.
                                    </div>';
            $this->duplicado = false;
        }


        
        return view( 'tesoreria.pagos.create', compact( 'form_create','miga_pan', 'registro', 'archivo_js', 'lineas_tabla_ingreso_registros', 'linea_num', 'mensaje_duplicado' ) );
    }

    public function verificar_permitir_editar( $doc_registros )
    {   
        $permitir_editar = true;

        foreach ($doc_registros as $linea)
        {
            if ( !is_null( $linea->motivo_id ) )
            {
                $motivo = TesoMotivo::find( $linea->motivo_id );
            }else{
                $motivo = $linea->motivo;
            }

            if ( $motivo->teso_tipo_motivo == 'Anticipo proveedor' || $motivo->teso_tipo_motivo == 'Prestamo financiero' ||  $motivo->teso_tipo_motivo == 'Pago anticipado' )
            {
                $permitir_editar = false;
            }

        }

        return $permitir_editar;
    }

    public function update(Request $request, $id)
    {
       $modelo = Modelo::find( $request->url_id_modelo );

        $doc_encabezado = app( $modelo->name_space )->find($id);

        // Borrar registros viejos asociados al documento
        TesoDocRegistro::where( 'teso_encabezado_id', $id )->delete();

        TesoMovimiento::where( 'core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id )
                        ->where( 'core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id )
                        ->where( 'consecutivo', $doc_encabezado->consecutivo )
                        ->delete();

        ContabMovimiento::where( 'core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id )
                        ->where( 'core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id )
                        ->where( 'consecutivo', $doc_encabezado->consecutivo )
                        ->delete();


        $request['core_tipo_transaccion_id'] = $doc_encabezado->core_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $doc_encabezado->core_tipo_doc_app_id;
        $request['consecutivo'] = $doc_encabezado->consecutivo;

        // Contabilizar nuevos registros
        $tabla_registros_documento = json_decode($request->tabla_registros_documento);
        
        $vec = $this->almacenar_lineas_registros( $request, $tabla_registros_documento, $doc_encabezado );

        $this->contabilizar_registro( $vec[0], '', 0, $vec[1]);

        $doc_encabezado->fill( $request->all() );
        $doc_encabezado->save();

        return redirect( 'tesoreria/pagos/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }

    public function imprimir($id)
    {      
       $documento_vista = $this->generar_documento_vista( $id, 'tesoreria.formatos_impresion.pagos.'.Input::get('formato_impresion_id') );

        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja='Letter';

        $pdf = App::make('dompdf.wrapper');
        //$pdf->set_option('isRemoteEnabled', TRUE);
        $pdf->loadHTML( $documento_vista )->setPaper($tam_hoja,$orientacion);

        return $pdf->stream( $this->doc_encabezado->documento_transaccion_descripcion.' - '.$this->doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }


    public function generar_documento_vista( $id, $ruta_vista )
    {
        $this->doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        $doc_registros = TesoDocRegistro::get_registros_impresion( $this->doc_encabezado->id );

        $empresa = Empresa::find( $this->doc_encabezado->core_empresa_id );

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $this->doc_encabezado );
        $doc_encabezado = $this->doc_encabezado;

        return View::make( $ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad' ) )->render();
    }


    //
    // AJAX: enviar fila para el ingreso de registros al elaborar pago
    public static function ajax_get_fila( $teso_tipo_motivo )
    {

        $btn_confirmar = "<button class='btn btn-success btn-xs btn_confirmar' style='display: inline;'><i class='fa fa-check'></i></button>";
        $btn_borrar = "<button class='btn btn-danger btn-xs btn_eliminar' style='display: inline;'><i class='fa fa-trash'></i></button>";

        $tr = '<tr id="linea_ingreso_default" class="linea_ingreso_default">
                    <td>
                        '.FormFacade::text( 'motivo_input', null, [ 'class' => 'form-control text_input_sugerencias', 'id' => 'motivo_input', 'data-url_busqueda' => url( 'teso_consultar_motivos' ).'?movimiento=salida', 'autocomplete'  => 'off' ] ).'
                        '.FormFacade::hidden( 'campo_motivos', null, [ 'id' => 'combobox_motivos' ] ).'
                    </td>
                    <td>
                        '.FormFacade::text( 'tercero_input', null, [ 'class' => 'form-control text_input_sugerencias', 'id' => 'tercero_input', 'data-url_busqueda' => url('core_consultar_terceros_v2'), 'autocomplete'  => 'off' ] ).'
                        '.FormFacade::hidden( 'campo_terceros', null, [ 'id' => 'combobox_terceros' ] ).'
                    </td>
                    <td> '.FormFacade::text( 'detalle_operacion', null, [ 'id' => 'col_detalle', 'class' => 'form-control' ] ).' </td>
                    <td> '.FormFacade::text( 'valor', null, [ 'id' => 'col_valor', 'class' => 'form-control' ] ).' </td>
                    <td> <div class="btn-group">'.$btn_confirmar.$btn_borrar.'</div> </td>
                </tr>';

        return $tr;
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

    /**
     Anular un Pago (distinto a Pago de cxp)
     */
    public function anular_pago($id)
    {
        $documento = TesoDocEncabezado::find($id);
        $modificado_por = Auth::user()->email;

        $array_wheres = ['core_empresa_id'=>$documento->core_empresa_id, 
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo];

        // >>> Validaciones inciales

        $tabla_existe = DB::select( DB::raw( "SHOW TABLES LIKE 'cxp_abonos'" ) );
        if ( !empty( $tabla_existe ) )
        {
            // Está en un documento cruce de cxp?
            $cantidad = CxpAbono::where($array_wheres)
                                ->where('doc_cruce_transacc_id','<>',0)
                                ->count();

            if($cantidad != 0)
            {
                return redirect( 'tesoreria/pagos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Pago NO puede ser anulado. Está en documento cruce de CxP.');
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

        // 5to. Se marcan como anulados los registros del documento
        TesoDocRegistro::where( 'teso_encabezado_id', $documento->id )->update( [ 'estado' => 'Anulado'] );

        // 4to. Se marca como anulado el documento
        $documento->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

      return redirect( 'tesoreria/pagos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Documento de pago anulado correctamente.');
    }

    public function duplicar_documento( $doc_encabezado_id )
    {
        $doc_encabezado = TesoDocEncabezado::find( $doc_encabezado_id );

        $registros_doc_encabezado = TesoDocRegistro::where( 'teso_encabezado_id', $doc_encabezado->id )->get();

        // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
        $consecutivo = TipoDocApp::get_consecutivo_actual( $doc_encabezado->core_empresa_id, $doc_encabezado->core_tipo_doc_app_id) + 1;

        // Se incementa el consecutivo para ese tipo de documento y la empresa
        TipoDocApp::aumentar_consecutivo($doc_encabezado->core_empresa_id, $doc_encabezado->core_tipo_doc_app_id);

        $nuevo_doc_encabezado = $doc_encabezado->replicate();
        $nuevo_doc_encabezado->consecutivo = $consecutivo;
        $nuevo_doc_encabezado->save();

        foreach ($registros_doc_encabezado as $linea )
        {
            $nueva_linea = $linea->toArray();
            $nueva_linea['teso_encabezado_id'] = $nuevo_doc_encabezado->id;

            $nueva_linea_registro = TesoDocRegistro::create( $nueva_linea );
        }

        $this->duplicado = true;

        return $this->edit( $nuevo_doc_encabezado->id );
    }

    public function recontabilizar_un_documento( $doc_encabezado_id )
    {
        $documento = TesoDocEncabezado::find( $doc_encabezado_id );

        // Obtener líneas de registros del documento
        $registros_documento = $documento->lineas_registros;

        if( !$this->verificar_permitir_editar( $registros_documento ) )
        {
           return redirect( 'tesoreria/pagos/' . $doc_encabezado_id . '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Los documentos que tienen MOTIVOS que afectan movimientos de CxP o CxC no pueden ser recontabilizados.');
        }

        $datos = $documento->toArray();

        $array_wheres = ['core_empresa_id'=>$documento->core_empresa_id, 
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo];

        TesoMovimiento::where( $array_wheres )->delete(); 

        ContabMovimiento::where( $array_wheres )->delete();

        $total_credito = 0;
        foreach ($registros_documento as $linea)
        {
            TesoMovimiento::create( $datos +  
                            [ 'teso_encabezado_id' => $linea->teso_encabezado_id] + 
                            [ 'teso_motivo_id' => $linea->teso_motivo_id] + 
                            [ 'teso_caja_id' => $linea->teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $linea->teso_cuenta_bancaria_id] + 
                            [ 'teso_medio_recaudo_id' => $linea->teso_medio_recaudo_id] + 
                            [ 'valor_movimiento' => $linea->valor ] +
                            [ 'descripcion' => $linea->detalle_operacion ] +
                            [ 'estado' => 'Activo' ]
                        );


            $detalle_operacion = 'Recontabilizado. ' . $linea->descripcion;

            // MOVIMIENTO DEBITO (SEGUN EL MOTIVO)
            $cuenta_id = $linea->motivo->contab_cuenta_id;
            $valor_debito = $linea->valor;
            $valor_credito = 0;

            ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $linea->teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $linea->teso_cuenta_bancaria_id]
                        );

            $total_credito += $linea->valor;
        }

        if ( $documento->teso_caja_id == 0 ) 
        {
            $banco = TesoCuentaBancaria::find( $documento->teso_cuenta_bancaria_id );
            $contab_cuenta_id = $banco->contab_cuenta_id;
        }else{
            $caja = TesoCaja::find( $documento->teso_caja_id );
            $contab_cuenta_id = $caja->contab_cuenta_id;
        }

        $detalle_operacion = $documento->descripcion . ' Recontabilizado.';

        ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => 0 ] + 
                            [ 'valor_credito' => ($total_credito * -1) ] + 
                            [ 'valor_saldo' => ( 0 - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $linea->teso_caja_id] + 
                            [ 'teso_cuenta_bancaria_id' => $linea->teso_cuenta_bancaria_id]
                        );

        return redirect( 'tesoreria/pagos/'.$doc_encabezado_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( 'flash_message', 'Documento Recontabilizado.' );
    }
}