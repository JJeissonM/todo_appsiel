<?php

namespace App\Http\Controllers\Contabilidad;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use Auth;
use Form;
use View;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Core\Tercero;
use App\Core\Empresa;

use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Contabilidad\ContabMovimiento;

use App\CxP\CxpMovimiento;
use App\CxC\CxcMovimiento;

class ContabilidadController extends TransaccionController
{
    protected $datos = [];
    protected $grupos_cuentas = [];

    /* El método index() está en TransaccionController */


    public function create()
    {
        $this->set_variables_globales();

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'contabilidad.create' );
    }




    public function store( Request $request )
    {

        //dd( $request->all() );
        $registro_encabezado_doc = $this->crear_encabezado_documento($request, $request->url_id_modelo);

        $tabla_registros_documento = json_decode($request->tabla_registros_documento);

        // 1ro. se guardan los registros asociados al encabezado del documento
        // Se recorre la tabla enviada en el request, descartando las DOS últimas filas
        for ($i=0; $i < count($tabla_registros_documento)-2; $i++)
        {
            // Se obtienen las id de los campos que se van a almacenar. Los campos vienen separados por "-" en cada columna de la tabla 
            $vec_1 = explode("-", $tabla_registros_documento[$i]->Cuenta);
            $contab_cuenta_id = $vec_1[0];

            $vec_2 = explode("-", $tabla_registros_documento[$i]->Tercero);


            $core_tercero_id = (int)$vec_2[0];
            
            if ( $core_tercero_id == 0 )
            {
                $core_tercero_id = (int)$request->core_tercero_id;
            }

            $detalle_operacion = $tabla_registros_documento[$i]->Detalle;

            // Se les quita la etiqueta de signo peso a los textos monetarios recibidos
            // en la tabla de movimiento
            $valor_debito = substr($tabla_registros_documento[$i]->debito, 1);
            $valor_credito = substr($tabla_registros_documento[$i]->credito, 1);

            ContabDocRegistro::create(
                            [ 'contab_doc_encabezado_id' => $registro_encabezado_doc->id ] + 
                            [ 'contab_cuenta_id' => (int)$contab_cuenta_id ] + 
                            [ 'core_tercero_id' => $core_tercero_id ] + 
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => (float)$valor_debito] + 
                            [ 'valor_credito' => (float)$valor_credito]
                        );


            // 1.1. Para cada registro del documento, también se va actualizando el movimiento de contabilidad
            
            // Para el movimiento contable se guarda en detalle_operacion el detalle del encabezado del documento
            if ($detalle_operacion == '') {
                $detalle_operacion = $request->descripcion;
            }

            $this->datos = array_merge( $request->all(), ['core_tercero_id' => $core_tercero_id , 'consecutivo' => $registro_encabezado_doc->consecutivo] );

            $this->contabilizar_registro( $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

            // Generar CxP.
            if ( $tabla_registros_documento[$i]->tipo_transaccion == 'crear_cxp' )
            {
                $this->datos['valor_documento'] = $valor_credito;
                $this->datos['valor_pagado'] = 0;
                $this->datos['saldo_pendiente'] = $valor_credito;
                $this->datos['fecha_vencimiento'] = $tabla_registros_documento[$i]->fecha_vencimiento;
                $this->datos['doc_proveedor_consecutivo'] = $tabla_registros_documento[$i]->documento_soporte_tercero;
                $this->datos['estado'] = 'Pendiente';
                CxpMovimiento::create( $this->datos );
            }

            // Generar CxC.
            if ( $tabla_registros_documento[$i]->tipo_transaccion == 'crear_cxc' )
            {
                $this->datos['valor_documento'] = $valor_debito;
                $this->datos['valor_pagado'] = 0;
                $this->datos['saldo_pendiente'] = $valor_debito;
                $this->datos['fecha_vencimiento'] = $tabla_registros_documento[$i]->fecha_vencimiento;
                $this->datos['estado'] = 'Pendiente';
                CxcMovimiento::create( $this->datos );
            }

        }

        return redirect( 'contabilidad/'.$registro_encabezado_doc->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }


    /**
     * Editar documento
     */
    public function edit($id)
    {
        $this->set_variables_globales();

        // Se obtiene el registro a modificar del modelo
        $registro = app($this->modelo->name_space)->find($id);

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, $registro,'edit');

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );

        $tercero_encabezado_numero_identificacion = $doc_encabezado->numero_identificacion; 
        $lineas_documento = View::make( 'contabilidad.incluir.lineas_documento', compact('doc_registros', 'tercero_encabezado_numero_identificacion') )->render();//'';//
        $linea_num = count( $doc_registros->toArray() );

        $url_action = 'web/'.$id.$this->variables_url;
        
        if ($this->modelo->url_form_create != '') {
            $url_action = $this->modelo->url_form_create.'/'.$id.$this->variables_url;
        }

        $form_create = [
                        'url' => $url_action,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Modificar: '.$doc_encabezado->documento_transaccion_prefijo_consecutivo );

        $archivo_js = app($this->modelo->name_space)->archivo_js;

        return view( 'contabilidad.edit', compact( 'form_create', 'miga_pan', 'registro', 'archivo_js', 'lineas_documento', 'linea_num') );
    }




    //     A L M A C E N A R  LA MODIFICACION DE UN REGISTRO
    public function update(Request $request, $id)
    {
        $modelo = Modelo::find( $request->url_id_modelo );

        $registro_encabezado_doc = app( $modelo->name_space )->find($id);

        // Borrar registros viejos del documento
        $registros_doc = ContabDocRegistro::where( 'contab_doc_encabezado_id', $id )->delete();
        $registros_doc = ContabMovimiento::where( 'core_tipo_transaccion_id', $registro_encabezado_doc->core_tipo_transaccion_id )
                                            ->where( 'core_tipo_doc_app_id', $registro_encabezado_doc->core_tipo_doc_app_id )
                                            ->where( 'consecutivo', $registro_encabezado_doc->consecutivo )
                                            ->delete();


        $request['core_tipo_transaccion_id'] = $registro_encabezado_doc->core_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $registro_encabezado_doc->core_tipo_doc_app_id;
        $request['consecutivo'] = $registro_encabezado_doc->consecutivo;

        // Contabilizar nuevos registros
        $tabla_registros_documento = json_decode($request->tabla_registros_documento);
        // 1ro. se guardan los registros asociados al encabezado del documento
        // Se recorre la tabla enviada en el request, descartando las DOS últimas filas
        for ($i=0; $i < count($tabla_registros_documento)-2; $i++)
        {
            // Se obtienen las id de los campos que se van a almacenar. Los campos vienen separados por "-" en cada columna de la tabla 
            $vec_1 = explode("-", $tabla_registros_documento[$i]->Cuenta);
            $contab_cuenta_id = $vec_1[0];

            $vec_2 = explode("-", $tabla_registros_documento[$i]->Tercero);


            $core_tercero_id = (int)$vec_2[0];            
            if ( $core_tercero_id == 0 )
            {
                $core_tercero_id = (int)$request->core_tercero_id;
            }

            $detalle_operacion = $tabla_registros_documento[$i]->Detalle;

            // Se les quita la etiqueta de signo peso a los textos monetarios recibidos
            // en la tabla de movimiento
            $valor_debito = substr($tabla_registros_documento[$i]->debito, 1);
            $valor_credito = substr($tabla_registros_documento[$i]->credito, 1);

            ContabDocRegistro::create(
                            [ 'contab_doc_encabezado_id' => $registro_encabezado_doc->id ] + 
                            [ 'contab_cuenta_id' => (int)$contab_cuenta_id ] + 
                            [ 'core_tercero_id' => $core_tercero_id ] + 
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => (float)$valor_debito] + 
                            [ 'valor_credito' => (float)$valor_credito]
                        );


            // 1.1. Para cada registro del documento, también se va actualizando el movimiento de contabilidad
            
            // Para el movimiento contable se guarda en detalle_operacion el detalle del encabezado del documento
            if ($detalle_operacion == '')
            {
                $detalle_operacion = $request->descripcion;
            }

            $this->datos = array_merge( $request->all(), ['core_tercero_id' => $core_tercero_id , 'consecutivo' => $registro_encabezado_doc->consecutivo] );

            $this->contabilizar_registro( $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);
        }

        $registro_encabezado_doc->fill( $request->all() );
        $registro_encabezado_doc->save();

        return redirect( 'contabilidad/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }

    // VISTA PARA MOSTRAR UN DOCUMENTO DE TRANSACCION
    public function show($id)
    {
        $this->set_variables_globales();

        $reg_anterior = ContabDocEncabezado::where('id', '<', $id)->where('core_empresa_id', Auth::user()->empresa_id)->max('id');
        $reg_siguiente = ContabDocEncabezado::where('id', '>', $id)->where('core_empresa_id', Auth::user()->empresa_id)->min('id');

        $doc_encabezado = ContabDocEncabezado::get_registro_impresion( $id );
        $doc_registros = ContabDocRegistro::get_registros_impresion( $doc_encabezado->id );
        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        //$view_pdf = ContabilidadController::vista_preliminar($id,'show');
        $view_pdf = View::make('contabilidad.incluir.tabla_registros_documento', compact( 'doc_encabezado', 'doc_registros') )->render();

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        return view( 'contabilidad.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id', 'empresa','doc_encabezado') ); 
    }


    // VISTA PARA MOSTRAR UN DOCUMENTO DE TRANSACCION
    public function imprimir($id)
    {
        $view_pdf = ContabilidadController::vista_preliminar($id,'imprimir');
       
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja='Letter';

        $pdf = \App::make('dompdf.wrapper');
        //$pdf->set_option('isRemoteEnabled', TRUE);
        $pdf->loadHTML( $view_pdf )->setPaper($tam_hoja,$orientacion);

        //echo $view_pdf;
        return $pdf->stream('documento.pdf');
    }


    // Generar vista para SOHW  o IMPRIMIR
    public static function vista_preliminar($id,$vista)
    {

        $doc_encabezado = ContabDocEncabezado::get_registro_impresion( $id );

        $doc_registros = ContabDocRegistro::get_registros_impresion( $doc_encabezado->id );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = '';

        $documento_vista = View::make( 'contabilidad.formatos_impresion.estandar', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad' ) )->render();

        return $documento_vista;         
    }

    // Generar vista para SOHW  o IMPRIMIR
    public function contab_anular_documento( $id )
    {
        $this->set_variables_globales();

        $doc_encabezado = ContabDocEncabezado::find( $id );
        $modificado_por = Auth::user()->email;

        $array_wheres = ['core_empresa_id'=>$doc_encabezado->core_empresa_id, 
                            'core_tipo_transaccion_id' => $doc_encabezado->core_tipo_transaccion_id,
                            'core_tipo_doc_app_id' => $doc_encabezado->core_tipo_doc_app_id,
                            'consecutivo' => $doc_encabezado->consecutivo];

        // Se elimina el movimiento
        ContabMovimiento::where( $array_wheres )->delete();

        // Se marcan como anulados los registros del documento
        ContabDocRegistro::where( 'contab_doc_encabezado_id', $doc_encabezado->id )->update( [ 'estado' => 'Anulado' ] );

        // Se marca como anulado el documento
        $doc_encabezado->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por ] );
        
        return redirect( 'contabilidad/'.$id.$this->variables_url );       
    }


    //
    // AJAX: enviar fila para el ingreso de registros al elaborar documento contable
    public static function contab_get_fila( $id_fila )
    {
        $cuentas = ContabCuenta::opciones_campo_select();

        $terceros = Tercero::opciones_campo_select();

        $btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-trash'></i></button>";
        $btn_confirmar = "<button type='button' class='btn btn-success btn-xs btn_confirmar'><i class='fa fa-check'></i></button>";

        $tr = '<tr id="linea_ingreso_datos">
                    <td style="display: none;"> <input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento" value="' . date('Y-m-d') . '"> </td>
                    <td style="display: none;"> <input type="hidden" name="documento_soporte_tercero" id="documento_soporte_tercero" value=""> </td>
                    <td> <input type="text" name="tipo_transaccion_linea" id="tipo_transaccion_linea" value="causacion" style="background: transparent;" readonly="readonly"> </td>
                    <td>
                        '.Form::select( 'campo_cuentas', $cuentas, null, [ 'id' => 'combobox_cuentas', 'class' => 'lista_desplegable' ] ).'
                    </td>
                    <td>
                        '.Form::select( 'campo_terceros', $terceros, null, [ 'id' => 'combobox_terceros', 'class' => 'lista_desplegable' ] ).'
                    </td>
                    <td> '.Form::text( 'detalle', null, [ 'id' => 'col_detalle', 'class' => 'caja_texto' ] ).' </td>
                    <td> '.Form::text( 'debito', null, [ 'id' => 'col_debito', 'class' => 'caja_texto' ] ).' </td>
                    <td> '.Form::text( 'credito', null, [ 'id' => 'col_credito', 'class' => 'caja_texto' ] ).' </td>
                    <td>'.$btn_confirmar.$btn_borrar.'</td>
                </tr>';

        return $tr;
    }

    public function get_saldo_grupo_cuentas_entre_fechas($fecha_ini,$fecha_fin,$grupo_cuenta_id)
    {
        $saldo_inicial = ContabMovimiento::where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.contab_cuenta_grupo_id','=',$grupo_cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'];

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where('contab_movimientos.fecha','>=',$fecha_ini)
                ->where('contab_movimientos.fecha','<=',$fecha_fin)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.contab_cuenta_grupo_id','=',$grupo_cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'] + $saldo_inicial;
    }

    public function get_saldo_cuentas_entre_fechas($fecha_ini,$fecha_fin,$cuenta_id)
    {
        $saldo_inicial = ContabMovimiento::where('contab_movimientos.fecha','<', $fecha_ini)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_movimientos.contab_cuenta_id','=', $cuenta_id )
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ) )
                ->get()
                ->toArray()[0]['valor_saldo'];

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->where('contab_movimientos.fecha','>=',$fecha_ini)
                ->where('contab_movimientos.fecha','<=',$fecha_fin)
                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                ->where('contab_cuentas.id','=',$cuenta_id)
                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ))
                ->get()
                ->toArray()[0]['valor_saldo'] + $saldo_inicial;
    }

    public function contab_get_grupos_cuentas($clase_id)
    {
        $registros_c = DB::table('contab_cuenta_grupos')
                ->where( [ 
                    [ 'contab_cuenta_clase_id', $clase_id ],
                    [ 'core_empresa_id','=', Auth::user()->empresa_id ]
                    ] )
                ->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros_c as $campo) {
            $grupo = DB::table('contab_cuenta_grupos')
                ->where( 'id', $campo->grupo_padre_id )
                ->value('descripcion');
            $opciones .= '<option value="'.$campo->id.'">'.$grupo.' > '.$campo->descripcion.'</option>';
        }

        return $opciones;
    }
    
    // Parámetro enviados por GET
    public function consultar_cuentas()
    {
        $campo_busqueda = Input::get('campo_busqueda');
        
        switch ( $campo_busqueda ) 
        {
            case 'descripcion':
                $operador = 'LIKE';
                $texto_busqueda = '%'.Input::get('texto_busqueda').'%';
                break;
            case 'codigo':
                $operador = 'LIKE';
                $texto_busqueda = Input::get('texto_busqueda').'%';
                break;
            
            default:
                # code...
                break;
        }

        $datos = ContabCuenta::where('contab_cuentas.estado','Activo')->where('contab_cuentas.core_empresa_id',Auth::user()->empresa_id)->where('contab_cuentas.core_app_id','0')->where('contab_cuentas.'.$campo_busqueda,$operador,$texto_busqueda)->select('contab_cuentas.id AS cuenta_id','contab_cuentas.descripcion','contab_cuentas.codigo')->get()->take(7);

        //dd($datos);

        $html = '<div class="list-group">';
        $es_el_primero = true;
        foreach ($datos as $linea) 
        {
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
            }

            $html .= '<a class="list-group-item list-group-item-autocompletar '.$clase.'" data-tipo_campo="cuenta" data-cuenta_id="'.$linea->cuenta_id.
                                '" data-id="'.$linea->cuenta_id.
                                '" > '.$linea->codigo.' '.$linea->descripcion.'</a>';
        }
        $html .= '</div>';

        return $html;
    }


    public function corregir_signo_a_movimientos()
    {
        $movimiento = ContabMovimiento::all();

        $i = 1;
        foreach ($movimiento as $registro)
        {
            $valor_debito = abs($registro->valor_debito);
            $valor_credito = abs($registro->valor_credito) * -1;
            $valor_saldo = $valor_debito + $valor_credito;

            $registro->valor_debito = $valor_debito;
            $registro->valor_credito = $valor_credito;
            $registro->valor_saldo = $valor_saldo;
            $registro->save();
            echo $i.'  ';
            $i++;
        }

        echo '<br>Se actualizaron '.($i-1).' registros.';
    }


    public static function contabilizar_registro2( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cta_bancaria_id = 0 )
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

    public function get_formulario_cxc()
    {
        $cuentas = ContabCuenta::opciones_campo_select();
        $terceros = Tercero::opciones_campo_select();

        $formulario = View::make( 'contabilidad.incluir.formulario_cxc', compact('cuentas','terceros') )->render();

        return $formulario;
    }

    public function get_formulario_cxp()
    {
        $cuentas = ContabCuenta::opciones_campo_select();
        $terceros = Tercero::opciones_campo_select();

        $formulario = View::make( 'contabilidad.incluir.formulario_cxp', compact('cuentas','terceros') )->render();

        return $formulario;
    }


}