<?php

namespace App\Http\Controllers\Ventas;

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

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Ventas\NotaCreditoController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;

use App\Core\EncabezadoDocumentoTransaccion;

// Modelos
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvCostoPromProducto;

use App\Ventas\VtasTransaccion;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\Cliente;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\Impuesto;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;


class NotaCreditoDirectaController extends TransaccionController
{
    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de ventas
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['15-entrada'=>'Devolución por ventas'];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros( VtasTransaccion::get_datos_tabla_ingreso_lineas_registros( $this->transaccion, $motivos ) );

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'ventas.create', $tabla );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 1ro. Crear documento de Salida de inventarios (Devolución) con base en la entrada y las cantidades a devolver
        // WARNING. HECHO MANUALMENTE
        $request['remision_doc_encabezado_id'] = $this->crear_devolucion( $request );

        // 2do. Crear encabezado del documento de Ventas (Nota Crédito)
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $nota_credito = $encabezado_documento->crear_nuevo( $request->all() );

        // 3ro. Crear líneas de registros del documento
        NotaCreditoDirectaController::crear_registros_nota_credito( $request, $nota_credito );

        return redirect('ventas_notas_credito_directa/'.$nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

    /*
        Este método crea el documento de salida de inventarios de los productos vendidos (Remisión de ventas)
        WARNING: Se asignan manualmente algunos campos de a tablas inv_doc_inventarios  
    */
    public function crear_devolucion(Request $request)
    {
        // Llamar a los parámetros del archivo de configuración
        $parametros = config('ventas');

        // Modelo del encabezado del documento (dvc = Devolución en Ventas)
        $dvc_modelo_id = $parametros['dvc_modelo_id'];
        $dvc_tipo_transaccion_id = $parametros['dvc_tipo_transaccion_id'];
        $dvc_tipo_doc_app_id = $parametros['dvc_tipo_doc_app_id'];

        $lineas_registros = json_decode($request->lineas_registros);

        // Se crea el documento, se cambia temporalmente el tipo de transacción y el tipo_doc_app

        $tipo_transaccion_id_original = $request['core_tipo_transaccion_id'];
        $core_tipo_doc_app_id_original = $request['core_tipo_doc_app_id'];

        $request['core_tipo_transaccion_id'] = $dvc_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $dvc_tipo_doc_app_id;
        $request['estado'] = 'Facturada';
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
    public static function crear_registros_nota_credito( Request $request, $nota_credito )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        // Se crean los registro con base en el documento de inventario ya creado
        // lineas_registros solo tiene el ID del documentos de inventario
        // remision_doc_encabezado_id es el ID de una devolución en ventas
        $lineas_registros = [(object)[ 'id_doc' => $nota_credito->remision_doc_encabezado_id ]];

        NotaCreditoDirectaController::crear_lineas_registros_ventas( $datos, $nota_credito, $lineas_registros );

        return true;
    }


    // Se crean los registros con base en los registros de la devolución
    public static function crear_lineas_registros_ventas( $datos, $nota_credito, $lineas_registros )
    {
        $total_documento = 0;
        // Por cada entrada de almacén pendiente
        $cantidad_registros = count( $lineas_registros );
        $remision_doc_encabezado_id = '';
        $primera = true;
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_devolucion_id = (int)$lineas_registros[$i]->id_doc;

            $registros_devolucion = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_devolucion_id )->get();

            foreach ($registros_devolucion as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad * -1;

                // Los precios se traen de la lista de precios del cliente
                $precio_unitario = ListaPrecioDetalle::get_precio_producto( $datos['lista_precios_id'], $datos['fecha'], $un_registro->inv_producto_id );

                $precio_total = $precio_unitario * $cantidad;

                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, 0, $nota_credito->cliente_id );

                $base_impuesto = $precio_unitario / ( 1 + $tasa_impuesto / 100 );

                $base_impuesto_total = abs($base_impuesto * $cantidad);

                $linea_datos = [ 'inv_bodega_id' => $un_registro->inv_bodega_id ] +
                                [ 'inv_motivo_id' => $un_registro->inv_motivo_id ] +
                                [ 'inv_producto_id' => $un_registro->inv_producto_id ] +
                                [ 'precio_unitario' => $precio_unitario ] +
                                [ 'cantidad' => $cantidad ] +
                                [ 'precio_total' => $precio_total ] +
                                [ 'base_impuesto' =>  $base_impuesto ] +
                                [ 'tasa_impuesto' => $tasa_impuesto ] +
                                [ 'valor_impuesto' => ( $precio_unitario - $base_impuesto ) ] +
                                [ 'base_impuesto_total' => $base_impuesto_total ] +
                                [ 'creado_por' => Auth::user()->email ] +
                                [ 'estado' => 'Activo' ];

                VtasDocRegistro::create( 
                                        $datos + 
                                        [ 'vtas_doc_encabezado_id' => $nota_credito->id ] +
                                        $linea_datos
                                    );

                $datos['consecutivo'] = $nota_credito->consecutivo;
                VtasMovimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );

                // Contabilizar
                $detalle_operacion = $datos['descripcion'];

                // Reversar ingresos e impuestos (Este método se llama de otro Controller)
                NotaCreditoController::contabilizar_movimiento_debito( $datos + $linea_datos, $detalle_operacion );

                $total_documento += $precio_total;

            } // Fin por cada registro de la entrada

            // Marcar la entrada como facturada
            InvDocEncabezado::find( $doc_devolucion_id )->update( [ 'estado' => 'Facturada' ] );

            // Se va creando un listado de entradas separadas por coma 
            if ($primera)
            {
                $remision_doc_encabezado_id = $doc_devolucion_id;
                $primera = false;
            }else{
                $remision_doc_encabezado_id .= ','.$doc_devolucion_id;
            }

        }

        $nota_credito->valor_total = $total_documento;
        $nota_credito->remision_doc_encabezado_id = $remision_doc_encabezado_id;
        $nota_credito->save();
        
        // Un solo registro para reversar la cuenta por cobrar (CR)
        NotaCreditoController::contabilizar_movimiento_credito( $datos + $linea_datos, $total_documento, $detalle_operacion, null );

        // Cargar a los registros de cuentas por pagar
        $datos['modelo_referencia_tercero_index'] = 'App\Ventas\Cliente';
        $datos['referencia_tercero_id'] = $datos['cliente_id'];
        $datos['valor_documento'] = $total_documento;
        $datos['valor_pagado'] = 0;
        $datos['saldo_pendiente'] = $total_documento;
        $datos['estado'] = 'Pendiente';
        CxcMovimiento::create( $datos );

        return true;
    }

    public function show($id)
    {
        $this->set_variables_globales();

        return redirect('ventas/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=ventas.notas_credito.show');
    }

    /*
        Nota con base en devoluciones pendientes
    */
    public function nota_devolucion_pendiente( Request $request )
    {
        $datos = $request->all();

        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $nota_credito = $encabezado_documento->crear_nuevo( $request->all() );

        $lineas_registros = json_decode( $request->lineas_registros );

        /*
            Nota: Ya las devoluciones están creadas
        */
        NotaCreditoDirectaController::crear_lineas_registros( $datos, $nota_credito, $lineas_registros );

        return redirect('ventas/'.$nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion.'&vista=ventas.notas_credito.show');
    }

    // Se crean los registros con base en los registros de la devolución o devoluciones
    public static function crear_lineas_registros( $datos, $nota_credito, $lineas_registros )
    {
        $total_documento = 0;
        // Por cada devolución pendiente
        $cantidad_registros = count( $lineas_registros );
        $remision_doc_encabezado_id = ''; // corresponde a la devolución
        $primera = true;
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_devolucion_id = (int)$lineas_registros[$i]->id_doc;

            /*
                Nota: Ya las devoluciones están creadas
            */
            $registros_devoluciones = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_devolucion_id )->get();
            foreach ($registros_devoluciones as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad * -1;

                // Los precios se deben traer de la lista de precios del cliente
                $precio_unitario = ListaPrecioDetalle::get_precio_producto( $datos['lista_precios_id'], $datos['fecha'], $un_registro->inv_producto_id );

                $precio_total = $precio_unitario * $cantidad;
                
                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, 0, $nota_credito->cliente_id );

                $base_impuesto = $precio_unitario / ( 1 + $tasa_impuesto / 100 );

                $base_impuesto_total = abs($base_impuesto * $cantidad);

                $linea_datos = [ 'vtas_motivo_id' => $un_registro->inv_motivo_id ] +
                                [ 'inv_producto_id' => $un_registro->inv_producto_id ] +
                                [ 'precio_unitario' => $precio_unitario ] +
                                [ 'cantidad' => $cantidad ] +
                                [ 'precio_total' => $precio_total ] +
                                [ 'base_impuesto' =>  $base_impuesto ] +
                                [ 'tasa_impuesto' => $tasa_impuesto ] +
                                [ 'valor_impuesto' => ( $precio_unitario - $base_impuesto ) ] +
                                [ 'base_impuesto_total' => $base_impuesto_total ] +
                                [ 'creado_por' => Auth::user()->email ] +
                                [ 'estado' => 'Activo' ];

                VtasDocRegistro::create( 
                                    $datos + 
                                    [ 'vtas_doc_encabezado_id' => $nota_credito->id ] +
                                    $linea_datos
                                );

                $datos['consecutivo'] = $nota_credito->consecutivo;
                VtasMovimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );

                // CONTABILIZAR
                $detalle_operacion = $datos['descripcion'];
                NotaCreditoController::contabilizar_movimiento_debito( $datos + $linea_datos, $detalle_operacion );

                $total_documento += $precio_total;
            } // Fin por cada registro de la remisión

            // Marcar la remisión como facturada
            InvDocEncabezado::find( $doc_devolucion_id )->update( [ 'estado' => 'Facturada' ] );

            // Se va creando un listado de remisiones separadas por coma 
            if ($primera)
            {
                $remision_doc_encabezado_id = $doc_devolucion_id;
                $primera = false;
            }else{
                $remision_doc_encabezado_id .= ','.$doc_devolucion_id;
            }

        }

        $nota_credito->valor_total = $total_documento;
        $nota_credito->remision_doc_encabezado_id = $remision_doc_encabezado_id;
        $nota_credito->save();
        
        // Un solo registro para reversar la cuenta por cobrar (CR)
        NotaCreditoController::contabilizar_movimiento_credito( $datos + $linea_datos, $total_documento, $detalle_operacion, null );

        // Cargar a los registros de cuentas por pagar
        $datos['modelo_referencia_tercero_index'] = 'App\Ventas\Cliente';
        $datos['referencia_tercero_id'] = $datos['cliente_id'];
        $datos['valor_documento'] = $total_documento;
        $datos['valor_pagado'] = 0;
        $datos['saldo_pendiente'] = $total_documento;
        $datos['estado'] = 'Pendiente';
        CxcMovimiento::create( $datos );

        return true;
    }

    public function anular( $id )
    {
        $this->set_variables_globales();

        $documento = VtasDocEncabezado::find( $id ); 

        $array_wheres = ['core_empresa_id'=>$documento->core_empresa_id, 
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo];

        // Está en un documento cruce de cxp?
        $cantidad = CxcAbono::where($array_wheres)
                            ->where('doc_cruce_transacc_id','<>',0)
                            ->count();

        if($cantidad != 0)
        {
            return redirect( 'ventas/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=ventas.notas_credito.show')->with('mensaje_error','Nota NO puede ser anulada. Está en documento cruce de CxC.');
        }

        // 1ro. Anular documento asociado de inventarios
        InventarioController::anular_documento_inventarios( $documento->remision_doc_encabezado_id );

        // 2do. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el documento del movimimeto de cuentas por pagar
        CxcMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el movimiento de ventas
        VtasMovimiento::where($array_wheres)->delete();

        $modificado_por = Auth::user()->email;
        // 5to. Se marcan como anulados los registros del documento
        VtasDocRegistro::where( 'vtas_doc_encabezado_id', $documento->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $documento->update( [ 'estado' => 'Anulado', 'ventas_doc_relacionado_id' => '', 'remision_doc_encabezado_id' => '', 'modificado_por' => $modificado_por] );

        return redirect( 'ventas/'.$id.'?id='.$this->app->id.'&id_modelo='.$this->modelo->id.'&id_transaccion='.$this->transaccion->id.'&vista=ventas.notas_credito.show')->with('flash_message','Nota anulada correctamente.');
    }

}