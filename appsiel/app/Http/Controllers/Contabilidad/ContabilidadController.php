<?php

namespace App\Http\Controllers\Contabilidad;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

use Illuminate\Http\Request;

use App\Sistema\Modelo;
use App\Core\TipoDocApp;
use App\Core\Tercero;
use App\Core\Empresa;

use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Contabilidad\ContabMovimiento;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class ContabilidadController extends TransaccionController
{
    protected $datos = [];
    protected $grupos_cuentas = [];

    protected $duplicado = false;

    /* El método index() está en TransaccionController */

    public function create()
    {
        $this->set_variables_globales();

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'contabilidad.create' );
    }

    public function store( Request $request )
    {
        $registro_encabezado_doc = $this->crear_encabezado_documento($request, $request->url_id_modelo);

        $tabla_registros_documento = json_decode($request->tabla_registros_documento);
        
        $this->almacenar_lineas_registros( $request, $tabla_registros_documento, $registro_encabezado_doc );

        return redirect( 'contabilidad/' . $registro_encabezado_doc->id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion );
    }

    public function almacenar_lineas_registros( $request, $tabla_registros_documento, $registro_encabezado_doc )
    {
        $cantidad = count($tabla_registros_documento) - 2;
        for ($i=0; $i < $cantidad; $i++)
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

            $registro_doc = ContabDocRegistro::create(
                            [ 'contab_doc_encabezado_id' => $registro_encabezado_doc->id ] + 
                            [ 'contab_cuenta_id' => (int)$contab_cuenta_id ] + 
                            [ 'core_tercero_id' => $core_tercero_id ] + 
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => (float)$valor_debito] + 
                            [ 'valor_credito' => (float)$valor_credito] + 
                            [ 'tipo_transaccion' => $tabla_registros_documento[$i]->tipo_transaccion ]
                        );


            // 1.1. Para cada registro del documento, también se va actualizando el movimiento de contabilidad
            
            // Para el movimiento contable se guarda en detalle_operacion el detalle del encabezado del documento
            if ($detalle_operacion == '') {
                $detalle_operacion = $request->descripcion;
            }

            $this->datos = array_merge( $request->all(), [
                                                            'core_tercero_id' => $core_tercero_id,
                                                            'consecutivo' => $registro_encabezado_doc->consecutivo,
                                                            'id_registro_doc_tipo_transaccion' => $registro_doc->id,
                                                            'fecha_vencimiento' => $tabla_registros_documento[$i]->fecha_vencimiento,
                                                            'documento_soporte' => $tabla_registros_documento[$i]->documento_soporte_tercero,
                                                            'tipo_transaccion' => $tabla_registros_documento[$i]->tipo_transaccion] );

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

        if( !$this->verificar_permitir_editar_duplicar( $doc_registros ) )
        {
           return redirect( 'contabilidad/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Los documentos que tienen transacciones de CxP o CxC no pueden ser modificados.');
        }

        $doc_registros = $this->modificar_lineas_doc_registros( $doc_registros, 'edit' );

        $tercero_encabezado_numero_identificacion = $doc_encabezado->numero_identificacion; 

        $lineas_documento = View::make( 'contabilidad.incluir.lineas_documento', compact('doc_registros', 'tercero_encabezado_numero_identificacion') )->render();

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

        $doc_encabezado->actualizar_valor_total();

        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $mensaje_duplicado = '';
        if ( $this->duplicado )
        {
            $mensaje_duplicado = '<div class="alert alert-success">
                                      <strong> ¡Documento duplicado correctamente! </strong>
                                    </div>

                                    <div class="alert alert-warning">
                                      <strong> ¡Nota! </strong> Debe guardar el documento para afectar el movimiento contable. Además, todos los registros de <b>cxc</b> y <b>cxp</b> se cambiaron por registros de <b>causacion</b>.
                                    </div>';
            $this->duplicado = false;
        }

        return view( 'contabilidad.edit', compact( 'form_create', 'miga_pan', 'registro', 'archivo_js', 'lineas_documento', 'linea_num', 'mensaje_duplicado') );
    }


    /*
        A cada linea de registro se le asignan tres campos adicionales : tipo_transaccion_linea, fecha_vencimiento y documento_soporte_tercero
    */
    public function modificar_lineas_doc_registros( $doc_registros, $accion )
    {
        foreach ($doc_registros as $linea)
        {
            $tipo_transaccion_linea = $linea->tipo_transaccion;

            if ( $tipo_transaccion_linea == '' )
            {
                $tipo_transaccion_linea = 'causacion';
            }

            $fecha_vencimiento = date('Y-m-d');
            $documento_soporte_tercero = '';

            $mov_contab_linea_registro_doc = ContabMovimiento::where( 'id_registro_doc_tipo_transaccion', $linea->id )->get()->first();
            
            if ( !is_null( $mov_contab_linea_registro_doc ) )
            {
                $tipo_transaccion_linea = $mov_contab_linea_registro_doc->tipo_transaccion;
                $fecha_vencimiento = $mov_contab_linea_registro_doc->fecha_vencimiento;
                $documento_soporte_tercero = $mov_contab_linea_registro_doc->documento_soporte;
            }
            
            $linea->tipo_transaccion_linea = $tipo_transaccion_linea;
            $linea->fecha_vencimiento = $fecha_vencimiento;
            $linea->documento_soporte_tercero = $documento_soporte_tercero;

        }

        return $doc_registros;
    }


    public function verificar_permitir_editar_duplicar( $doc_registros )
    {   
        $permitir_editar = true;

        foreach ($doc_registros as $linea)
        {
            $tipo_transaccion_linea = 'causacion';

            $mov_contab_linea_registro_doc = ContabMovimiento::where( 'id_registro_doc_tipo_transaccion', $linea->id )->get()->first();
            
            if ( !is_null( $mov_contab_linea_registro_doc ) )
            {
                $tipo_transaccion_linea = $mov_contab_linea_registro_doc->tipo_transaccion;
            }

            if ( $tipo_transaccion_linea != 'causacion' )
            {
                $permitir_editar = false;
            }

        }

        return $permitir_editar;
    }


    //     A L M A C E N A R  LA MODIFICACION DE UN REGISTRO
    public function update(Request $request, $id)
    {
        $modelo = Modelo::find( $request->url_id_modelo );

        $registro_encabezado_doc = app( $modelo->name_space )->find($id);

        // Borrar registros viejos del documento
        ContabDocRegistro::where( 'contab_doc_encabezado_id', $id )->delete();

        ContabMovimiento::where( 'core_tipo_transaccion_id', $registro_encabezado_doc->core_tipo_transaccion_id )
                        ->where( 'core_tipo_doc_app_id', $registro_encabezado_doc->core_tipo_doc_app_id )
                        ->where( 'consecutivo', $registro_encabezado_doc->consecutivo )
                        ->delete();


        $request['core_tipo_transaccion_id'] = $registro_encabezado_doc->core_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $registro_encabezado_doc->core_tipo_doc_app_id;
        $request['consecutivo'] = $registro_encabezado_doc->consecutivo;

        // Contabilizar nuevos registros
        $tabla_registros_documento = json_decode($request->tabla_registros_documento);
        
        $this->almacenar_lineas_registros( $request, $tabla_registros_documento, $registro_encabezado_doc );

        $registro_encabezado_doc->fill( $request->all() );
        $registro_encabezado_doc->save();

        $registro_encabezado_doc->actualizar_valor_total();

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

        $doc_registros = $this->modificar_lineas_doc_registros( $doc_registros, 'show' );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $view_pdf = View::make( 'contabilidad.incluir.tabla_registros_documento', compact( 'doc_encabezado', 'doc_registros') )->render();

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        $registros_contabilidad = $doc_encabezado->get_movimiento_contable();

        return view( 'contabilidad.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id', 'empresa','doc_encabezado', 'registros_contabilidad') ); 
    }


    // VISTA PARA MOSTRAR UN DOCUMENTO DE TRANSACCION
    public function imprimir($id)
    {
        $view_pdf = $this->vista_preliminar($id,'imprimir');
       
        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja='Letter';

        $pdf = App::make('dompdf.wrapper');
        //$pdf->set_option('isRemoteEnabled', TRUE);
        $pdf->loadHTML( $view_pdf )->setPaper($tam_hoja,$orientacion);

        //echo $view_pdf;
        return $pdf->stream('documento.pdf');
    }


    // Generar vista para SOHW  o IMPRIMIR
    public function vista_preliminar($id,$vista)
    {

        $this->set_variables_globales();
        
        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );

        //$doc_encabezado = ContabDocEncabezado::get_registro_impresion( $id );

        $doc_registros = ContabDocRegistro::get_registros_impresion( $doc_encabezado->id );

        $doc_registros = $this->modificar_lineas_doc_registros( $doc_registros, 'imprimir' );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = '';

        $documento_vista = View::make( 'contabilidad.formatos_impresion.estandar', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad' ) )->render();

        return $documento_vista;         
    }



    public function duplicar_documento( $doc_encabezado_id )
    {
        $doc_encabezado = ContabDocEncabezado::find( $doc_encabezado_id );

        $registros_doc_encabezado = ContabDocRegistro::where( 'contab_doc_encabezado_id', $doc_encabezado->id )->get();

        /*
            Al duplicar el documento no se realiza contabilización, por tanto se puede duplicar aunque tenga movimientos de cxc o cxp. Pero los registros de con estos movimientos, se llamaran como registros de causacion en el formulario de editar.
        */

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
            $nueva_linea['contab_doc_encabezado_id'] = $nuevo_doc_encabezado->id;

            $nueva_linea_registro = ContabDocRegistro::create( $nueva_linea );
        }

        $this->duplicado = true;

        return $this->edit( $nuevo_doc_encabezado->id );
    }


    public function contab_anular_documento( $id )
    {
        $this->set_variables_globales();

        $doc_encabezado = ContabDocEncabezado::find( $id );
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );
        $modificado_por = Auth::user()->email;

        $array_estado = $this->verificar_estados_lineas_registros( $doc_encabezado, $doc_registros );

        if ( !$array_estado['permitir_eliminar'] )
        {
            return redirect( 'contabilidad/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','El documento no se puede eliminar; tiene transacciones de CxP o CxC con abonos o pagos aplicados. Retire los abonos o pagos para poder eliminar el documento.');
        }

        // Elimimar movimientos de CxC y CxP, si los hubiere
        foreach ( $array_estado['ids_registros_cxc_eliminar'] as $key => $value)
        {
            $registro_cartera = CxcMovimiento::find( $value );
            if ( !is_null( $registro_cartera ) )
            {
                $registro_cartera->delete();
            }                
        }

        foreach ( $array_estado['ids_registros_cxp_eliminar'] as $key => $value)
        {
            $registro_cartera = CxpMovimiento::find( $value );
            if ( !is_null( $registro_cartera ) )
            {
                $registro_cartera->delete();
            }  
        }

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



    public function verificar_estados_lineas_registros( $doc_encabezado, $doc_registros )
    {
        $permitir_eliminar = true;
        $ids_registros_cxc_eliminar = [];
        $ids_registros_cxp_eliminar = [];

        foreach ($doc_registros as $linea)
        {
            $tipo_transaccion_linea = 'causacion';

            $mov_contab_linea_registro_doc = ContabMovimiento::where( 'id_registro_doc_tipo_transaccion', $linea->id )->get()->first();
            
            if ( !is_null( $mov_contab_linea_registro_doc ) )
            {
                $tipo_transaccion_linea = $mov_contab_linea_registro_doc->tipo_transaccion;
            }

            if ( $tipo_transaccion_linea != 'causacion' )
            {
                switch ( $tipo_transaccion_linea ) {
                    case 'crear_cxc':
                        // Verificar si la linea tiene abonos, si tiene no se puede eliminar
                        $abono_cxc = CxcAbono::where('doc_cxc_transacc_id',$doc_encabezado->core_tipo_transaccion_id)
                                            ->where('doc_cxc_tipo_doc_id',$doc_encabezado->core_tipo_doc_app_id)
                                            ->where('doc_cxc_consecutivo',$doc_encabezado->consecutivo)
                                            ->get()
                                            ->first();

                        if( is_null( $abono_cxc ) )
                        {
                            $linea_movimiento = CxcMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                                                            ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                                                            ->where('consecutivo',$doc_encabezado->consecutivo)
                                                            ->get()
                                                            ->first();
                            if ( !is_null( $linea_movimiento ) )
                            {
                                $ids_registros_cxc_eliminar[] = $linea_movimiento->id;
                            }
                        }else{
                            $permitir_eliminar = false;
                        }

                        break;
                    case 'crear_cxp':
                        // Verificar si la linea tiene abonos, si tiene no se puede eliminar
                        $abono_cxp = CxpAbono::where('doc_cxp_transacc_id',$doc_encabezado->core_tipo_transaccion_id)
                                            ->where('doc_cxp_tipo_doc_id',$doc_encabezado->core_tipo_doc_app_id)
                                            ->where('doc_cxp_consecutivo',$doc_encabezado->consecutivo)
                                            ->get()
                                            ->first();

                        if( is_null( $abono_cxp ) )
                        {
                            $linea_movimiento = CxpMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                                                            ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                                                            ->where('consecutivo',$doc_encabezado->consecutivo)
                                                            ->get()
                                                            ->first();
                            if ( !is_null( $linea_movimiento ) )
                            {
                                $ids_registros_cxp_eliminar[] = $linea_movimiento->id;
                            }
                            
                        }else{
                            $permitir_eliminar = false;
                        }

                        break;
                    
                    default:
                        # code...
                        break;
                }
            }

        }

        return [ 'permitir_eliminar' => $permitir_eliminar, 'ids_registros_cxc_eliminar' => $ids_registros_cxc_eliminar, 'ids_registros_cxp_eliminar' => $ids_registros_cxp_eliminar];
                        
    }


    //
    // AJAX: enviar fila para el ingreso de registros al elaborar documento contable
    public static function contab_get_fila( $id_fila )
    {
        $btn_confirmar = "<button class='btn btn-success btn-xs btn_confirmar' style='display: inline;'><i class='fa fa-check'></i></button>";
        $btn_borrar = "<button class='btn btn-danger btn-xs btn_eliminar' style='display: inline;'><i class='fa fa-trash'></i></button>";

        $tr = '<tr id="linea_ingreso_datos">
                    <td style="display: none;"> <input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento" value="' . date('Y-m-d') . '"> </td>
                    <td style="display: none;"> <input type="hidden" name="documento_soporte_tercero" id="documento_soporte_tercero" value=""> </td>
                    <td> <input type="text" name="tipo_transaccion_linea" id="tipo_transaccion_linea" value="causacion" style="background: transparent; border:0; width:70px;" readonly="readonly"> </td>
                    <td>
                        '.FormFacade::text( 'cuenta_input', null, [ 'class' => 'form-control text_input_sugerencias', 'id' => 'cuenta_input', 'data-url_busqueda' => url('contab_consultar_cuentas'), 'autocomplete'  => 'off' ] ).'
                        '.FormFacade::hidden( 'campo_cuentas', null, [ 'id' => 'combobox_cuentas' ] ).'
                    </td>
                    <td>
                        '.FormFacade::text( 'tercero_input', null, [ 'class' => 'form-control text_input_sugerencias', 'id' => 'tercero_input', 'data-url_busqueda' => url('core_consultar_terceros_v2'), 'autocomplete'  => 'off' ] ).'
                        '.FormFacade::hidden( 'campo_terceros', null, [ 'id' => 'combobox_terceros' ] ).'
                    </td>
                    <td> '.FormFacade::text( 'detalle', null, [ 'id' => 'col_detalle', 'class' => 'form-control' ] ).' </td>
                    <td> '.FormFacade::text( 'debito', null, [ 'id' => 'col_debito', 'class' => 'form-control' ] ).' </td>
                    <td> '.FormFacade::text( 'credito', null, [ 'id' => 'col_credito', 'class' => 'form-control' ] ).' </td>
                    <td> <div class="btn-group">'.$btn_confirmar.$btn_borrar.'</div> </td>
                </tr>';

        return $tr;
    }

    public function get_saldo_grupo_cuentas_entre_fechas($fecha_ini, $fecha_fin, $grupo_cuenta_id)
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
                    [ 'core_empresa_id','=', Auth::user()->empresa_id ],
                    [ 'grupo_padre_id','<>', 0 ]
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
        $texto_busqueda_codigo = (int)Input::get('texto_busqueda');

        if( $texto_busqueda_codigo == 0 )
        {
            $campo_busqueda = 'descripcion';
            $texto_busqueda = '%' . str_replace( " ", "%", Input::get('texto_busqueda') ) . '%';
        }else{
            $campo_busqueda = 'codigo';
            $texto_busqueda = Input::get('texto_busqueda').'%';
        }

        $texto_busqueda_descripcion = '%'.Input::get('texto_busqueda').'%';

        $datos = ContabCuenta::where('contab_cuentas.estado','Activo')
                                ->where('contab_cuentas.core_empresa_id', Auth::user()->empresa_id)
                                ->where('contab_cuentas.core_app_id','0')
                                ->where('contab_cuentas.'.$campo_busqueda, 'LIKE', $texto_busqueda)
                                ->select(
                                            'contab_cuentas.id',
                                            'contab_cuentas.descripcion',
                                            'contab_cuentas.codigo')
                                ->get()
                                ->take(7);

        $html = '<div class="list-group">';
        $es_el_primero = true;
        $ultimo_item = 0;
        $num_item = 1;
        $cantidad_datos = count( $datos->toArray() ); // si datos es null?
        foreach ($datos as $linea) 
        {
            $primer_item = 0;
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
                $primer_item = 1;
            }


            if ( $num_item == $cantidad_datos )
            {
                $ultimo_item = 1;
            }

            $html .= '<a class="list-group-item list-group-item-sugerencia '.$clase.'" data-registro_id="'.$linea->id.
                                '" data-primer_item="'.$primer_item.
                                '" data-accion="na" '.
                                '" data-ultimo_item="'.$ultimo_item; // Esto debe ser igual en todas las busquedas

            $html .=            '" data-tipo_campo="cuenta" ';

            $html .=            '" > '.$linea->codigo.' '.$linea->descripcion.' </a>';

            $num_item++;
        }

        // Linea crear nuevo registro
        $modelo_id = 49; // Cuentas contables
        $html .= '<a class="list-group-item list-group-item-sugerencia list-group-item-warning" data-modelo_id="'.$modelo_id.'" data-accion="crear_nuevo_registro" > + Crear nueva </a>';

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