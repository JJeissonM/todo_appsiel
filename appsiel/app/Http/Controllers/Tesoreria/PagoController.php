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
use App\Http\Controllers\Sistema\CrudController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos
use App\Sistema\Html\BotonesAnteriorSiguiente;


// Modelos
use App\Sistema\Modelo;
use App\Core\Tercero;

use App\Matriculas\Grado;
use App\Matriculas\Estudiante;
use App\Core\Colegio;
use App\Core\Empresa;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\CxC\CxcMovimiento;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabCuenta;

class PagoController extends TransaccionController
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

        $this->set_variables_globales();
        $id_transaccion = $this->transaccion->id;

        $lista_campos = ModeloController::get_campos_modelo($this->modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$this->transaccion,$lista_campos,$cantidad_campos,'create');

        $form_create = [
                      'url' => $this->modelo->url_form_create,
                      'campos' => $lista_campos
                  ];

        $miga_pan = [
              [ 'url' => $this->app->app.'?id='.Input::get('id'),'etiqueta' => $this->app->descripcion ],
              [ 'url' => 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'etiqueta' => $this->modelo->descripcion ],
              [ 'url' => 'NO', 'etiqueta' => 'Crear: '.$this->transaccion->descripcion]
          ];



        $motivos = [''];//PagoController::get_motivos($id_transaccion);
        $medios_recaudo = PagoController::get_medios_recaudo();
        $cajas = PagoController::get_cajas();
        $cuentas_bancarias = PagoController::get_cuentas_bancarias();

        /*$registros = Tercero::all();
        //$vec_m[''] = ''; // si quito esto queda seleccionado el primer registro por defecto, entonces no tengo que hacer validaciones para evitar que este campo se vaya vacío 
        foreach ($registros as $fila) {
            $vec_m[$fila->id]=$fila->apellido1.' '.$fila->apellido2.' '.$fila->nombre1.' '.$fila->razon_social; 
        }*/        
        $terceros = [''];//$vec_m;



        return view('tesoreria.pagos.create', compact( 'form_create','id_transaccion','motivos','miga_pan','medios_recaudo','cajas','cuentas_bancarias', 'terceros' ) );
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

        // 1ro. se guardan los registros asociados al encabezado del documento
        // Se recorre la tabla enviada en el request, descartando las DOS últimas filas
        $total_documento = 0;
        $cant = count($tabla_registros_documento)-3;
        for ($i=1; $i < $cant; $i++) 
        {
            // Se obtienen las id de los campos que se van a almacenar. Los campos vienen separados por "-" en cada columna de la tabla 
            $vec_1 = explode("-", $tabla_registros_documento[$i]->teso_motivo_id);
            $teso_motivo_id = $vec_1[0];
            $motivo = TesoMotivo::find( $teso_motivo_id );
              //


            $vec_2 = explode("-", $tabla_registros_documento[$i]->linea_tercero_id);
            $core_tercero_id = $vec_2[0];
            if ( $core_tercero_id == '') {
                $core_tercero_id = $request->core_tercero_id;
            }

            // Se les quita la etiqueta de signo peso a los textos monetarios recibidos
            // en la tabla de movimiento
            $valor = (float)substr($tabla_registros_documento[$i]->valor, 1);

            $detalle_operacion = $tabla_registros_documento[$i]->detalle;

            TesoDocRegistro::create(
                                [ 'teso_encabezado_id' => $doc_encabezado->id ] +
                                [ 'teso_motivo_id' => $teso_motivo_id ] + 
                                [ 'core_tercero_id' => $core_tercero_id ] + 
                                [ 'detalle_operacion' => $detalle_operacion ] +
                                [ 'valor' => $valor ] +
                                [ 'estado' => 'Activo' ] );
            

            // 1.1. Para cada registro del documento, se va actualizando el movimiento de tesorería (teso_movimientos)
            $this->datos = array_merge( $request->all(), [ 'consecutivo' => $doc_encabezado->consecutivo, 'core_tercero_id' => $core_tercero_id ] );            

            // Datos la caja o el la cuenta bancaria
            // Tambien se asigna el ID de la cuenta contable para el movimiento CREDITO
            $vec_3 = explode('-',$request->teso_medio_recaudo_id);
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

            // Los pagos son movimiento de salida, se registran con signo negativo en el movimiento
            $valor_movimiento = $valor * -1;

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


            // Generar CxP a favor. Saldo negativo por pagar (a favor de la empresa)
            if ( $motivo->teso_tipo_motivo == 'Anticipo proveedor' )
            {
                $this->datos['valor_documento'] = $valor * -1;
                $this->datos['valor_pagado'] = 0;
                $this->datos['saldo_pendiente'] = $valor * -1;
                $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
                $this->datos['estado'] = 'Pendiente';
                CxpMovimiento::create( $this->datos );
            }

            // Generar CxP porque se utilizó dinero de un agente externo (banco, coopertaiva, tarjeta de crédito).
            if ( $motivo->teso_tipo_motivo == 'Prestamo financiero' )
            {
                $this->datos['valor_documento'] = $valor;
                $this->datos['valor_pagado'] = 0;
                $this->datos['saldo_pendiente'] = $valor;
                $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
                $this->datos['estado'] = 'Pendiente';
                CxpMovimiento::create( $this->datos );
            }

            // Generar CxC por algún dinero prestado o anticipado a trabajadores o clientes.
            if ( $motivo->teso_tipo_motivo == 'Pago anticipado' )
            {
                $this->datos['valor_documento'] = $valor;
                $this->datos['valor_pagado'] = 0;
                $this->datos['saldo_pendiente'] = $valor;
                $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
                $this->datos['estado'] = 'Pendiente';
                CxcMovimiento::create( $this->datos );
            }


            $total_documento += $valor;

          } // FIN FOR CADA LINEA DEL PAGO


        // Un solo movimiento contable de (CR) CAJA O BANCO
        // MOVIMIENTO CREDITO (CAJA/BANCO)
        $cuenta = ContabCuenta::find($contab_cuenta_id);

        $valor_debito = 0;
        $valor_credito = $total_documento;

        $this->contabilizar_registro( $cuenta->id, $detalle_operacion, $valor_debito, $valor_credito);

        // se llama la vista de PagoController@show
        return redirect( 'tesoreria/pagos/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
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

        $doc_registros = TesoDocRegistro::get_registros_impresion( $doc_encabezado->id );

        //dd( $doc_registros );

        $empresa = $this->empresa;

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $documento_vista = '';//View::make( 'tesoreria.pagos.documento_vista', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad' ) )->render();
        $id_transaccion = $doc_encabezado->core_tipo_transaccion_id;

        $miga_pan = [
                [ 'url' => $this->app->app.'?id='.Input::get('id'),'etiqueta' => $this->app->descripcion ],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $this->modelo->descripcion ],
                ['url'=>'NO','etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo]
            ];
        
        return view( 'tesoreria.pagos.show', compact( 'id', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan','doc_encabezado','doc_registros','registros_contabilidad','empresa') );
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
        $modelo = Modelo::find(Input::get('id_modelo'));

        $reg_anterior = TesoDocEncabezado::where('id', '<', $id)->where('core_empresa_id', Auth::user()->empresa_id)->max('id');
        $reg_siguiente = TesoDocEncabezado::where('id', '>', $id)->where('core_empresa_id', Auth::user()->empresa_id)->min('id');

        $doc_encabezado = TesoDocEncabezado::get_registro_impresion( $id );

        $doc_registros = TesoDocRegistro::get_registros_impresion( $doc_encabezado->id );

        //dd( $doc_pagados );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $documento_vista = View::make( 'tesoreria.pagos.documento_imprimir', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad' ) )->render();
       
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja='Letter';

        $pdf = \App::make('dompdf.wrapper');
        //$pdf->set_option('isRemoteEnabled', TRUE);
        $pdf->loadHTML( $documento_vista )->setPaper($tam_hoja,$orientacion);

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }


    //
    // AJAX: enviar fila para el ingreso de registros al elaborar pago
    public static function ajax_get_fila( $teso_tipo_motivo )
    {

        $btn_confirmar = "<button class='btn btn-success btn-xs btn_confirmar' style='display: inline;'><i class='fa fa-check'></i></button>";
        $btn_borrar = "<button class='btn btn-danger btn-xs btn_eliminar' style='display: inline;'><i class='fa fa-trash'></i></button>";

        $tr = '<tr id="linea_ingreso_default" class="linea_ingreso_default">
                    <td>
                        '.Form::text( 'motivo_input', null, [ 'class' => 'form-control text_input_sugerencias', 'id' => 'motivo_input', 'data-url_busqueda' => url( 'teso_consultar_motivos' ), 'autocomplete'  => 'off' ] ).'
                        '.Form::hidden( 'campo_motivos', null, [ 'id' => 'combobox_motivos' ] ).'
                    </td>
                    <td>
                        '.Form::text( 'tercero_input', null, [ 'class' => 'form-control text_input_sugerencias', 'id' => 'tercero_input', 'data-url_busqueda' => url('core_consultar_terceros_v2'), 'autocomplete'  => 'off' ] ).'
                        '.Form::hidden( 'campo_terceros', null, [ 'id' => 'combobox_terceros' ] ).'
                    </td>
                    <td> '.Form::text( 'detalle_operacion', null, [ 'id' => 'col_detalle', 'class' => 'form-control' ] ).' </td>
                    <td> '.Form::text( 'valor', null, [ 'id' => 'col_valor', 'class' => 'form-control' ] ).' </td>
                    <td> <div class="btn-group">'.$btn_confirmar.$btn_borrar.'</div> </td>
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

        // 2do. Se elimina el documento del movimimeto de cuentas por pagar
        CxpMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el movimiento de tesorería
        TesoMovimiento::where($array_wheres)->delete();

        // 5to. Se marcan como anulados los registros del documento
        TesoDocRegistro::where( 'teso_encabezado_id', $documento->id )->update( [ 'estado' => 'Anulado'] );

        // 4to. Se marca como anulado el documento
        $documento->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

      return redirect( 'tesoreria/pagos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Documento de pago anulado correctamente.');
    }
}