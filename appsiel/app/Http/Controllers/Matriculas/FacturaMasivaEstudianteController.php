<?php

namespace App\Http\Controllers\Matriculas;


use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Matriculas\FacturaEstudianteController;
use App\Http\Controllers\Ventas\VentaController;

use Input;
use DB;
use Auth;

use App\Sistema\TipoTransaccion;
use App\Sistema\Modelo;
use App\Core\TipoDocApp;
use App\Core\Tercero;

use App\Core\Empresa;

use App\CxC\CxcDocRegistro;
use App\CxC\CxcMovimiento;
use App\CxC\CxcServicio;
use App\CxC\CxcEstadoCartera;

use App\Tesoreria\TesoPlanPagosEstudiante;

use App\Matriculas\FacturaAuxEstudiante;

use App\Contabilidad\ContabMovimiento;

use App\Inventarios\InvProducto;
use App\Ventas\VtasDocEncabezado;

class FacturaMasivaEstudianteController extends TransaccionController
{
    protected $datos = [];

    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Muestra el formulario para la Generación
     *
     */
    public function index()
    {
        $this->set_variables_globales();

        $tipo_transaccion = TipoTransaccion::where('core_modelo_id', Input::get('id_modelo') )->get()->first();

        $id_transaccion = $tipo_transaccion->id;

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find( Input::get('id_modelo') );

        $lista_campos = ModeloController::get_campos_modelo( $modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create');

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_array_miga_pan( $this->app, $modelo, 'Crear: '.$tipo_transaccion->descripcion );

        return view('matriculas.facturas.generacion_masiva', compact('form_create','id_transaccion','miga_pan') );//,'empresas'
    }


    /**
     * 
     */
    public function generar_consulta_preliminar(Request $request, TesoPlanPagosEstudiante $obj_planes_pagos)
    {
        $concepto_id = $request->concepto_id;

        if ( $concepto_id == '' )
        {
            $concepto_id = null;
        }

        $planes_pagos = $obj_planes_pagos->get_registros_pendientes_o_vencidos_a_la_fecha( $request->fecha, $concepto_id );

        $thead = '<tr>
                    <th style="display:none;">linea_plan_pago_id</th>
                    <th style="display:none;">valor</th>
                    <th>Estudiante</th> 
                    <th>Acudiente</th>
                    <th width="280px">Concepto</th>
                    <th> Fecha vencimiento </th>
                    <th> Precio Unit. </th>
                    <th>Cantidad</th>
                    <th>Precio Total</th>
                    <th>&nbsp;</th>
                </tr>';

        $tbody = '';
        $precio_total = 0;
        $cantidad_estudiantes = 0;
        $cantidad_registros = 0;
        $estudiante_anterior_id = 0;
        foreach ( $planes_pagos as $registro_plan_pagos )
        {
            $clase_danger = 'danger';
            $linea_plan_pago_id = 0;

            $estudiante = $registro_plan_pagos->estudiante;

            $acudiente = $estudiante->responsable_financiero();
            
            $descripcion_acudiente = 'Sin responsable financiero. Asignar responsable aquí: <a href="' . url( 'matriculas/estudiantes/gestionresponsables/estudiante_id?id=1&id_modelo=29&estudiante_id=' . $estudiante->id ) . '" target="_blank" title="Gestionar Responsables" class="btn btn-success btn-xs">  <i class="fa fa-arrow-right"></i> </a>';
            
            if ( !is_null( $acudiente ) )
            {
                $descripcion_acudiente = $acudiente->tercero->descripcion . ': Responsable financiero no está creado como cliente. Crear como cliente aquí: <a href="' . url( 'web/create?id=13&id_modelo=157' ) . '" target="_blank" title="Crear tercero como cliente" class="btn btn-primary btn-xs">  <i class="fa fa-arrow-right"></i> </a>';

                if ( !is_null( $acudiente->tercero->cliente() ) )
                {
                    $clase_danger = '';
                    $linea_plan_pago_id = $registro_plan_pagos->id;
                    $descripcion_acudiente = $acudiente->tercero->descripcion;
                }
            }

            $btn_imprimir_factura = '';
            $factura_estudiante = FacturaAuxEstudiante::where( 'cartera_estudiante_id', $registro_plan_pagos->id )->first();
            if ( !is_null( $factura_estudiante ) ) // Si ya tiene factura
            {
                continue;
            }
            
            $tbody .= '<tr class="'.$clase_danger.'">
                        <td style="display:none;">' . $linea_plan_pago_id . '</td>
                        <td style="display:none;" class="valor">' . $registro_plan_pagos->valor_cartera . '</td>
                        <td>' . $estudiante->tercero->descripcion . '</td>
                        <td>' . $descripcion_acudiente . '</td>
                        <td>' . $registro_plan_pagos->concepto->descripcion . '</td>
                        <td>' . $registro_plan_pagos->fecha_vencimiento . '</td>
                        <td>$'. number_format( $registro_plan_pagos->valor_cartera, 0, ',', '.') . '</td>
                        <td>1</td>
                        <td>$'. number_format( $registro_plan_pagos->valor_cartera, 0, ',', '.') . '</td>
                        <td> 
                            <a class="btn btn-primary btn-xs btn-detail" href="' . url( 'tesoreria/ver_plan_pagos/' . $registro_plan_pagos->libreta->id . '?id=3&amp;id_modelo=31&amp;id_transaccion=') . '" title="Consultar libreta" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>
                            ' . $btn_imprimir_factura . '
                            <button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-trash"></i></button>                        
                        </td>
                    </tr>';
            
            $precio_total += $registro_plan_pagos->valor_cartera;
            
            $cantidad_registros++;

            if ($estudiante->id != $estudiante_anterior_id )
            {
                $cantidad_estudiantes++;
            }

            $estudiante_anterior_id = $estudiante->id;
            
        }

        $tbody.='<tr>
                <td colspan="6"></td>
                <td id="total_facturas">$'.number_format($precio_total, 0, ',', '.').'</td>
                <td></td>
            </tr>';

        return response()->json( [ 
                        'thead' => $thead,
                        'tbody' => $tbody,
                        'precio_total' => number_format($precio_total, 0, ',', '.'),
                        'cantidad_registros' => $cantidad_registros,
                        'cantidad_estudiantes' => $cantidad_estudiantes
                    ] );
    }

    /**
     * PETICIÓN AJAX
     * Almacena la Generación de CxC.
     */
    public function store(Request $request)
    {
        
        $tbody = '';
        $precio_total = 0;
        $i = 0;
        $cantidad_facturas = 0;
        $cantidad_estudiantes = 0;
        $lineas_registros = json_decode( $request->lineas_registros );
        $estudiante_anterior = 'estudiante_anterior';
        // POR CADA LINEA DE REGISTRO
        foreach ( $lineas_registros as $linea )
        {
            if ( (int)$linea->linea_plan_pago_id == 0 )
            {
                continue;
            }

            $registro_plan_pagos = TesoPlanPagosEstudiante::find( $linea->linea_plan_pago_id );

            $factura = $this->crear_factura_estudiante_desde_registro_plan_pagos( $registro_plan_pagos, $request->fecha );

            $tbody .= '<tr>
                        <td>' . $registro_plan_pagos->estudiante->tercero->descripcion . '</td>
                        <td>' . $registro_plan_pagos->estudiante->responsable_financiero()->tercero->descripcion . '</td>
                        <td> <a href="' . url( 'vtas_imprimir/' . $factura->id . '?id=13&id_modelo=139&id_transaccion=' . config('matriculas.transaccion_id_factura_estudiante') . '&formato_impresion_id=estandar' ) . '" target="_blank" title="Vista previa">' . $factura->tipo_documento_app->prefijo . ' ' . $factura->consecutivo . '</a> </td>
                        <td>' . number_format( $factura->valor_total, 0, ',', '.' ) . '</td>
                    </tr>';

            $precio_total += $factura->valor_total;

            $cantidad_facturas++;

            if ( $linea->Estudiante != $estudiante_anterior )
            {
                $cantidad_estudiantes++;
            }

            $estudiante_anterior = $linea->Estudiante;
        }

        $thead = '<tr>
                        <th>Estudiante</th>
                        <th>Acudiente</th>
                        <th>Factura</th>
                        <th>Valor total</th>
                    </tr>';

        $mensaje = '<div class="alert alert-success">
                      <strong>¡Transacción exitosa!</strong> Facturas creadas correctamente.
                    </div>';
        
        return response()->json( [ 
                        'thead' => $thead,
                        'tbody' => $tbody,
                        'precio_total' => number_format($precio_total, 0, ',', '.'),
                        'cantidad_facturas' => $cantidad_facturas,
                        'cantidad_estudiantes' => $cantidad_estudiantes,
                        'mensaje' => $mensaje
                    ] );
    }

    public function prueba()
    {
        dd( VtasDocEncabezado::find( 105 )->tipo_documento_app );
    }

    public function crear_factura_estudiante_desde_registro_plan_pagos( $registro_plan_pagos, $fecha_factura )
    {
        $request = $this->preparar_datos_factura_estudiante( $registro_plan_pagos, $fecha_factura );

        $request['remision_doc_encabezado_id'] = 0;
        $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);

        // Crear Líneas de registros del documento de ventas
        $lineas_registros = json_decode($request->lineas_registros);
        $request['creado_por'] = Auth::user()->email;
        $request['registros_medio_pago'] = '[]';
        VentaController::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        $aux_factura = FacturaAuxEstudiante::create( [ 'vtas_doc_encabezado_id' => $doc_encabezado->id,
                                                        'matricula_id' => (int)$request->matricula_id,
                                                        'cartera_estudiante_id' => (int)$request->cartera_estudiante_id
                                                     ] );

        return $doc_encabezado;
    }


    public function preparar_datos_factura_estudiante( $registro_plan_pagos, $fecha_factura )
    {
        $id_modelo = config('matriculas.modelo_id_factura_estudiante'); // Factura de Estudiantes
        $id_transaccion = config('matriculas.transaccion_id_factura_estudiante'); // Factura de Ventas

        $tipo_transaccion = TipoTransaccion::find( $id_transaccion );
        
        $datos = new Request;
        $datos["core_empresa_id"] = Auth::user()->empresa_id;
        $datos["core_tipo_doc_app_id"] = $tipo_transaccion->tipos_documentos->first()->id; // FV - Factura de venta
        $datos["fecha"] = $fecha_factura;
        $datos["cliente_input"] = "";

        $cliente = $registro_plan_pagos->estudiante->responsable_financiero()->tercero->cliente();
        $datos["vendedor_id"] = $cliente->vendedor_id;
        $datos["forma_pago"] = "credito";
        $datos["fecha_vencimiento"] = $fecha_factura;
        $datos["inv_bodega_id"] = $cliente->inv_bodega_id;
        $datos["cliente_id"] = $cliente->id;
        $datos["inv_bodega_id_aux"] = "";
        $datos["zona_id"] = "1";
        $datos["clase_cliente_id"] = $cliente->clase_cliente_id;
        $datos["core_tercero_id"] = $cliente->core_tercero_id;
        $datos["lista_precios_id"] = $cliente->lista_precios_id;
        $datos["lista_descuentos_id"] = $cliente->lista_descuentos_id;
        $datos["liquida_impuestos"] = $cliente->liquida_impuestos;
        
        $datos["orden_compras"] = "";
        $datos["descripcion"] = "";
        $datos["consecutivo"] = "";
        $datos["core_tipo_transaccion_id"] = $id_transaccion;
        $datos["url_id"] = "3";
        $datos["url_id_modelo"] = $id_modelo;
        $datos["url_id_transaccion"] = $id_transaccion;

        $datos["estudiante_id"] = $registro_plan_pagos->estudiante->id;
        $datos["matricula_id"] = $registro_plan_pagos->estudiante->matricula_activa()->id;
        $datos["cartera_estudiante_id"] = $registro_plan_pagos->id;
        $datos["libreta_id"] = $registro_plan_pagos->id_libreta;

        $datos["lineas_registros"] = $this->crear_json_linea_registro_ventas( $registro_plan_pagos->inv_producto_id, $registro_plan_pagos->valor_cartera );
        $datos["lineas_registros_medios_recaudo"] = "[]";
        $datos["tipo_transaccion"] = "factura_directa";
        $datos["rm_tipo_transaccion_id"] = config('ventas.rm_tipo_transaccion_id');
        $datos["dvc_tipo_transaccion_id"] = config('ventas.dvc_tipo_transaccion_id');
        $datos["saldo_original"] = "0";

        return $datos;
    }

    public function crear_json_linea_registro_ventas( $inv_producto_id, $precio_unitario )
    {
        return '[{"inv_motivo_id":"10","inv_bodega_id":"1","inv_producto_id":"'.$inv_producto_id.'","costo_unitario":"0","precio_unitario":"'.$precio_unitario.'","base_impuesto":"'.$precio_unitario.'","tasa_impuesto":"0","valor_impuesto":"0","base_impuesto_total":"'.$precio_unitario.'","cantidad":"1","costo_total":"0","precio_total":"'.$precio_unitario.'","tasa_descuento":"0","valor_total_descuento":"0","Item":"","Motivo":"","Stock":"0","Cantidad":"1","Precio Unit. (IVA incluido)":"$0","Dcto. (%)":"0%","Dcto. Tot. ($)":"$ 0","IVA":"0%","Total":"$0"}]';
    }

}