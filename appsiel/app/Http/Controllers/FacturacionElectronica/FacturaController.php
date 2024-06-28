<?php

namespace App\Http\Controllers\FacturacionElectronica;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\TransaccionController;

use App\Core\TipoDocApp;
use App\Sistema\Html\BotonesAnteriorSiguiente;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\NotaCredito;

use App\CxC\CxcAbono;
use App\FacturacionElectronica\DATAICO\FacturaGeneral;
use App\Tesoreria\TesoMovimiento;

use App\FacturacionElectronica\Factura;
use App\FacturacionElectronica\ResultadoEnvioDocumento;
use App\FacturacionElectronica\Services\DocumentHeaderService;
use App\Inventarios\RemisionVentas;
use App\VentasPos\FacturaPos;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;
use Khill\Lavacharts\Laravel\LavachartsFacade;

class FacturaController extends TransaccionController
{
    protected $documento_factura;

    public function index()
    {
        /*
            $documentos = $this->grafica_documentos_x_mes();
        */
    	return view('facturacion_electronica.index');
    }

    public function grafica_documentos_x_mes( $periodo_lectivo )
    {
        //  PENDIENTE POR TERMINAR


        $fecha_ini = date('Y-m-01');
        //$fecha_fin = ;
        $documentos = ResultadoEnvioDocumento::where([
            ['codigo','=',201]
        ])
            ->leftJoin('vtas_doc_encabezados', 'vtas_doc_encabezados.id', '=', 'fe_resultados_envios_documentos.vtas_doc_encabezado_id')
            ->select(DB::raw('COUNT(sga_matriculas.id_estudiante) AS Cantidad'), 'sga_estudiantes.genero AS Genero')
            ->groupBy('sga_estudiantes.genero')
            ->get();

        // Creación de gráfico de Barras
        $stocksTable2 = LavachartsFacade::DataTable();
        
        $stocksTable2->addStringColumn('Genero')
                    ->addNumberColumn('Cantidad');
        
        foreach($documentos as $registro){
            $stocksTable2->addRow([
              $registro->Genero, (int)$registro->Cantidad
            ]);
        }

        LavachartsFacade::PieChart('Documentos', $stocksTable2);
        
        return $documentos;
    }

    public function show( $id )
    {
    	$this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );

        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );

        $docs_relacionados = VtasDocEncabezado::get_documentos_relacionados( $doc_encabezado );
        $empresa = $this->empresa;
        $id_transaccion = $this->transaccion->id;

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $registros_tesoreria = TesoMovimiento::get_registros_un_documento( $doc_encabezado->core_tipo_transaccion_id, $doc_encabezado->core_tipo_doc_app_id, $doc_encabezado->consecutivo );
        $medios_pago = View::make('tesoreria.incluir.show_medios_pago', compact('registros_tesoreria'))->render();

        // Datos de los abonos aplicados a la factura
        $abonos = CxcAbono::get_abonos_documento( $doc_encabezado );

        // Datos de Notas Crédito aplicadas a la factura
        $notas_credito = NotaCredito::get_notas_aplicadas_factura( $doc_encabezado->id );

        $documento_vista = '';

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        //$url_crear = $this->modelo->url_crear.$this->variables_url;
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );

        $url_crear = $acciones->create;

        return view( 'facturacion_electronica.facturas.show', compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_credito','medios_pago') );

    }

    public function store( Request $request )
    {
        // 1ra. Crear documento de salida de inventarios (REMISIÓN)
        $remision = new RemisionVentas();
        $documento_remision = $remision->crear_nueva( $request->all() );

        $doc_encabezado = (new DocumentHeaderService())->store_invoice( $request, $documento_remision->id );

        $mensaje = (object)[ 'tipo'=>'flash_message', 'contenido' => 'Documento creado correctamente.' ];

        // Paso 3: Validar Resolución (secuenciales) del documento
        if ( empty( $doc_encabezado->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
            $mensaje->tipo = 'mensaje_error';
            $mensaje->contenido .= ' NOTA: El documento de factura no tiene resolución asociada.';
        }

    	return redirect( 'fe_factura/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion)->with( $mensaje->tipo, $mensaje->contenido );
    }

    public function set_fields_default($datos)
    {
        if (!isset($datos['inv_bodega_id'])) {
            $datos['inv_bodega_id'] = (int)config('ventas.inv_bodega_id');
        }else{
            if (in_array($datos['inv_bodega_id'],[0,''])) {
                $datos['inv_bodega_id'] = (int)config('ventas.inv_bodega_id');
            }
        }

        if (!isset($datos['vendedor_id'])) {
            $datos['vendedor_id'] = (int)config('ventas.vendedor_id');
        }else{
            if (in_array($datos['vendedor_id'],[0,''])) {
                $datos['vendedor_id'] = (int)config('ventas.vendedor_id');
            }
        }

        return $datos;
    }

    // Llamado directamente
    public function enviar_factura_electronica( $id )
    {
        $vtas_doc_encabezado = Factura::find( $id );

        if ( empty( $vtas_doc_encabezado->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
           return redirect( 'fe_factura/'.$vtas_doc_encabezado->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( 'mensaje_error', 'Documento no puede ser enviado. El prefijo ' . $vtas_doc_encabezado->tipo_documento_app->prefijo . ' no tiene una resolución asociada.');
        }

        $result = (new DocumentHeaderService())->validar_datos_tercero($vtas_doc_encabezado->cliente->tercero);

        if ( $result->status == 'error' )
        {
            return redirect( 'fe_factura/'.$vtas_doc_encabezado->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( 'mensaje_error', 'Documento no puede ser enviado. <br> El cliente presenta inconsistencia en sus datos básicos: ' . $result->message);
        }

        $mensaje = $vtas_doc_encabezado->enviar_al_proveedor_tecnologico();

        if ( $mensaje->tipo == 'mensaje_error' )
        {
            return redirect( 'fe_factura/'.$vtas_doc_encabezado->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
        }

        $documento_electronico = new FacturaGeneral( $vtas_doc_encabezado, 'factura' );
        $json_dataico = $documento_electronico->get_einvoice_in_dataico();
        
        $errores_einvoice =  $documento_electronico->get_errores($json_dataico);

        if ( $errores_einvoice != '' ) {
            return redirect( 'fe_factura/'.$vtas_doc_encabezado->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( 'mensaje_error', 'Documento no pudo ser enviado. <br> Presenta inconsistencias: ' . $errores_einvoice);
        }

        if (isset($json_dataico->invoice)) {
            if ($json_dataico->invoice->dian_status != 'DIAN_RECHAZADO') {
                // La factura ya está en DATAICO, pero no se reflejó en Appsiel
                $this->contabilizar_factura($vtas_doc_encabezado);
    
                $mensaje = (object)[
                    'tipo'=>'flash_message',
                    'contenido' => '<h3>Documento ya fue enviado correctamente hacia el proveedor tecnológico.</h3>'
                ];
    
                return redirect( 'fe_factura/'.$vtas_doc_encabezado->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
            }
        }

        if ( $mensaje->tipo != 'mensaje_error' )
        {
            $this->contabilizar_factura($vtas_doc_encabezado);
        }

        return redirect( 'fe_factura/'.$vtas_doc_encabezado->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
    }

    public function contabilizar_factura($encabezado_factura)
    {
        if ( $encabezado_factura->estado != 'Contabilizado - Sin enviar')
        {
            $encabezado_factura->crear_movimiento_ventas();

            // Contabilizar
            $encabezado_factura->contabilizar_movimiento_debito();
            $encabezado_factura->contabilizar_movimiento_credito();

            $encabezado_factura->crear_registro_pago();
        }
        
        FacturaPos::where([
            ['core_tipo_transaccion_id','=',$encabezado_factura->core_tipo_transaccion_id],
            ['core_tipo_doc_app_id','=',$encabezado_factura->core_tipo_doc_app_id],
            ['consecutivo','=',$encabezado_factura->consecutivo],
        ])->update([
            'estado'=>'Enviada'
        ]);
        
        $encabezado_factura->estado = 'Enviada';
        $encabezado_factura->save();
    }

    public function validar_datos_tercero($tercero)
    {
        $status = 'success';
        $message = '';

        if ( $tercero->direccion1 == '' || strlen( $tercero->direccion1 ) < 10 )
        {
            $status = 'error';
            $message .= ' - Revisar dirección';
        }

        if ( $tercero->email == '' || gettype( filter_var($tercero->email, FILTER_VALIDATE_EMAIL) ) != 'string' )
        {
            $status = 'error';
            $message .= ' - Revisar email - ';
        }

        if ( $tercero->telefono1 == '' || !is_numeric( $tercero->telefono1 ) )
        {
            $status = 'error';
            $message .= ' - Revisar teléfono - ';
        }

        if ( $tercero->tipo == 'Persona natural' )
        {
            if ( $tercero->nombre1 == '' || strlen( $tercero->nombre1 ) < 2 )
            {
                $status = 'error';
                $message .= ' - Revisar nombre completo. No tiene asignado el primer nombre. ';
            }

            if ( $tercero->apellido1 == '' || strlen( $tercero->apellido1 ) < 2 )
            {
                $status = 'error';
                $message .= 'No tiene asignado el primer apellido.';
            }
        }

        return (object)[
            'status' => $status,
            'message' => $message
        ];
    }

    // Only For POS
    public function convertir_en_factura_electronica( $vtas_doc_encabezado_id )
    {
        $tipo_doc_fe = TipoDocApp::find(config('facturacion_electronica.document_type_id_default'));
        if ( empty( $tipo_doc_fe->resolucion_facturacion->toArray() ) )
        {
            return back()->with( 'mensaje_error', 'Documento no puede ser enviado. El prefijo ' . $tipo_doc_fe->prefijo . ' no tiene una resolución asociada.');
        }

        $doc_header_serv = new DocumentHeaderService();
        $result = $doc_header_serv->convert_to_electronic_invoice( $vtas_doc_encabezado_id );

        if ( $result->status == 'mensaje_error' )
        {
            return back()->with( $result->status, $result->message);
        }

        return redirect( 'fe_factura/'. $result->new_document_header_id.'?id=21&id_modelo=244&id_transaccion=52' )->with($result->status,$result->message);
    }
    
}
