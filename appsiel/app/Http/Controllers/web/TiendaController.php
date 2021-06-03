<?php

namespace App\Http\Controllers\web;

use App\Core\Tercero;
use App\Inventarios\InvProducto;
use App\User;
use App\Ventas\ClienteWeb;
use App\web\Footer;
use App\web\RedesSociales;
use App\web\Tienda;
use Form;
use Input;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Inventarios\InventarioController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;

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
    public function cuenta(Request $request)
    {
        $paises = DB::table('core_paises')->get();

        $cliente = null;
        
        if (!Auth::guest()) {
            $user = Auth::user();
            $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($user->id, 'users.id');
        }

        if ($cliente == null) {            
            return redirect()->route('tienda.login');
        }
        if($request->vista == null){
            $vista = 'nav-home-tab';
        }else{
            $vista = $request->vista;
        }

        $doc_encabezados = DB::table('vtas_doc_encabezados')->where('cliente_id',$cliente->id)->where('core_tipo_transaccion_id',42)->orderBy('fecha','desc')->get();
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        return view('web.tienda.mi_cuenta.index', compact('paises', 'cliente','footer','redes','doc_encabezados','vista'));

    }

    public function newcuenta()
    {         
        $paises = DB::table('core_paises')->get(); 

        $user = Auth::user();
        $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($user->id, 'users.id');

        $doc_encabezados = DB::table('vtas_doc_encabezados')->where('cliente_id',$cliente->id)->get();
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        return view('web.tienda.mi_cuenta.index', compact('paises', 'cliente','footer','redes','doc_encabezados'));
    }

    public function login()
    {        
        $grupos = InvProducto::get_grupos_pagina_web();
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        return view('web.tienda.login', compact( 'grupos' ,'footer','redes'));
    }

    public function crearCuenta()   
    {
        $tipos = DB::table('core_tipos_docs_id')->get();
        return view('web.tienda.crearCuenta', compact('tipos'));
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
            $encabezado_doc_venta->save();

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
                            return redirect()->route('tienda.micuenta', $id)->with('flash_message', $message);
                        }
                    } else {
                        //la contraseña incorrecta lo devuelve
                        $message = 'La contraseña actual ingresada no es correcta.';
                        return redirect()->route('tienda.micuenta', $id)->with('flash_message', $message);
                    }
                } else {
                    //debe completar todos los campos
                    $message = 'Debe completar todos los campos para cambiar la contraseña';
                    return redirect()->route('tienda.micuenta', $id)->with('flash_message', $message);
                }
            }
            $result2 = $user->save();
            if ($result2) {
                $message = 'Datos modificados de forma exitosa!';
                return redirect()->route('tienda.micuenta', $id)->with('flash_message', $message);
            }
        } else {
            $message = 'Los datos no pudieron ser modificados.';
            return redirect()->route('tienda.micuenta', $id)->with('flash_message', $message);
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
        $user = Auth::user();
        $cliente = \App\Ventas\ClienteWeb::get_datos_basicos($user->id, 'users.id');
        return view('web.tienda.comprar',compact('cliente'));
    }


}
