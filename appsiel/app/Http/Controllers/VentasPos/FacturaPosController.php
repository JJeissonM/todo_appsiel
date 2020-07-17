<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;


use Spatie\Permission\Models\Permission;

use App\Http\Controllers\Sistema\CrudController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Inventarios\InventarioController;

use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Http\Controllers\Ventas\ReportesController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;


use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\InvMotivo;

use App\VentasPos\PreparaTransaccion;

use App\VentasPos\FacturaPos;
use App\VentasPos\DocRegistro;
use App\VentasPos\Movimiento;

use App\VentasPos\Pdv;

use App\Ventas\Cliente;
use App\Ventas\ResolucionFacturacion;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;

use App\Ventas\VtasMovimiento;

use App\CxC\DocumentosPendientes;
use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoMotivo;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\Impuesto;


class FacturaPosController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['22-salida'=>'Ventas POS'];

        $inv_motivo_id = 22;

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros( PreparaTransaccion::get_datos_tabla_ingreso_lineas_registros( $this->transaccion, $motivos ) );

        if ( is_null($tabla) )
        {
            $tabla = '';
        }
        
        $lista_campos = ModeloController::get_campos_modelo($this->modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, $cantidad_campos, 'create', null);

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo( $this->modelo, '' );



        $user = Auth::user();        

        $pdv = Pdv::find( Input::get('pdv_id') );

        //dd( $pdv->bodega );

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++)
        {
            switch ($lista_campos[$i]['name']) {

                case 'core_tipo_doc_app_id':
                    $lista_campos[$i]['opciones'] = [ $pdv->tipo_doc_app_default_id => $pdv->tipo_doc_app->prefijo . " - " . $pdv->tipo_doc_app->descripcion];
                    break;

                case 'cliente_input':
                    $lista_campos[$i]['value'] = $pdv->cliente->tercero->descripcion;
                    break;

                case 'vendedor_id':
                    array_shift( $lista_campos[$i]['opciones'] );
                    $lista_campos[$i]['value'] = [ $pdv->cliente->vendedor_id ];
                    //$lista_campos[$i]['opciones'] = [ $pdv->cliente->vendedor->id => $pdv->cliente->vendedor->tercero->descripcion];
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$i]['value'] = date('Y-m-d');
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$i]['opciones'] = [ $pdv->bodega_default_id => $pdv->bodega->descripcion ];
                    break;
                default:
                    # code...
                    break;
            }
        }
        
        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Crear: '.$this->transaccion->descripcion );
        
        $productos = InvProducto::get_datos_basicos( '', 'Activo' );
        $precios = ListaPrecioDetalle::get_precios_productos_de_la_lista( $pdv->cliente->lista_precios_id );
        $descuentos = ListaDctoDetalle::get_descuentos_productos_de_la_lista( $pdv->cliente->lista_descuentos_id );

        $contenido_modal = View::make('ventas_pos.lista_items',compact( 'productos') )->render();

        $plantilla_factura = $this->generar_plantilla_factura( $pdv );

        //echo $plantilla_factura;

        return view( 'ventas_pos.create', compact( 'form_create','miga_pan','tabla','pdv','productos','precios','descuentos', 'inv_motivo_id','contenido_modal', 'plantilla_factura') );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $lineas_registros = json_decode($request->lineas_registros);

        // Crear documento de Ventas
        $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);


        // Crear Registros del documento de ventas
        $request['creado_por'] = Auth::user()->email;
        FacturaPosController::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        return $doc_encabezado->consecutivo;
    }


    /*
        Crea los registros de un documento.
        No Devuelve nada.
    */
    public static function crear_registros_documento( Request $request, $doc_encabezado, array $lineas_registros )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);

        for ($i=0; $i < $cantidad_registros; $i++) 
        {
            $linea_datos = [ 'vtas_motivo_id' => (int)$request->inv_motivo_id ] +
                            [ 'inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id ] +
                            [ 'precio_unitario' => (float)$lineas_registros[$i]->precio_unitario ] +
                            [ 'cantidad' => (float)$lineas_registros[$i]->cantidad ] +
                            [ 'precio_total' => (float)$lineas_registros[$i]->precio_total ] +
                            [ 'base_impuesto' => (float)$lineas_registros[$i]->base_impuesto ] +
                            [ 'tasa_impuesto' => (float)$lineas_registros[$i]->tasa_impuesto ] +
                            [ 'valor_impuesto' => (float)$lineas_registros[$i]->valor_impuesto ] +
                            [ 'base_impuesto_total' => (float)$lineas_registros[$i]->base_impuesto_total ] +
                            [ 'tasa_descuento' => (float)$lineas_registros[$i]->tasa_descuento ] +
                            [ 'valor_total_descuento' => (float)$lineas_registros[$i]->valor_total_descuento ] +
                            [ 'creado_por' => Auth::user()->email ] +
                            [ 'estado' => 'Pendiente' ] +
                            [ 'vtas_pos_doc_encabezado_id' => $doc_encabezado->id ];

            $registro_creado = DocRegistro::create( $linea_datos );

            $datos['consecutivo'] = $doc_encabezado->consecutivo;

            Movimiento::create( 
                                    $datos +
                                    $linea_datos
                                );

            $total_documento += (float)$lineas_registros[$i]->precio_total;

        } // Fin por cada registro

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->save();
        
        return 0;
    }

    /**
     *
     */
    public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );

        $docs_relacionados = FacturaPos::get_documentos_relacionados( $doc_encabezado );
        $empresa = $this->empresa;
        $id_transaccion = $this->transaccion->id;

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        // Datos de los abonos aplicados a la factura
        $abonos = CxcAbono::get_abonos_documento( $doc_encabezado );

        // Datos de Notas Crédito aplicadas a la factura
        $notas_credito = NotaCredito::get_notas_aplicadas_factura( $doc_encabezado->id );

        $documento_vista = '';

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        $url_crear = $this->modelo->url_crear.$this->variables_url;
        
        $vista = 'ventas.show';

        if( !is_null( Input::get('vista') ) )
        {
            $vista = Input::get('vista');
        }

        return view( $vista, compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_credito') );
    }


    /*
        Imprimir
    */
    public function imprimir( $id )
    {
        $documento_vista = $this->generar_documento_vista( $id, 'ventas.formatos_impresion.pos' );

        // Se prepara el PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $this->doc_encabezado->documento_transaccion_descripcion.' - '.$this->doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');        
    }


    /*
        Generar la vista para los métodos show(), imprimir() o enviar_por_email()
    */
    public function generar_documento_vista( $id, $ruta_vista )
    {
        $this->set_variables_globales();
        
        $this->doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $this->doc_encabezado->id );

        $doc_encabezado = $this->doc_encabezado;
        $empresa = $this->empresa;

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)->where('estado','Activo')->get()->last();

        $etiquetas = $this->get_etiquetas();

        return View::make( $ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas' ) )->render();
    }



    public function generar_plantilla_factura( $pdv )
    {
        $this->set_variables_globales();

        $resolucion = ResolucionFacturacion::where( 'tipo_doc_app_id', $pdv->tipo_doc_app_default_id )->where('estado','Activo')->get()->last();

        $empresa = $this->empresa;

        $etiquetas = $this->get_etiquetas();

        return View::make( 'ventas_pos.plantilla_factura', compact( 'empresa', 'resolucion', 'etiquetas', 'pdv' ) )->render();
    }

    /*
        Proceso de eliminar FACTURA POS 


        PRIMERO TRABAJAR EN EDITAR

    */
    public static function anular_factura_pos( $doc_encabezado_id )
    {        
        $factura = FacturaPos::find( $request->factura_id );

        $array_wheres = ['core_empresa_id'=>$factura->core_empresa_id, 
            'core_tipo_transaccion_id' => $factura->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $factura->core_tipo_doc_app_id,
            'consecutivo' => $factura->consecutivo];

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $cantidad = CxcAbono::where('doc_cxc_transacc_id',$factura->core_tipo_transaccion_id)
                            ->where('doc_cxc_tipo_doc_id',$factura->core_tipo_doc_app_id)
                            ->where('doc_cxc_consecutivo',$factura->consecutivo)
                            ->count();

        if($cantidad != 0)
        {
            return redirect( 'ventas/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error','Factura NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería).');
        }

        $modificado_por = Auth::user()->email;

        // 1ro. Anular documento asociado de inventarios
        // Obtener las remisiones relacionadas con la factura y anularlas o dejarlas en estado Pendiente
        $ids_documentos_relacionados = explode( ',', $factura->remision_doc_encabezado_id );
        $cant_registros = count($ids_documentos_relacionados);
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $remision = InvDocEncabezado::find( $ids_documentos_relacionados[$i] );
            if ( !is_null($remision) )
            {
                if ( $request->anular_remision ) // anular_remision es tipo boolean
                {
                    InventarioController::anular_documento_inventarios( $remision->id );
                }else{
                    $remision->update(['estado'=>'Pendiente', 'modificado_por' => $modificado_por]);
                }    
            }
        }

        // 2do. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el documento del movimimeto de cuentas por cobrar
        CxcMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el movimiento de compras
        Movimiento::where($array_wheres)->delete();
        // 5to. Se marcan como anulados los registros del documento
        DocRegistro::where( 'vtas_doc_encabezado_id', $factura->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $factura->update(['estado'=>'Anulado', 'remision_doc_encabezado_id' => '', 'modificado_por' => $modificado_por]);

        return redirect( 'ventas/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('flash_message','Factura de ventas ANULADA correctamente.');
        
    }


    public function acumular( $pdv_id )
    {
        $encabezados_documentos = FacturaPos::where('pdv_id',$pdv_id)->where('estado','Pendiente')->get();

        foreach ($encabezados_documentos as $factura)
        {
            $lineas_registros = DocRegistro::where('vtas_pos_doc_encabezado_id',$factura->id)->get();

            foreach ($lineas_registros as $linea)
            {
                $datos = $factura->toArray() + $linea->toArray();

                // Falta Movimientos y Documentos de Inventarios
                /*
                    
                */


                // Movimiento de Ventas
                $datos['estado'] = 'Activo';
                VtasMovimiento::create( $datos );

                $linea->estado ='Acumulado';
                $linea->save();

                // Actualiza Movimiento POS
                $movimiento_pos = Movimiento::where('pdv_id', $datos['pdv_id'])
                                                ->where('core_tipo_transaccion_id', $datos['core_tipo_transaccion_id'])
                                                ->where('core_tipo_doc_app_id', $datos['core_tipo_doc_app_id'])
                                                ->where('consecutivo', $datos['consecutivo'])
                                                ->get()
                                                ->first();

                $movimiento_pos->estado ='Acumulado';
                $movimiento_pos->save();
            }

            // Movimiento de Tesoreria ó CxC
            $datos['estado'] = 'Activo';
            FacturaPosController::crear_registro_pago( $factura->forma_pago, $datos, $factura->valor_total, $factura->descripcion );

            $factura->estado ='Acumulado';
            $factura->save();
        }

        return 1;
        
    }

    public static function crear_registro_pago( $forma_pago, $datos, $total_documento, $detalle_operacion )
    {
        /*
            WARNING. Esto debe ser un parámetro de la configuración. Si se quiere llevar la factura contado a la caja directamente o si se causa una cuenta por cobrar
        */
        
        // Cargar la cuenta por cobrar (CxC)
        if ( $forma_pago == 'credito')
        {
            $datos['modelo_referencia_tercero_index'] = 'App\Ventas\Cliente';
            $datos['referencia_tercero_id'] = $datos['cliente_id'];
            $datos['valor_documento'] = $total_documento;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $total_documento;
            $datos['estado'] = 'Pendiente';
            DocumentosPendientes::create( $datos );
        }
        
        // Agregar el movimiento a tesorería
        if ( $forma_pago == 'contado')
        {
            // WARNING: La caja la debe tomar de la caja por defecto asociada al usuario,
            // Si el usuario no tiene caja asignada, el sistema no debe permitirle hacer facturas de contado.
            
            $pdv = Pdv::find( $datos['pdv_id'] );

            $caja = TesoCaja::find( $pdv->caja_default_id );
            // El motivo lo debe traer de unparámetro de la configuración
            $datos['teso_motivo_id'] = TesoMotivo::where('movimiento','entrada')->get()->first()->id;
            $datos['teso_caja_id'] = $caja->id;
            $datos['teso_cuenta_bancaria_id'] = 0;
            $datos['valor_movimiento'] = $total_documento;
            TesoMovimiento::create( $datos );
        }
    }

    public function get_etiquetas()
    {
        $parametros = config('ventas');

        $encabezado = '';

        if ($parametros['encabezado_linea_1'] != '')
        {
            $encabezado .= $parametros['encabezado_linea_1'];
        }

        if ($parametros['encabezado_linea_2'] != '')
        {
            $encabezado .= '<br>'.$parametros['encabezado_linea_2'];
        }

        if ($parametros['encabezado_linea_3'] != '')
        {
            $encabezado .= '<br>'.$parametros['encabezado_linea_3'];
        }


        $pie_pagina = '';

        if ($parametros['pie_pagina_linea_1'] != '')
        {
            $pie_pagina .= $parametros['pie_pagina_linea_1'];
        }

        if ($parametros['pie_pagina_linea_2'] != '')
        {
            $pie_pagina .= '<br>'.$parametros['pie_pagina_linea_2'];
        }

        if ($parametros['pie_pagina_linea_3'] != '')
        {
            $pie_pagina .= '<br>'.$parametros['pie_pagina_linea_3'];
        }

        return [ 'encabezado' => $encabezado, 'pie_pagina' => $pie_pagina ];
    }

}