<?php

namespace App\Http\Controllers\Compras;

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
use App\Http\Controllers\Inventarios\InventarioController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;

// Modelos

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvProducto;
use App\Inventarios\InvMotivo;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasMovimiento;
use App\Compras\Proveedor;

use App\Contabilidad\ContabMovimiento;

use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\Contabilidad\Impuesto;

class NotaCreditoController extends TransaccionController
{
    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de compras
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        $id_transaccion = $this->transaccion->id;

        if ( is_null( Input::get('factura_id') ) )
        {
            return redirect('web?id=9&id_modelo=166')->with('mensaje_error','No puede hacer notas crédito desde esta opción. Debe ir al Botón Crear Nota crédito directa');
        }else{

            $factura = ComprasDocEncabezado::get_registro_impresion( Input::get('factura_id') );

            $movimiento_cxc = CxpMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                                ->where('consecutivo', $factura->consecutivo)
                                ->get()
                                ->first();

            if ( is_null( $movimiento_cxc ) )
            {
                return redirect('compras/'.$factura->id.'?id=9&id_modelo=159&id_transaccion=25')->with('mensaje_error','La factura no tiene registros de cuentas por cobrar');
            }else{
                if ( $movimiento_cxc->saldo_pendiente == 0 )
                {
                    return redirect('compras/'.$factura->id.'?id=9&id_modelo=159&id_transaccion=25')->with('mensaje_error','La factura no tiene SALDO PENDIENTE por cobrar');
                }
            }
        }

        // Información de la Factura de compras
        $doc_encabezado = ComprasDocEncabezado::get_registro_impresion( Input::get('factura_id') );
        $doc_registros = ComprasDocRegistro::get_registros_impresion( Input::get('factura_id') );

        $entrada_almacen = InvDocEncabezado::get_registro_impresion( $doc_encabezado->entrada_almacen_id );

        //dd( $doc_registros->toArray() );

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['18-salida'=>'Devolución por compras'];

        $tabla = View::make('compras.notas_credito.tabla_registros_create', compact( 'doc_encabezado', 'doc_registros', 'motivos', 'entrada_almacen' ) )->render();
        
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$this->transaccion,$lista_campos,$cantidad_campos,'create',null);

        // Agregar al comienzo del documento
        array_unshift($lista_campos, [
                                            "id" => 201,
                                            "descripcion" => "Empresa",
                                            "tipo" => "personalizado",
                                            "name" => "encabezado",
                                            "opciones" => "",
                                            "value" => '<div style="border: solid 1px #ddd; padding-top: -20px;">
                                                            <b style="font-size: 1.6em; text-align: center; display: block;">
                                                                '.$doc_encabezado->documento_transaccion_descripcion.'
                                                                <br/>
                                                                <b>No.</b> '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'
                                                                <br/>
                                                                <b>Fecha:</b> '.$doc_encabezado->fecha.'
                                                            </b>
                                                            <br/>
                                                            <b>Cliente:</b> '.$doc_encabezado->tercero_nombre_completo.'
                                                            <br/>
                                                            <b>NIT: &nbsp;&nbsp;</b> '.number_format( $doc_encabezado->numero_identificacion, 0, ',', '.').'
                                                        </div>',
                                            "atributos" => [],
                                            "definicion" => "",
                                            "requerido" => 0,
                                            "editable" => 1,
                                            "unico" => 0
                                        ] );

        $form_create = [
                        'url' => $this->modelo->url_form_create,
                        'campos' => $lista_campos
                    ];
        
        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Crear: '.$this->transaccion->descripcion );

        return view('compras.notas_credito.create', compact('form_create','id_transaccion','miga_pan','tabla','doc_encabezado'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $factura = ComprasDocEncabezado::get_registro_impresion( $request->compras_doc_relacionado_id ); // WARNING: si la factura tiene varias entradas, no se puede hacer la nota

        $request['creado_por'] = Auth::user()->email;

        // 1ro. Crear documento de Salida de inventarios (Devolución) con base en la entrada y las cantidades a devolver
        // WARNING. HECHO MANUALMENTE
        $request['entrada_almacen_id'] = $this->crear_devolucion( $request , $factura->entrada_almacen_id );


        // 2do. Crear encabezado del documento de Compras (Nota Crédito)
        $request['compras_doc_relacionado_id'] = $factura->id; // Relacionar Nota con la Factura
        $nota_credito = CrudController::crear_nuevo_registro($request, $request->url_id_modelo); // Nuevo encabezado

        // 3ro. Crear líneas de registros del documento
        NotaCreditoController::crear_registros_nota_credito( $request, $nota_credito, $factura );

        return redirect('compras_notas_credito_directa/'.$nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

    /*
        Este método crea el documento de salida de inventarios de los productos vendidos (Remisión de compras)
        WARNING: Se asignan manualmente algunos campos de a tablas inv_doc_inventarios  
    */
    public function crear_devolucion(Request $request, $entrada_almacen_id)
    {
        // Llamar a los parámetros del archivo de configuración
        $parametros = config('compras');

        // Modelo del encabezado del documento (dvc = Devolución en Compras)
        $dvc_modelo_id = $parametros['dvc_modelo_id'];
        $dvc_tipo_transaccion_id = $parametros['dvc_tipo_transaccion_id'];
        $dvc_tipo_doc_app_id = $parametros['dvc_tipo_doc_app_id'];

        // Se crea un nuevo campo para lineas_registros
        $lineas_registros = []; // Para la devolución

        $array_devolucion = [];

        // Obtener registros de la entrada de almacén de la factura de compras
        // Se harán la devoluciones a cada línea de estos registros (si se le ingresó cantidad a devolver)
        $registros_ea = InvDocRegistro::where( 'inv_doc_encabezado_id', $entrada_almacen_id )->get();
        $l = 0; // Contaador para las lineas a devolver
        $regs = 0; // Contador para los registro de la entradas de almacén


        foreach ($registros_ea as $linea)
        {
            $cantidad_devolver = (float)$request->all()['cantidad_devolver'][$regs];
            
            if ( $cantidad_devolver > 0)
            {
                $linea_devolucion = $linea->toArray();
                $linea_devolucion['cantidad'] = $cantidad_devolver;
                $linea_devolucion['inv_motivo_id'] = (int)explode('-', $request->all()['motivos_ids'][$l])[0];
                $linea_devolucion['costo_total'] = $cantidad_devolver * $linea['costo_unitario'];
                $inv_bodega_id = $linea['inv_bodega_id'];
                $lineas_registros[$l] = (object)( $linea_devolucion );
                $l++;
            }
            $regs++;  
        }

        // Se crea el documento, se cambia temporalmente el tipo de transacción y el tipo_doc_app

        $tipo_transaccion_id_original = $request['core_tipo_transaccion_id'];
        $core_tipo_doc_app_id_original = $request['core_tipo_doc_app_id'];

        $request['core_tipo_transaccion_id'] = $dvc_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $dvc_tipo_doc_app_id;
        $request['estado'] = 'Facturada';
        $request['inv_bodega_id'] = $inv_bodega_id;

        $documento_inventario_id = InventarioController::crear_documento($request, $lineas_registros, $dvc_modelo_id);

        // Se revierten los datos cambiados
        $request['core_tipo_transaccion_id'] = $tipo_transaccion_id_original;
        $request['core_tipo_doc_app_id'] = $core_tipo_doc_app_id_original;
        $request['estado'] = 'Activo';

        return $documento_inventario_id;
    }
    
    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public static function crear_registros_nota_credito( Request $request, $nota_credito, $factura )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        // Se crean los registro con base en el documento de inventario ya creado
        // lineas_registros solo tiene el ID del documentos de inventario
        // entrada_almacen_id es el ID de una devolución en compras
        $lineas_registros = [(object)[ 'id_doc' => $nota_credito->entrada_almacen_id ]];

        //dd( $lineas_registros );

        NotaCreditoController::crear_lineas_registros_compras( $datos, $nota_credito, $lineas_registros, $factura );

        return true;
    }


    // Se crean los registros con base en los registros de la devolución
    public static function crear_lineas_registros_compras( $datos, $nota_credito, $lineas_registros, $factura )
    {
        $total_documento = 0;
        // Por cada entrada de almacén pendiente
        $cantidad_registros = count( $lineas_registros );
        $entrada_almacen_id = '';
        $primera = true;
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_devolucion_id = (int)$lineas_registros[$i]->id_doc;

            $registros_devolucion = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_devolucion_id )->get();

            foreach ($registros_devolucion as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad;
                $total_base_impuesto = abs($un_registro->costo_total);

                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, $factura->proveedor_id, 0 );

                $precio_unitario = $un_registro->costo_unitario * ( 1 + $tasa_impuesto  / 100 );

                $precio_total = $precio_unitario * $cantidad;

                $linea_datos = [ 'inv_bodega_id' => $un_registro->inv_bodega_id ] +
                                [ 'inv_motivo_id' => $un_registro->inv_motivo_id ] +
                                [ 'inv_producto_id' => $un_registro->inv_producto_id ] +
                                [ 'precio_unitario' => $precio_unitario ] +
                                [ 'cantidad' => $cantidad ] +
                                [ 'precio_total' => $precio_total ] +
                                [ 'base_impuesto' =>  $total_base_impuesto ] +
                                [ 'tasa_impuesto' => $tasa_impuesto ] +
                                [ 'valor_impuesto' => ( abs($precio_total) - $total_base_impuesto ) ] +
                                [ 'creado_por' => Auth::user()->email ] +
                                [ 'estado' => 'Activo' ];

                ComprasDocRegistro::create( 
                                        $datos + 
                                        [ 'compras_doc_encabezado_id' => $nota_credito->id ] +
                                        $linea_datos
                                    );

                $datos['consecutivo'] = $nota_credito->consecutivo;
                ComprasMovimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );

                // Contabilizar
                $detalle_operacion = $datos['descripcion'];

                NotaCreditoController::contabilizar_movimiento_credito( $datos + $linea_datos, $detalle_operacion );

                $total_documento += $precio_total;

                // Actualizar campo de cantidad_devuelta en cada línea de registro de la factura de compras
                ComprasDocRegistro::where('compras_doc_encabezado_id', $factura->id)
                                    ->where('inv_producto_id', $un_registro->inv_producto_id)
                                    ->update( [ 'cantidad_devuelta' => abs($un_registro->cantidad) ] );


            } // Fin por cada registro de la entrada

            // Marcar la entrada como facturada
            InvDocEncabezado::find( $doc_devolucion_id )->update( [ 'estado' => 'Facturada' ] );

            // Se va creando un listado de entradas separadas por coma 
            if ($primera)
            {
                $entrada_almacen_id = $doc_devolucion_id;
                $primera = false;
            }else{
                $entrada_almacen_id .= ','.$doc_devolucion_id;
            }

        }

        $nota_credito->valor_total = $total_documento;
        $nota_credito->entrada_almacen_id = $entrada_almacen_id;
        $nota_credito->save();
        
        // Un solo registro de la cuenta por pagar (CR)
        $forma_pago = 'credito'; // esto se debe determinar de acuerdo a algún parámetro en la configuración, $datos['forma_pago']

        NotaCreditoController::contabilizar_movimiento_debito( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion, $factura );

        // Actualizar registro del pago de la factura a la que afecta la nota
        NotaCreditoController::actualizar_registro_pago( $total_documento, $factura, $nota_credito, 'crear' ); 

        return true;
    }

    public static function contabilizar_movimiento_credito( $datos, $detalle_operacion )
    {
        // IVA descontable (CR)
        // Si se ha liquidado impuestos en la transacción
        if ( isset( $datos['tasa_impuesto'] ) && $datos['tasa_impuesto'] > 0 )
        {
            $cta_impuesto_compras_id = InvProducto::get_cuenta_impuesto_devolucion_compras( $datos['inv_producto_id'] );
            ContabilidadController::contabilizar_registro2( $datos, $cta_impuesto_compras_id, $detalle_operacion, 0, abs( $datos['valor_impuesto'] ));
        }

        // Reversar cuenta por legalizar (completar CR), en la transacción inventarios, se liquidó el costo_total
        $cta_contrapartida_id = InvMotivo::find( $datos['inv_motivo_id'] )->cta_contrapartida_id;
        ContabilidadController::contabilizar_registro2( $datos, $cta_contrapartida_id, $detalle_operacion, 0, abs( $datos['base_impuesto'] ));
    }

    public static function contabilizar_movimiento_debito( $forma_pago, $datos, $total_documento, $detalle_operacion, $factura = null )
    {
        /*
            Se crea un SOLO registro contable de la cuenta por pagar (Crédito) o la tesorería (Contado)
        */

        
        // Contabilizar Cta. Por Pagar (DB)

        // Se resetean estos campos del registro
        $datos['inv_producto_id'] = 0;
        $datos['cantidad '] = 0;
        $datos['tasa_impuesto'] = 0;
        $datos['base_impuesto'] = 0;
        $datos['valor_impuesto'] = 0;
        $datos['inv_bodega_id'] = 0;

        if ( is_null($factura) )
        {
            $cxp_id = Proveedor::get_cuenta_por_pagar( $datos['proveedor_id'] );
        }else{
            $cxp_id = Proveedor::get_cuenta_por_pagar( $factura->proveedor_id );
        }
        
        ContabilidadController::contabilizar_registro2( $datos, $cxp_id, $detalle_operacion, abs($total_documento), 0 );
    }

    public static function actualizar_registro_pago( $total_nota, $factura, $nota, $accion )
    {
        /*
            Al crear la nota: Se disminuye el saldo pendiente y se aumenta el valor pagado. También se crea un registro de abono de cxp
            A anular la nota: Se aumenta el saldo pendiente y se disminuye el valor pagado
        */

        // total_nota es negativo cuando se hace la nota y positivo cuando se anula


        $movimiento_cxp = CxpMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                                ->where('consecutivo', $factura->consecutivo)
                                ->get()
                                ->first();

        $nuevo_total_pendiente = $movimiento_cxp->saldo_pendiente + $total_nota; 
        $nuevo_total_pagado = $movimiento_cxp->valor_pagado - $total_nota;

        $estado = 'Pendiente';
        if ( $nuevo_total_pendiente == 0)
        {
            $estado = 'Pagado';
        }

        $movimiento_cxp->update( [ 
                                    'valor_pagado' => $nuevo_total_pagado,
                                    'saldo_pendiente' => $nuevo_total_pendiente,
                                    'estado' => $estado
                                ] );

        $datos = ['core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id]+
                  ['core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id]+
                  ['consecutivo' => $nota->consecutivo]+
                  ['fecha' => $nota->fecha]+
                  ['core_empresa_id' => $nota->core_empresa_id]+
                  ['core_tercero_id' => $nota->core_tercero_id]+
                  ['modelo_referencia_tercero_index' => 'App\Compras\Proveedor']+
                  ['referencia_tercero_id' => $factura->proveedor_id]+
                  ['doc_cxp_transacc_id' => $factura->core_tipo_transaccion_id]+
                  ['doc_cxp_tipo_doc_id' => $factura->core_tipo_doc_app_id]+
                  ['doc_cxp_consecutivo' => $factura->consecutivo]+
                  ['doc_cruce_transacc_id' => 0]+
                  ['doc_cruce_tipo_doc_id' => 0]+
                  ['doc_cruce_consecutivo' => 0]+
                  ['abono' => abs($total_nota)]+
                  ['creado_por' => $nota->creado_por];

        if ( $accion == 'crear')
        {
            // Almacenar registro de abono
            CxpAbono::create( $datos );
        }else{
            // Eliminar registro de abono
            CxpAbono::where( $datos )->delete();
        }
    }

    public function show($id)
    {
        $this->set_variables_globales();

        return redirect('compras/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=compras.notas_credito.show');
    }

    // ANULAR
    public function anular( $id )
    {
        $this->set_variables_globales();

        $nota = ComprasDocEncabezado::find( $id );

        $array_wheres = ['core_empresa_id'=>$nota->core_empresa_id, 
            'core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id,
            'consecutivo' => $nota->consecutivo];

        // 1ro. Actualizar campo de cantidad_devuelta en cada línea de registro de la factura de compras
        $factura = ComprasDocEncabezado::find( $nota->compras_doc_relacionado_id );
        $registros_factura = ComprasDocRegistro::where('compras_doc_encabezado_id', $factura->id)->get();
        foreach ($registros_factura as $linea)
        {
            $cantidad_nota = ComprasDocRegistro::where('compras_doc_encabezado_id',$nota->id)
                                                ->where('inv_producto_id',$linea->inv_producto_id)
                                                ->value('cantidad');

            $nueva_cantidad_devuelta = $linea->cantidad_devuelta + $cantidad_nota;
            $linea->update( [ 'cantidad_devuelta' => $nueva_cantidad_devuelta ] );
        }
                            

        // 2do. Anular documento asociado de inventarios
        InventarioController::anular_documento_inventarios( $nota->entrada_almacen_id );

        // 3ro. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 4to. Se actualiza el registro de la factura a la que afecto la nota en el movimimeto de cuentas por pagar
        // Se envía el valor en positivo para que sume al saldo pendiente y reste al valor abonado
        NotaCreditoController::actualizar_registro_pago( $nota->valor_total * -1, $factura, $nota, 'anular' );

        // 5to. Se elimina el movimiento de compras
        ComprasMovimiento::where($array_wheres)->delete();

        $modificado_por = Auth::user()->email;
        // 6to. Se marcan como anulados los registros del documento
        ComprasDocRegistro::where( 'compras_doc_encabezado_id', $nota->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $nota->update( [ 'estado' => 'Anulado', 'compras_doc_relacionado_id' => '0', 'entrada_almacen_id' => '0', 'modificado_por' => $modificado_por] );

        return redirect( 'compras/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=compras.notas_credito.show')->with('flash_message','Nota anulada correctamente.');
    }

}