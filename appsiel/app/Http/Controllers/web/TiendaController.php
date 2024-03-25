<?php

namespace App\Http\Controllers\web;

use App\Core\Tercero;
use App\Core\Empresa;
use App\Inventarios\InvProducto;
use App\User;
use App\Ventas\ClienteWeb;
use App\web\Footer;
use App\web\RedesSociales;
use App\web\Tienda;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventarios\InventarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;

class TiendaController extends Controller
{
    /*
    * almacena las configuraciones generales de la tienda
    *
    * @param Reques $request
    * @return Response
    */
    public function store(Request $request)
    {
        $tienda = new Tienda($request->all());
        $tienda->pais = DB::table('core_paises')->find($request->pais)->descripcion;
        $tienda->ciudad = DB::table('core_ciudades')->find($request->ciudad)->descripcion;
        $result = $tienda->save();
        if ($result) {
            $message = "Las configuraciones generales del de la tienda se guardaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = "Las configuraciones generales no se guardaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /* modifica los datos de la tienda
     *
     * @param Request $request
     * @return Response
     */
    public function generalUpdated(Request $request, $id)
    {
        $tienda = Tienda::find($id);
        foreach ($tienda->attributesToArray() as $key => $value) {
            if (isset($request->$key)) {
                if ($key == 'pais') {
                    $tienda->pais = DB::table('core_paises')->find($request->$key)->descripcion;
                } elseif ($key == 'ciudad') {
                    $tienda->$key = DB::table('core_ciudades')->find($request->$key)->descripcion;
                } else {
                    $tienda->$key = $request->$key;
                }
            }
        }
        $result = $tienda->save();
        if ($result) {
            $message = "Las configuraciones generales del de la tienda se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = "Las configuraciones generales no se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /*
     * modifica las configuraciones de productos
     *
     * @param Reques $request, $tienda_id
     * @return Response
     */
    public function productoUpdated(Request $request, $id)
    {
        $tienda = Tienda::find($id);
        foreach ($tienda->attributesToArray() as $key => $value) {
            if (isset($request->$key)) {
                $tienda->$key = $request->$key;
            }
        }
        $result = $tienda->save();
        if ($result) {
            $message = "Las configuraciones de procutos del de la tienda se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = "Las configuraciones de productos no se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /* modifica los las configuraciones de inventario en la tienda
     *
     * @param Request $request, App\Tienda $id
     * @return Response
     */
    public function inventarioUpdated(Request $request, $id)
    {
        $tienda = Tienda::find($id);
        if (isset($request->aviso_poca_exitencia)) {
            $tienda->aviso_poca_exitencia = 'SI';
        } else {
            $tienda->aviso_poca_exitencia = 'NO';
        }
        if (isset($request->aviso_inventario_agotado)) {
            $tienda->aviso_inventario_agotado = 'SI';
        } else {
            $tienda->aviso_inventario_agotado = 'NO';
        }
        if (isset($request->visibilidad_inv_agotado)) {
            $tienda->visibilidad_inv_agotado = 'SI';
        } else {
            $tienda->visibilidad_inv_agotado = 'NO';
        }
        $tienda->email_destinatario = $request->email_destinatario;
        $tienda->umbral_inventario_agotado = $request->umbral_inventario_agotado;
        $tienda->umbral_existencia = $request->umbral_existencia;
        $tienda->mostrar_inventario = $request->mostrar_inventario;
        $result = $tienda->save();
        if ($result) {
            $message = "Las configuraciones de inventario de la tienda se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = "Las configuraciones de inventario no se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    /* modifica los terminos y condiciones de la pagina
     *
     * @param Request $request, App\Tienda $id
     * @return Response
     */
    public function terminos(Request $request, $id)
    {
        $tienda = Tienda::find($id);
        $tienda->terminos_condiciones = $request->terminos_condiciones;
        $result = $tienda->save();
        if ($result) {
            $message = "Los Terminos y Condiciones de la tienda se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = "Los Terminnos y Condiciones no se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }
    }

    public function getCiudades($id)
    {
        //$pais = DB::table('core_paises')->find($id);
        $deptos = DB::table('core_departamentos')->where('codigo_pais', $id)->get();
        $response = null;
        if (count($deptos) > 0) {
            foreach ($deptos as $item) {
                $ciud = DB::table('core_ciudades')->where('core_departamento_id', $item->id)->get();
                if (count($ciud) > 0) {
                    foreach ($ciud as $value) {
                        $obj['id'] = $value->id;
                        $obj['value'] = $value->descripcion;
                        $response[] = $obj;
                    }
                }
            }
            $arreglo = $this->orderMultiDimensionalArray($response, 'value', false);
            if ($response != null) {
                return json_encode($arreglo);
            } else {
                return "null";
            }
        } else {
            return "null";
        }
    }

    function orderMultiDimensionalArray($toOrderArray, $field, $inverse = false)
    {
        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
            $position[$key] = $row[$field];
            $newRow[$key] = $row;
        }
        if ($inverse) {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    /*
     * Muestra el panel de la cuenta del cliente en la parte publica
     * @param un $id usuario logueado
     */
    public function cuenta($vista = "nav-home-tab")
    {
        $paises = DB::table('core_paises')->get();

        $cliente = null;
        //dd($vista);
        if (!Auth::guest()) {
            $user = Auth::user();
            $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($user->id, 'users.id');
        }

        if ($cliente == null) {            
            return redirect()->route('tienda.login');
        }
        
        $pedido_pendiente_id = 0;     

        $doc_encabezados = DB::table('vtas_doc_encabezados')->where('cliente_id',$cliente->id)->where('core_tipo_transaccion_id',42)->orderBy('fecha','desc')->get();
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        return view('web.tienda.mi_cuenta.index', compact('paises', 'cliente','footer','redes','doc_encabezados','vista', 'pedido_pendiente_id'));

    }

    public function newcuenta()
    {         
        $paises = DB::table('core_paises')->get(); 

        $user = Auth::user();
        $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($user->id, 'users.id');

        $doc_encabezados = VtasDocEncabezado::where('core_tipo_transaccion_id',42)
                                ->orderBy('fecha','desc')
                                ->get();
        
        $pedido_pendiente = $doc_encabezados->where('estado','Pendiensste')->all();

        $pedido_pendiente_id = 0;
        if ( !empty($pedido_pendiente) ) {
            $pedido_pendiente_id = $pedido_pendiente[0]->id;
        }
        
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        return view('web.tienda.mi_cuenta.index', compact('paises', 'cliente','footer','redes', 'doc_encabezados', 'pedido_pendiente_id'));
    }

    public function login()
    {        
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();

        $cliente = null;

        if (!Auth::guest()) {
            $user = Auth::user();
            $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($user->id, 'users.id');
        }

        if ($cliente == null) {            
            return view('web.tienda.login', compact('footer','redes'));
        }else{
            return redirect()->route('tienda.micuenta');
        }
        
    }

    public function crearCuenta_parte1()   
    {
        $tipos = DB::table('core_tipos_docs_id')->get();
        return view('web.tienda.crearCuenta_parte1', compact('tipos'));
    }

    public function crearCuenta_parte2()   
    {
        dd('hi');
        $tipos = DB::table('core_tipos_docs_id')->get();
        return view('web.tienda.crearCuenta_parte1', compact('tipos'));
    }

    public function detallepedido($pedido_id){
        if(Auth::user()){
            $doc_encabezado = VtasDocEncabezado::get_registro_impresion($pedido_id);
            $doc_registros = VtasDocRegistro::get_registros_impresion($pedido_id); 
            $user = Auth::user();
            $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($user->id, 'users.id');
            return  view('web.tienda.detallepedido', compact('doc_encabezado', 'doc_registros','cliente'));    
        }
        
    }

    public function crear_factura_desde_pasarela_de_pago(Request $request)
    {     
        $data = $request->data['transaction'];
        $cheksum_request = $request->signature['checksum'];
        $cheksum_generated = hash ("sha256",$data['id'].$data['status'].$data['amount_in_cents'].$request->timestamp.env('APP_TIENDA','none'));

        if($data['status'] == 'APPROVED' && $cheksum_request == $cheksum_generated){
            $encabezado_doc_venta = VtasDocEncabezado::find( (int)$data['reference'] );        

            // este metodo crear_remision_desde_doc_venta() debe estar en una clase Model

            //crear_remision_desde_doc_venta
            $datos_remision = $encabezado_doc_venta->toArray();
            $datos_remision['fecha'] = date('Y-m-d');
            $datos_remision['inv_bodega_id'] = $encabezado_doc_venta->cliente->inv_bodega_id;

            $descripcion = 'Generada desde ' . $encabezado_doc_venta->tipo_transaccion->descripcion . ' ' . $encabezado_doc_venta->tipo_documento_app->prefijo . ' ' . $encabezado_doc_venta->consecutivo;
            $datos_remision['descripcion'] = $descripcion;

            $datos_remision['vtas_doc_encabezado_origen_id'] = $encabezado_doc_venta->id;
            $lineas_registros = VtasDocRegistro::where( 'vtas_doc_encabezado_id', $encabezado_doc_venta->id )->get();

            $doc_remision = InventarioController::crear_encabezado_remision_ventas($datos_remision, 'Pendiente');
            
            InventarioController::crear_registros_remision_ventas( $doc_remision, $lineas_registros);

            InventarioController::contabilizar_documento_inventario( $doc_remision->id, '' );

            $this->actualizar_cantidades_pendientes( $lineas_registros );
            //crear_remision_desde_doc_venta
            //crear_remision_desde_doc_venta
            $modelo_id = 139;

            $descripcion = 'Generada desde ' . $encabezado_doc_venta->tipo_transaccion->descripcion . ' ' . $encabezado_doc_venta->tipo_documento_app->prefijo . ' ' . $encabezado_doc_venta->consecutivo;

            if(str_contains($data['redirect_url'],'domicil')){
                $tercero = Tercero::find($encabezado_doc_venta->core_tercero_id);
                $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($tercero->user_id, 'users.id');
                $direccion_por_defecto = $cliente->direccion_por_defecto();

                $descripcion .= "<address>
                                    <b>Domicilio: $direccion_por_defecto->nombre_contacto</b><br>
                                    $direccion_por_defecto->direccion1, $direccion_por_defecto->barrio<br>".
                                    $direccion_por_defecto->ciudad->descripcion.", ".$direccion_por_defecto->ciudad->departamento->descripcion.", $direccion_por_defecto->codigo_postal<br>
                                    Tel.: $direccion_por_defecto->telefono1 <br></address>";
            }else{
                $empresa = Empresa::all()->first();
                $descripcion .= "<address>
                                    <b>Recoger en: $empresa->descripcion</b><br>
                                    $empresa->direccion1, $empresa->barrio<br>".
                                    $empresa->ciudad->descripcion.", ".$empresa->ciudad->departamento->descripcion.", $empresa->codigo_postal<br>
                                    Tel.: $empresa->telefono1 <br></address>";

            }

            $nueva_factura = $encabezado_doc_venta->clonar_encabezado(date('Y-m-d'), (int)config('ventas.factura_ventas_tipo_transaccion_id'), (int)config('ventas.factura_ventas_tipo_doc_app_id'), $descripcion, $modelo_id );
            
            $nueva_factura->forma_pago = 'contado';            

            $nueva_factura->estado = 'Activo';
            $nueva_factura->save();
            
            $encabezado_doc_venta->clonar_lineas_registros( $nueva_factura->id );

            $nueva_factura->crear_movimiento_ventas();

                // Contabilizar
            $nueva_factura->contabilizar_movimiento_debito();
            $nueva_factura->contabilizar_movimiento_credito();

            $nueva_factura->crear_registro_pago();

            //crear_remision_desde_doc_venta
            $nueva_factura->remision_doc_encabezado_id = $doc_remision->id;
            $nueva_factura->ventas_doc_relacionado_id = $encabezado_doc_venta->id;
            $nueva_factura->save();

            $doc_remision->estado = 'Facturada';
            $doc_remision->save();

            $encabezado_doc_venta->estado = 'Cumplido';
            $encabezado_doc_venta->descripcion = $descripcion;
            $encabezado_doc_venta->save();

            //$this->enviar_facturaweb_email($nueva_factura->id,str_contains($data['redirect_url'],'domicil'));           

            return response()->json([
                'status'=> '200',
                'msg'=>'Transacción completada con exito'
            ]);
        }else{
            return response()->json([
                'status'=> '400',
                'msg'=>'Transacción fallida'
            ]);
        }
        
    }

    public function actualizar_cantidades_pendientes( $lineas_registros )
    {
        foreach( $lineas_registros AS $linea )
        {
            $linea->cantidad_pendiente = $linea->cantidad_pendiente - $linea->cantidad;
            $linea->save();
        }
    }

    public function sumar_dias_calendario_a_fecha( string $fecha, int $cantidad_dias )
    {
        $fecha_aux = Carbon::createFromFormat('Y-m-d', $fecha );

        return $fecha_aux->addDays( $cantidad_dias )->format('Y-m-d');
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

    public function get_documento_transaccion_prefijo_consecutivo( $doc_encabezado )
    {
        if( (int)config('ventas.longitud_consecutivo_factura') == 0 )
        {
            return $doc_encabezado->documento_transaccion_prefijo_consecutivo;
        }

        $consecutivo = $doc_encabezado->consecutivo;
        $largo = (int)config('ventas.longitud_consecutivo_factura') - strlen($doc_encabezado->consecutivo);
        for ($i=0; $i < $largo; $i++)
        { 
            $consecutivo = '0' . $consecutivo;
        }

        return $doc_encabezado->tipo_documento_app->prefijo . ' ' . $consecutivo;
    }

    public function enviar_pedidoweb_email($id){

        $doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);

        $documento_vista = $this->generar_documento_vista_pedido($id);

        $tercero = Tercero::find($doc_encabezado->core_tercero_id);

        $asunto = $doc_encabezado->documento_transaccion_descripcion . ' No. ' . $doc_encabezado->documento_transaccion_prefijo_consecutivo;
        $empresa = Empresa::all()->first();
        $descripcion =  $empresa->descripcion;
        $cuerpo_mensaje = "Hola <strong>$tercero->nombre1 $tercero->nombre2</strong>,"
                          ."Gracias por su compra en <strong> $descripcion. </strong> </br>"
                          ."Le hacemos llegar su Pedido de Ventas $doc_encabezado->documento_transaccion_prefijo_consecutivo. Forma de pago en Efectivo. <br><br>"
                          ."Estamos alistando su compra. Los esperemos en la dirección. $empresa->direccion1, $empresa->barrio <br>"
                          ."¡No olvide llevar este comprobante!<br><br>"
                          ."Si tiene alguna duda o sugerencia nos puede llamar y escribirnos al $empresa->telefono1 o $empresa->email.<br><br>
                          Por favor no responda este mensaje, fue generado automáticamente.
                          ";

        
        $email_interno = 'info@appsiel.com.co';//.substr( url('/'), 7);

        //$empresa = Empresa::find( Auth::user()->empresa_id );

        $vec = \App\Http\Controllers\Sistema\EmailController::enviar_por_email_documento($empresa->descripcion, $tercero->email . ',' . $email_interno, $asunto, $cuerpo_mensaje, $documento_vista);
        return redirect()->route('ecommerce/public/detallepedido'.'/'.$id.'?efectivo=true');
    }

    public function enviar_facturaweb_email( $id, $compra_domi)
    {
        $empresa = Empresa::all()->first();
        $descripcion =  $empresa->descripcion;
        $doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);

        $tercero = Tercero::find( $doc_encabezado->core_tercero_id );   
        
        $documento_vista = $this->generar_documento_vista_factura( $id, 'ventas.formatos_impresion.estandar' );        

        $asunto = $doc_encabezado->documento_transaccion_descripcion.' No. '.$doc_encabezado->documento_transaccion_prefijo_consecutivo;

        if($compra_domi){
            $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($tercero->user_id, 'users.id');
            $domicilio = $cliente->direccion_por_defecto();
            
            $cuerpo_mensaje = "Hola <strong>$tercero->nombre1 $tercero->nombre2</strong>,"
                          ."Gracias por su compra en <strong> $descripcion. </strong> </br>"
                          ."Le hacemos llegar su Factura de Ventas $doc_encabezado->documento_transaccion_prefijo_consecutivo. Forma de pago en Wompi. <br><br>"
                          ."Estamos alistando su compra para ser enviada a la dirección $domicilio->direccion1, $domicilio->barrio <br>"
                          ."Si tiene alguna duda o sugerencia nos puede llamar y escribirnos al $domicilio->telefono1 o $tercero->email.<br><br>
                          Por favor no responda este mensaje, fue generado automáticamente.
                          ";
        }else{
            $cuerpo_mensaje = "Hola <strong>$tercero->nombre1 $tercero->nombre2</strong>,"
                          ."Gracias por su compra en <strong> $descripcion. </strong> </br>"
                          ."Le hacemos llegar su Factura de Ventas $doc_encabezado->documento_transaccion_prefijo_consecutivo. Forma de pago en Wompi. <br><br>"
                          ."Estamos alistando su compra. Lo esperamos en la dirección $empresa->direccion1, $empresa->barrio <br>"
                          ."¡No olvide llevar este comprobante!<br><br>"
                          ."Si tiene alguna duda o sugerencia nos puede llamar y escribirnos al $empresa->telefono1 o $empresa->email.<br><br>
                          Por favor no responda este mensaje, fue generado automáticamente.
                          ";
        }

        

        $email_destino = $tercero->email;
        if ( $doc_encabezado->contacto_cliente_id != 0 )
        {
            $email_destino = $doc_encabezado->contacto_cliente->tercero->email;
        }

        $vec = \App\Http\Controllers\Sistema\EmailController::enviar_por_email_documento( $empresa->descripcion, $email_destino, $asunto, $cuerpo_mensaje, $documento_vista );

    }

    public function imprimir_pedido($id)
    {
        $documento_vista = $this->generar_documento_vista_pedido($id);

        $doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);

        // Se prepara el PDF
        $orientacion = 'portrait';
        $tam_hoja = array(0, 0, 50, 800); //'A4';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($documento_vista); //->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream($doc_encabezado->documento_transaccion_descripcion . ' - ' . $doc_encabezado->documento_transaccion_prefijo_consecutivo . '.pdf');
    }

    public function imprimir_factura( $id )
    {
        $documento_vista = $this->generar_documento_vista_factura($id);

        $doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);

        // Se prepara el PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
        
    }

    public function generar_documento_vista_factura($id)
    {
        //set_variables_globales
        $empresa = Empresa::all()->first();
        $app = \App\Sistema\Aplicacion::find( '13' );
        $modelo = \App\Sistema\Modelo::find( '139' );
        $transaccion = \App\Sistema\TipoTransaccion::find( '23' );
        //set_variables_globales
        
        $doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);
        $doc_registros = VtasDocRegistro::get_registros_impresion($id); 

        $doc_encabezado->documento_transaccion_prefijo_consecutivo = $this->get_documento_transaccion_prefijo_consecutivo( $doc_encabezado );

        $resolucion = \App\Ventas\ResolucionFacturacion::where('tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)->where('estado','Activo')->get()->last();

        $etiquetas = $this->get_etiquetas();

        $abonos = \App\CxC\CxcAbono::get_abonos_documento( $doc_encabezado );

        $docs_relacionados = VtasDocEncabezado::get_documentos_relacionados( $doc_encabezado );

        return View::make( 'ventas.formatos_impresion.estandar', compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas', 'abonos', 'docs_relacionados' ) )->render();
    }

    public function generar_documento_vista_pedido($id)
    {

        $doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);

        $doc_registros = VtasDocRegistro::get_registros_impresion($doc_encabezado->id);

        $empresa = Empresa::find($doc_encabezado->core_empresa_id);

        $contacto = (object)[ 'descripcion'=> $doc_encabezado->tercero->descripcion, 'telefono1' => $doc_encabezado->tercero->telefono1, 'email' => $doc_encabezado->tercero->email ];
        if ( $doc_encabezado->contacto_cliente_id != 0 )
        {
            $contacto = $doc_encabezado->contacto_cliente->tercero;
        }

        $resolucion = '';

        $empresa = $empresa;

        $etiquetas = $this->get_etiquetas();

        return View::make('ventas.pedidos.formatos_impresion.estandar', compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion','etiquetas','contacto'))->render();
    }

    /*
     * Edita la informacion general de la cuenta del clienteweb
     * @param $reques Request, $id Clienteweb
     */
    public function informacionUpdate(Request $request, $id)
    {
        $cliente = ClienteWeb::find($id);
        $tercero = Tercero::find($cliente->core_tercero_id);
        $user = User::find($tercero->user_id);
        //dd([$request->all(),$cliente,$tercero,$user]);
        foreach ($tercero->attributesToArray() as $key => $value) {
            if (isset($request->$key)) {
                $tercero->$key = $request->$key;
            }
        }
        $tercero->descripcion = $request->nombre1 . " " . $request->otros_nombres . " " . $request->apellido1 . " " . $request->apellido2;
        $result = $tercero->save();
        if ($result) {
            $user->email = $tercero->email;
            $user->name = $request->nombre1 . " " . $request->otros_nombres . " " . $request->apellido1 . " " . $request->apellido2;
            if (isset($request->change_password)) {
                if ($request->password != null && $request->current_password != null && $request->confirmation != null) {
                    if (Hash::check($request->current_password, $user->password)) {
                        if ($request->password === $request->confirmation) {
                            $user->password = Hash::make($request->password);
                        } else {
                            //la nueva contraseña no coincide ;
                            $message = 'Las contraseñas no coinciden.';
                            return redirect()->route('tienda.micuenta', 'nav-infor-tab')->with('mensaje_error', $message);
                        }
                    } else {
                        //la contraseña incorrecta lo devuelve
                        $message = 'La contraseña actual ingresada no es correcta.';
                        return redirect()->route('tienda.micuenta', 'nav-infor-tab')->with('mensaje_error', $message);
                    }
                } else {
                    //debe completar todos los campos
                    $message = 'Debe completar todos los campos obligatorios para cambiar la contraseña';
                    return redirect()->route('tienda.micuenta', 'nav-infor-tab')->with('mensaje_error', $message);
                }
            }
            $result2 = $user->save();
            if ($result2) {
                $message = 'Datos modificados de forma exitosa!';
                return redirect()->route('tienda.micuenta', 'nav-infor-tab')->with('flash_message', $message);
            }
        } else {
            $message = 'Los datos no pudieron ser modificados.';
            return redirect()->route('tienda.micuenta', 'nav-infor-tab')->with('mensaje_error', $message);
        }
    }

    public function filtroCategoria($id)
    {
        $items = InvProducto::get_datos_pagina_web($id, 'Activo', 100);
        //$grupos = InvProducto::get_grupos_pagina_web();

        $texto = '';

        return view('web.tienda.lista_productos',compact('items','texto'));
    }

    public function busqueda(Request $request)
    {
        if ( $request->categoria == 0 )
        {
            $grupo_inventario_id = '';
        } else {
            $grupo_inventario_id = $request->categoria;
        }

        $items = InvProducto::get_datos_pagina_web( $grupo_inventario_id, 'Activo',9,$request->search);
        //$grupos = InvProducto::get_grupos_pagina_web();

        $texto = $request->search;

        //dd( $items->toArray()['data'] );
        return view('web.tienda.lista_productos',compact('items', 'texto'));
    }

    function comprar(){

        if(!Auth::check()){
            return redirect(url('ecommerce/public/signIn'))->with('flash_message', '<span style="font-size: 1.5em; color:#F98200;"><i class="fa fa-smile-o"></i></span> Vas por buen camino. Ahora regálanos tus datos para continuar.');
        }

        return view('web.tienda.comprar');
    }


}
