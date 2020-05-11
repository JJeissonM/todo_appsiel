<?php

namespace App\Http\Controllers\web;

use App\web\Tienda;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

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
        $tienda->mostrar_inventario =$request->mostrar_inventario;
        $result = $tienda->save();
        if($result){
            $message = "Las configuraciones de inventario de la tienda se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }else{
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
    public function terminos(Request $request,$id){
        $tienda=Tienda::find($id);
        $tienda->terminos_condiciones = $request->terminos_condiciones;
        $result = $tienda->save();
        if($result){
            $message = "Los Terminos y Condiciones de la tienda se modificaron correctamente";
            $variables_url = $request->variables_url;
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        }else{
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
    public function cuenta( $cliente_id = 0 )
    {
        $paises = DB::table('core_paises')->get();

        $cliente = \App\Ventas\ClienteWeb::get_datos_basicos( $cliente_id );
        
        return view( 'web.tienda.cuenta', compact('paises','cliente') );
    }

    public function  login (){
        return view('web.tienda.login');
    }

    public function crearCuenta(){
        $tipos = DB::table('core_tipos_docs_id')->get();
        return view('web.tienda.crearCuenta',compact('tipos'));
    }

}
