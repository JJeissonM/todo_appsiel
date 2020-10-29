<?php

namespace App\Http\Controllers\Matriculas;


use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

use Input;
use DB;

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
    public function generar_consulta_preliminar(Request $request)
    {
        $concepto = InvProducto::find( $request->concepto_id );

        if ( is_null($concepto) )
        {
            $planes_pagos = TesoPlanPagosEstudiante::where( 'fecha_vencimiento', '<=', $request->fecha )
                                        ->orWhere(function ($query) {
                                                $query->where('estado', '=', 'Pendiente')
                                                      ->where('estado', '=', 'Vencida');
                                            })
                                        ->get();
        }else{
            $planes_pagos = TesoPlanPagosEstudiante::where( 'fecha_vencimiento', '<=', $request->fecha )
                                        ->where( 'concepto', '=', $concepto->descripcion )
                                        ->orWhere(function ($query) {
                                                $query->where('estado', '=', 'Pendiente')
                                                      ->where('estado', '=', 'Vencida');
                                            })
                                        ->get();
        }
            

        $thead = '<tr>
                    <th style="display:none;">cartera_id</th>
                    <th>Estudiante  ' . $request->fecha . '</th> 
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

        foreach ( $planes_pagos as $registro_plan_pagos)
        {
            $clase_danger = 'danger';
            $cartera_id = 0;

            $estudiante = $registro_plan_pagos->estudiante;

            $acudiente = $estudiante->responsableestudiantes->where('tiporesponsable_id', 3)->first();
            
            $descripcion_acudiente = ' Sin responsable financiero. Asignar responsable aquí: <a href="' . url( 'matriculas/estudiantes/gestionresponsables/estudiante_id?id=1&id_modelo=29&estudiante_id=' . $estudiante->id ) . '" target="_blank" title="Gestionar Responsables" class="btn btn-success btn-xs">  <i class="fa fa-arrow-right"></i> </a>';
            
            if ( !is_null( $acudiente ) )
            {
                $clase_danger = '';
                $cartera_id = $registro_plan_pagos->id;
                $descripcion_acudiente = $acudiente->tercero->descripcion;
            }

            $btn_imprimir_factura = '';
            $factura_estudiante = FacturaAuxEstudiante::where( 'cartera_estudiante_id', $registro_plan_pagos->id )->first();
            if ( !is_null($factura_estudiante)) {
                $clase_danger = 'danger';
                $cartera_id = 0;
                $btn_imprimir_factura = '<a class="btn btn-success btn-xs btn-detail" href="' . url( 'vtas_imprimir/' . $factura_estudiante->vtas_doc_encabezado_id . '?id=13&amp;id_modelo=139&amp;id_transaccion=23&amp;formato_impresion_id=estandar') . '" title="Imprimir Factura" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;Imprimir Factura</a>';
            }
            
            $tbody .= '<tr class="'.$clase_danger.'">
                        <td style="display:none;">' . $cartera_id . '</td>
                        <td>' . $estudiante->tercero->descripcion . '</td>
                        <td>' . $descripcion_acudiente . '</td>
                        <td>' . $registro_plan_pagos->concepto . '</td>
                        <td>' . $registro_plan_pagos->fecha_vencimiento . '</td>
                        <td>$'. number_format( $registro_plan_pagos->valor_cartera, 0, ',', '.') . '</td>
                        <td>1</td>
                        <td>$'. number_format( $registro_plan_pagos->valor_cartera, 0, ',', '.') . '</td>
                        <td> 
                            <a class="btn btn-primary btn-xs btn-detail" href="' . url( 'tesoreria/ver_plan_pagos/' . $registro_plan_pagos->libreta->id . '?id=3&amp;id_modelo=31&amp;id_transaccion=') . '" title="Consultar libreta" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>
                            ' . $btn_imprimir_factura . '                            
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
                <td>$'.number_format($precio_total, 0, ',', '.').'</td>
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
        $cant_propiedades = 0;
        $primer_registro = 0;
        $lineas_registros = json_decode( $request->lineas_registros );
        // POR CADA LINEA DE REGISTRO
        foreach ( $lineas_registros as $linea )
        {
            if ( (int)$linea->cartera_id == 0 )
            {
                continue;
            }

            $this->crear_factura_estudiante( $linea->cartera_id );


            dd( $linea );

            $cant_propiedades++;

            // 1. SE CREA EL ENCABEZADO DEL DOCUMENTO (DocumentoCxC)
            // 1.1. Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual($request->core_empresa_id,$request->core_tipo_doc_app_id) + 1;

            // Se obtiene el primer documento generado para la impresión por lotes
            if ($primer_registro==0) {
                $primer_registro = $consecutivo;
            }

            // 1.2. incementamos el consecutivo para ese tipo de documento y empresa
            TipoDocApp::aumentar_consecutivo($request->core_empresa_id,$request->core_tipo_doc_app_id);

            // 1.3. Se REEMPLAZA el consecutivo en los datos del request
            // Tambien se adiciona el codigo_referencia_tercero
            $this->datos = array_merge($request->all(),['consecutivo' => $consecutivo, 'codigo_referencia_tercero' => $propiedad['id'], 'tipo_movimiento' => 'Facturación masiva' ]);
            
            // 1.4. Se guarda el encabezado del documento 
            $propiedad_id = $propiedad['id'];
            $core_tercero_id = $propiedad['core_tercero_id'];

            $cxc_doc_encabezado = app($modelo->name_space)->create( $this->datos +  
                            ['core_tercero_id'=>$core_tercero_id] );

            
            // 2. SE CREAN LOS REGISTROS DEL DOCUMENTO Y EL MOVIMIENTO DE CARTERA
            // 2.1. Se van hayando las variables de los campos que se almacenarán en LOS REGISTROS Y  el movimiento
            
            $cxc_motivo_id = 1; // 1 = Generación masiva CxC Prop. Horizontal
            $valor_cartera = 0;

            // Se verifica si el inmueble tiene un Vlr. de cuota de administración por defecto
            // Si no lo tiene se usa el concepto asignado por defecto
            if ( (float)$propiedad['valor_cuota_defecto'] > 0 ) {
                $cxc_servicio_id = 0;
                $precio_venta = (float)$propiedad['valor_cuota_defecto'];
                $detalle_operacion = 'Cuota de administración - '.$request->descripcion;
            }else{
                $servicio_default = CxcServicio::find($propiedad['cxc_servicio_id']);
                $cxc_servicio_id = $servicio_default->id;
                $precio_venta = $servicio_default->precio_venta;
                $detalle_operacion = $servicio_default->descripcion.' - '.$request->descripcion;
            }
                

            CxcDocRegistro::create(
                            [ 'cxc_doc_encabezado_id' => $cxc_doc_encabezado->id ] +
                            [ 'cxc_motivo_id' => $cxc_motivo_id ] + 
                            [ 'cxc_servicio_id' => $cxc_servicio_id ] + 
                            [ 'valor_unitario' => $precio_venta ] + 
                            [ 'cantidad' => 1 ] +
                            [ 'valor_total' => $precio_venta ] +
                            [ 'descripcion' => $detalle_operacion ] +
                            [ 'estado' => 'Activo' ] );
            
            $valor_cartera+=$precio_venta;

            /*
                **  Generar la contabilidad (Cartera vs Ingresos)
            */
            // CARTERA (DB)
            $contab_cuenta = Tercero::find($core_tercero_id)->cuenta_cartera;
            $contab_cuenta_id = $contab_cuenta->id; 

            $valor_debito = $precio_venta;

            $valor_credito = 0;

            $this->contabilizar_registro( $core_tercero_id, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

            // INGRESOS (CR)
            if ( isset($servicio_default) ) {
                $contab_cuenta_id = $servicio_default->contab_cuenta_id;
            }else{
                $contab_cuenta_id = $propiedad['cuenta_ingresos_id'];
            }
                
            $valor_debito = 0;
            $valor_credito = $precio_venta;
            $this->contabilizar_registro( $core_tercero_id, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);


            // Se obtienen los servicios ADICIONALES asociados para cobrar
            $servicios = DB::table('ph_propiedad_tiene_servicios')
                            ->where( 'propiedad_id', $propiedad['id'] )
                            ->get();
            // Se debe recorrer cada servicio asignado a la propiedad
            foreach ($servicios as $un_servicio) {
                $sql_servicio = DB::table('cxc_servicios')->where('id',$un_servicio->cxc_servicio_id)->get();
                $el_servicio = $sql_servicio[0];

                if ( $un_servicio->valor_servicio == 0) 
                {
                    $precio_venta = $el_servicio->precio_venta;
                }else{
                    $precio_venta = $un_servicio->valor_servicio;
                }

                $detalle_operacion = $el_servicio->descripcion.' - '.$request->descripcion;
                // Se crea cada registro en la tabla cxc_doc_registros
                CxcDocRegistro::create(
                            [ 'cxc_doc_encabezado_id' => $cxc_doc_encabezado->id ] +
                            [ 'cxc_motivo_id' => $cxc_motivo_id ] + 
                            [ 'cxc_servicio_id' => $el_servicio->id ] + 
                            [ 'valor_unitario' => $precio_venta ] + 
                            [ 'cantidad' => 1 ] +
                            [ 'valor_total' => $precio_venta ] +
                            [ 'descripcion' => $detalle_operacion ] +
                            [ 'estado' => 'Activo' ] );

                $valor_cartera+=$precio_venta;

                /*
                    **  Generar la contabilidad (Cartera vs Ingresos)
                */
                // CARTERA (DB)
                $contab_cuenta = Tercero::find($core_tercero_id)->cuenta_cartera;
                $contab_cuenta_id = $contab_cuenta->id; 

                $valor_debito = $precio_venta;

                $valor_credito = 0;

                $this->contabilizar_registro( $core_tercero_id, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito);

                // INGRESOS (CR)
                $contab_cuenta_id = $el_servicio->contab_cuenta_id;
                $valor_debito = 0;
                $valor_credito = $precio_venta;
                $this->contabilizar_registro( $core_tercero_id,$contab_cuenta_id,$detalle_operacion,$valor_debito,$valor_credito);


                // Se elimina el servicio asignado a la propiedad
                DB::table('ph_propiedad_tiene_servicios')
                        ->where( 'propiedad_id', $propiedad['id'] )
                        ->where( 'cxc_servicio_id', $el_servicio->id )
                        ->delete();
            } // Fin for cada servicio asignado a la propiedad

            // 2.2. Se almacena el registro del movimiento en la tabla cxc_movimientos
            $this->datos = array_merge($this->datos,['descripcion' => 'Cobro de servicios '.$request->descripcion]);
            

            // Se agrega un nuevo estado de cartera para el movimiento creado
            CxcEstadoCartera::crear($cxc_movimiento->id, $request->fecha, 0, $valor_cartera, 'Pendiente', $request->creado_por, $request->modificado_por);

            // Se actualiza el valor total en el encabezado del documento
            $cxc_doc_encabezado->valor_total = $valor_cartera;
            $cxc_doc_encabezado->save();

            $precio_total+=$valor_cartera;
            $tipo_doc_app = TipoDocApp::find($request->core_tipo_doc_app_id);
            $documento  = $tipo_doc_app->prefijo.' '.$consecutivo;

            $tbody.='<tr>
                    <td>'.$propiedad['codigo'].'</td>
                    <td>'.$propiedad['descripcion'].'</td>
                    <td> <a href="'.url('cxc/'.$cxc_doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo).'" target="_blank" title="Vista previa">'.$documento.'</a> </td>
                    <td>'.number_format($valor_cartera, 0, ',', '.').'</td>
                </tr>';
        } // Termina bucle "Para cada propiedad"


        $thead = '<tr>
                        <th>Propiedad</th>
                        <th>Propietario</th>
                        <th>Documento</th>
                        <th>Valor total</th>
                    </tr>';

        $mensaje = '<div class="alert alert-success">
  <strong>¡Transacción exitosa!</strong> Cuentas de cobro creadas correctamente.
</div>';

        return [$thead, $tbody, number_format($precio_total, 0, ',', '.'), $cant_propiedades, $mensaje, $request->core_empresa_id, $request->core_tipo_doc_app_id, $primer_registro, $consecutivo];
    }

    public function crear_factura_estudiante_desde_registro_plan_pagos( $registro_plan_pagos, $fecha_factura )
    {
        $request = $this->preparar_datos_factura_estudiante();
    }


    public function preparar_datos_factura_estudiante( $request )
    {
        $id_modelo = config('matriculas.modelo_id_factura_estudiante'); // Factura de Estudiantes
        $id_transaccion = config('matriculas.transaccion_id_factura_estudiante'); // Factura de Ventas

        $tipo_transaccion = TipoTransaccion::find( $id_transaccion );
        
        $datos = new Request;
        $datos["core_empresa_id"] = Auth::user()->empresa_id;
        $datos["core_tipo_doc_app_id"] = $tipo_transaccion->tipos_documentos->first()->id; // FV - Factura de venta
        $datos["fecha"] = $request->fecha;
        $datos["cliente_input"] = "";

        //$cliente = 
        $datos["vendedor_id"] = "1";
        $datos["forma_pago"] = "credito";
        $datos["fecha_vencimiento"] = "2020-10-27";
        $datos["inv_bodega_id"] = "1";
        $datos["orden_compras"] = "";
        $datos["descripcion"] = "";
        $datos["consecutivo"] = "";
        $datos["core_tipo_transaccion_id"] = $id_transaccion;
        $datos["url_id"] = "3";
        $datos["url_id_modelo"] = $id_modelo;
        $datos["url_id_transaccion"] = $id_transaccion;
        $datos["estudiante_id"] = "19";
        $datos["matricula_id"] = "14";
        $datos["cartera_estudiante_id"] = "29";
        $datos["libreta_id"] = "3";
        $datos["inv_bodega_id_aux"] = "";
        $datos["cliente_id"] = "214";
        $datos["zona_id"] = "1";
        $datos["clase_cliente_id"] = "1";
        $datos["core_tercero_id"] = "479";
        $datos["lista_precios_id"] = "1";
        $datos["lista_descuentos_id"] = "1";
        $datos["liquida_impuestos"] = "1";
        $datos["lineas_registros"] = '[{"inv_motivo_id":"10","inv_bodega_id":"1","inv_producto_id":"25","costo_unitario":"0","precio_unitario":"150000","base_impuesto":"150000","tasa_impuesto":"0","valor_impuesto":"0","base_impuesto_total":"150000","cantidad":"1","costo_total":"0","precio_total":"150000","tasa_descuento":"0","valor_total_descuento":"0","Item":"25 25 - Pensión","Motivo":"Ventas POS","Stock":"0","Cantidad":"1","Precio Unit. (IVA incluido)":"$ 150.000","Dcto. (%)":"0%","Dcto. Tot. ($)":"$ 0","IVA":"0%","Total":"$ 150.000"}]';
        $datos["lineas_registros_medios_recaudo"] = "0";
        $datos["tipo_transaccion"] = "factura_directa";
        $datos["rm_tipo_transaccion_id"] = "24";
        $datos["dvc_tipo_transaccion_id"] = "34";
        $datos["saldo_original"] = "0";
    }

}