<?php

namespace App\Http\Controllers\web;

use App\Core\Tercero;
use App\Http\Controllers\Salud\ResultadoExamenMedicoController;
use App\Inventarios\InvProducto;
use App\User;
use App\Ventas\ClienteWeb;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\ListaPrecioDetalle;
use App\web\Tienda;
use Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
    public function cuenta()
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

        return view('web.tienda.cuenta', compact('paises', 'cliente'));

    }

    public function login()
    {
        $grupos = InvProducto::get_grupos_pagina_web();
        return view('web.tienda.login', compact( 'grupos' ) );
    }

    public function crearCuenta()
    {
        $tipos = DB::table('core_tipos_docs_id')->get();
        return view('web.tienda.crearCuenta', compact('tipos'));
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
}
