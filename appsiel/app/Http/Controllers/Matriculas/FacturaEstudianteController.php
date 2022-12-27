<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;

use Auth;
use View;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Inventarios\InventarioController;

use App\Http\Controllers\Ventas\VentaController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Core\Tercero;

use App\Matriculas\Estudiante;
use App\Matriculas\FacturaAuxEstudiante;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvProducto;

use App\Ventas\VtasTransaccion;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\Ventas\Cliente;
use App\Ventas\ResolucionFacturacion;
use App\Ventas\NotaCredito;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\Tesoreria\TesoMovimiento;

use App\Contabilidad\ContabMovimiento;
use App\Matriculas\Services\FacturaEstudiantesService;
use Illuminate\Support\Facades\Input;

class FacturaEstudianteController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida'=>'Ventas POS'];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros( VtasTransaccion::get_datos_tabla_ingreso_lineas_registros( $this->transaccion, $motivos ) );

        $lista_campos = ModeloController::get_campos_modelo( $this->modelo, '', 'create' );
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, $cantidad_campos, 'create', null);

        $estudiante = Estudiante::find( Input::get('estudiante_id') );

        $responsable_financiero_estudiante = $estudiante->responsable_financiero();

        if ( empty( $responsable_financiero_estudiante ) )
        {
            return redirect( 'tesoreria/ver_plan_pagos/' . Input::get('libreta_id') . '?id=3&id_modelo=31&id_transaccion=' )->with( 'mensaje_error', 'El estudiante no tiene responsable financiero asociado.');
        }

        $cliente = Cliente::where('core_tercero_id', $responsable_financiero_estudiante->tercero_id )->get()->first();
        if ( is_null( $cliente ) )
        {
            return redirect( 'tesoreria/ver_plan_pagos/' . Input::get('libreta_id') . '?id=3&id_modelo=31&id_transaccion=' )->with( 'mensaje_error', 'El responsable financiero no esta creado como cliente.');
        }

        foreach ($lista_campos as $key => $value)
        {
            if ($value['name'] == 'cliente_input')
            {
                $lista_campos[$key]['value'] = $estudiante->tercero->descripcion;
                $lista_campos[$key]['atributos'] = ['readonly'=>'readonly'];
            }

            if ($value['name'] == 'inv_bodega_id')
            {
                $lista_campos[$key]['value'] = $cliente->inv_bodega_id;
            }

            if ($value['name'] == 'forma_pago')
            {
                $lista_campos[$key]['value'] = 'credito';
                $lista_campos[$key]['atributos'] = ['readonly'=>'readonly'];
            }
        }

        $concepto = InvProducto::find( Input::get('inv_producto_id') );

        $linea_registro = '<tr class="linea_registro" data-numero_linea="1"><td style="display: none;"><div class="inv_motivo_id">10</div></td><td style="display: none;"><div class="inv_bodega_id">1</div></td><td style="display: none;"><div class="inv_producto_id">'. $concepto->id .'</div></td><td style="display: none;"><div class="costo_unitario">0</div></td><td style="display: none;"><div class="precio_unitario">'. Input::get('valor_cartera') .'</div></td><td style="display: none;"><div class="base_impuesto">'. Input::get('valor_cartera') .'</div></td><td style="display: none;"><div class="tasa_impuesto">0</div></td><td style="display: none;"><div class="valor_impuesto">0</div></td><td style="display: none;"><div class="base_impuesto_total">'. Input::get('valor_cartera') .'</div></td><td style="display: none;"><div class="cantidad">1</div></td><td style="display: none;"><div class="costo_total">0</div></td><td style="display: none;"><div class="precio_total">'. Input::get('valor_cartera') .'</div></td><td style="display: none;"><div class="tasa_descuento">0</div></td><td style="display: none;"><div class="valor_total_descuento">0</div></td><td> &nbsp; </td><td> <span style="background-color:#F7B2A3;">'. $concepto->id .'</span> '. $concepto->id . ' - ' . $concepto->descripcion .'  </td><td>Ventas POS</td><td> 0</td><td>1 </td><td> $ '. number_format( Input::get('valor_cartera'), 0, ',', '.' ) .'</td><td>0% </td><td> $ 0</td><td>0%</td><td> $ '. number_format( Input::get('valor_cartera'), 0, ',', '.' ) .' </td><td> &nbsp; </td></tr>';

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo( $this->modelo, '' );
        
        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $id_transaccion = $this->transaccion->id;

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Crear: '.$this->transaccion->descripcion );

        return view( 'matriculas.facturas.create', compact('form_create','miga_pan','tabla','id_transaccion','motivos', 'cliente', 'responsable_financiero_estudiante', 'estudiante','linea_registro') );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Crear documento de Ventas
        $request['remision_doc_encabezado_id'] = 0;

        if (!isset($request['vendedor_id'])) {
            $request['vendedor_id'] = 1;
        }

        if (!isset($request['fecha_vencimiento'])) {
            $request['fecha_vencimiento'] = $request['fecha'];
        }

        if (!isset($request['forma_pago'])) {
            $request['forma_pago'] = 'credito';
        }        
        
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
        
        return redirect( 'tesoreria/ver_plan_pagos/' . (int)$request->libreta_id . '?id=3&id_modelo=31&id_transaccion=' )->with( 'flash_message', 'Factura creada correctamente.');

    }


    /**
     *
     */
    public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $encabezado_documento = app( $this->transaccion->modelo_encabezados_documentos )->find( $id );

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );

        $docs_relacionados = VtasDocEncabezado::get_documentos_relacionados( $doc_encabezado );
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

        return view( $vista, compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'encabezado_documento', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_credito') );
    }


    /*
        Imprimir
    */
    public function imprimir( $id )
    {
        $documento_vista = $this->generar_documento_vista( $id, 'ventas.formatos_impresion.'.Input::get('formato_impresion_id') );

        // Se prepara el PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream( $this->doc_encabezado->documento_transaccion_descripcion.' - '.$this->doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
        
    }

    /*
        Enviar por email
    */
    public function enviar_por_email( $id )
    {
        $documento_vista = $this->generar_documento_vista( $id, 'ventas.formatos_impresion.'.Input::get('formato_impresion_id') );

        $tercero = Tercero::find( $this->doc_encabezado->core_tercero_id );

        $asunto = $this->doc_encabezado->documento_transaccion_descripcion.' No. '.$this->doc_encabezado->documento_transaccion_prefijo_consecutivo;

        $cuerpo_mensaje = 'Saludos, <br/> Le hacemos llegar su '. $asunto;

        $vec = EmailController::enviar_por_email_documento( $this->empresa->descripcion, $tercero->email, $asunto, $cuerpo_mensaje, $documento_vista );

        return redirect( 'ventas/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( $vec['tipo_mensaje'], $vec['texto_mensaje'] );
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

        $abonos = CxcAbono::get_abonos_documento( $doc_encabezado );

        return View::make( $ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas', 'abonos' ) )->render();
    }

    /*
        Proceso de eliminar FACTURA DE VENTAS
        Se eliminan los registros de:
            - cxc_documentos_pendientes (se debe verificar que no tenga un abono, sino se debe eliminar primero el abono) y su movimiento en contab_movimientos
            - inv_movimientos de la REMISIÓN y su contabilidad. Además se actualiza el estado a Anulado en inv_doc_registros e inv_doc_encabezados
            - vtas_movimientos y su contabilidad. Además se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public static function anular_factura(Request $request)
    {        
        $factura = VtasDocEncabezado::find( $request->factura_id );

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

        // 3ro. Se elimina el documento del movimimeto de cuentas por cobrar y de tesorería
        CxcMovimiento::where($array_wheres)->delete();
        TesoMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el movimiento de ventas
        VtasMovimiento::where($array_wheres)->delete();
        // 5to. Se marcan como anulados los registros del documento
        VtasDocRegistro::where( 'vtas_doc_encabezado_id', $factura->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $factura->update(['estado'=>'Anulado', 'remision_doc_encabezado_id' => '', 'modificado_por' => $modificado_por]);

        return redirect( 'ventas/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('flash_message','Factura de ventas ANULADA correctamente.');
        
    }

    public function index_facturas_plan_pagos()
    {
        $factura_estudiante_serv = new FacturaEstudiantesService();
        
        $fecha_desde = date('Y-01-01');
        $fecha_hasta = date('Y-m-d');
        if (Input::get('fecha_desde')!=null) {
            $fecha_desde = Input::get('fecha_desde');
            $fecha_hasta = Input::get('fecha_hasta');
        }

        $plan_pagos = $factura_estudiante_serv->get_rows_planes_pagos($fecha_desde,$fecha_hasta);     

        $miga_pan = [
                ['url'=>'tesoreria?id='.Input::get('id'),'etiqueta'=>'Tesorería'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo=31','etiqueta'=>'Libretas de pagos'],
                ['url'=>'NO','etiqueta'=>'Planes de pagos de estudiantes']
            ];

        return view('tesoreria.ver_facturas_aux_estudiantes', compact( 'plan_pagos', 'miga_pan') );
    }

}
