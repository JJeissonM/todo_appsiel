<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Auth;
use DB;
use View;
use Input;
use Form;

use Spatie\Permission\Models\Permission;

use App\Http\Controllers\Sistema\CrudController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Inventarios\InventarioController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Core\Empresa;
use App\Sistema\TipoTransaccion;
use App\Sistema\Modelo;
use App\Core\Tercero;

use App\Ventas\VtasTransaccion;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\ResolucionFacturacion;

class CotizacionController extends TransaccionController
{
    protected $doc_encabezado;

    protected $duplicado = false;

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
        $motivos = ['10-salida'=>'Ventas POS'];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros( VtasTransaccion::get_datos_tabla_ingreso_lineas_registros( $this->transaccion, $motivos ) );

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'ventas.cotizaciones.create', $tabla );
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

        $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);

        // 2do. Crear documento de Ventas
        CotizacionController::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        return redirect('vtas_cotizacion/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }

    public function show( $id )
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $documento_vista = $this->generar_documento_vista( $id, 'documento_vista' );

        $id_transaccion = $this->transaccion->id;
        $doc_encabezado = $this->doc_encabezado;

        $registros_contabilidad = [];

        $empresa = $this->empresa;

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );
        
        return view( 'ventas.cotizaciones.show', compact( 'id', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan','doc_encabezado','registros_contabilidad','empresa') );
    }


    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public static function crear_registros_documento( Request $request, $doc_encabezado, array $lineas_registros )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);
        for ($i=0; $i < $cantidad_registros; $i++) 
        {

            $linea_datos = [ 'vtas_motivo_id' => (int)$lineas_registros[$i]->inv_motivo_id ] +
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
                            [ 'estado' => 'Activo' ];


            VtasDocRegistro::create( 
                                    $datos + 
                                    [ 'vtas_doc_encabezado_id' => $doc_encabezado->id ] +
                                    $linea_datos
                                );

            $total_documento += (float)$lineas_registros[$i]->precio_total;

        } // Fin por cada registro

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->save();

    }

    /*
        Imprimir
    */
    public function imprimir( $id )
    {
        $documento_vista = $this->generar_documento_vista( $id, 'documento_imprimir' );

        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja = array(0,0,50,800);//'A4';

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
        $this->set_variables_globales();
        
        $documento_vista = $this->generar_documento_vista( $id, 'documento_imprimir' );

        $tercero = Tercero::find( $this->doc_encabezado->core_tercero_id );

        $asunto = $this->doc_encabezado->documento_transaccion_descripcion.' No. '.$this->doc_encabezado->documento_transaccion_prefijo_consecutivo;

        $cuerpo_mensaje = 'Saludos, <br/> Le hacemos llegar su '. $asunto;

        $vec = EmailController::enviar_por_email_documento( $this->empresa->descripcion, $tercero->email, $asunto, $cuerpo_mensaje, $documento_vista );

        return redirect( 'vtas_cotizacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with( $vec['tipo_mensaje'], $vec['texto_mensaje'] );
    }


    /*
        Generar la vista para los métodos show(), imprimir() o enviar_por_email()
    */
    public function generar_documento_vista( $id, $nombre_vista )
    {
        $this->doc_encabezado = VtasDocEncabezado::get_registro_impresion( $id );
        
        $doc_registros = VtasDocRegistro::get_registros_impresion( $this->doc_encabezado->id );

        $this->empresa = Empresa::find( $this->doc_encabezado->core_empresa_id );

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id',$this->doc_encabezado->core_tipo_doc_app_id)->where('estado','Activo')->get()->first();

        $doc_encabezado = $this->doc_encabezado;
        $empresa = $this->empresa;

        return View::make( 'ventas.cotizaciones.'.$nombre_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion' ) )->render();
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
        //dd( $doc_registros );
        $lineas_documento = View::make( 'ventas.cotizaciones.lineas_documento', compact('doc_registros') )->render();

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

        $motivos = ['10-salida'=>'Ventas POS'];

        $fila_controles_formulario = VtasTransaccion::get_fila_controles( $this->transaccion, $motivos );

        return view( 'ventas.cotizaciones.edit', compact( 'form_create', 'miga_pan', 'registro', 'archivo_js', 'lineas_documento', 'linea_num', 'mensaje_duplicado', 'doc_encabezado', 'fila_controles_formulario') );
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


    //     A L M A C E N A R  LA MODIFICACION DE UN REGISTRO
    public function update(Request $request, $id)
    {
        $modelo = Modelo::find( $request->url_id_modelo );

        $registro_encabezado_doc = app( $modelo->name_space )->find($id);

        // Borrar registros viejos del documento
        VtasDocRegistro::where( 'vtas_doc_encabezado_id', $id )->delete();

        $request['core_tipo_transaccion_id'] = $registro_encabezado_doc->core_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $registro_encabezado_doc->core_tipo_doc_app_id;
        $request['consecutivo'] = $registro_encabezado_doc->consecutivo;
        
        CotizacionController::crear_registros_documento( $request, $registro_encabezado_doc, json_decode($request->lineas_registros) );

        $registro_encabezado_doc->fill( $request->all() );
        $registro_encabezado_doc->save();

        return redirect( 'vtas_cotizacion/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
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

    /*
        Proceso de eliminar COTIZACION
        Se eliminan los registros de:
            - se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public static function anular_cotizacion($id)
    {        
        $cotizacion = VtasDocEncabezado::find( $id );

        VtasDocRegistro::where('vtas_doc_encabezado_id',$cotizacion->id)->update(['estado'=>'Anulado']);

        $cotizacion->update(['estado'=>'Anulado']);

        return redirect( 'vtas_cotizacion/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','Cotización ANULADA correctamente.');
        
    }



}