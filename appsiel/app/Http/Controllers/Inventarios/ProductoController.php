<?php

namespace App\Http\Controllers\Inventarios;

use App\Inventarios\InvFichaProducto;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\ListaPrecioDetalle;
use App\web\Footer;
use App\web\RedesSociales;
use Illuminate\Http\Request;
use Input;

use App\Sistema\Modelo;
use App\Sistema\Aplicacion;

use App\Http\Controllers\Controller;
use App\Inventarios\InvProducto;
use App\Sistema\Html\MigaPan;



class ProductoController extends  Controller {

    public function create_ficha($id){
        $inv_producto =  InvProducto::findOrFail($id);
        $modelo = Modelo::find(Input::get('id_modelo'));
        $aplicacion = Aplicacion::find(Input::get('id'));
        $miga_pan = MigaPan::get_array($aplicacion, $modelo, 'ficha técnica');
        return view('inventarios.ficha',compact('inv_producto','miga_pan'));
    }

    public function  store_ficha(Request $request){

        $ficha = new InvFichaProducto($request->all());
        $ficha_copia =  InvFichaProducto::where([
            ['producto_id','=',$request->producto_id],
            ['key','=',$request->key]
        ])->first();

        if($ficha_copia){
            return redirect()->back()->with( 'flash_message','No se aceptan caracteristicas duplicadas' );
        }

        foreach ($ficha->attributesToArray() as $key => $value){
            $ficha->$key = strtoupper($value);
        }
        $result = $ficha->save();
        if($result){
           return redirect()->back()->with( 'flash_message','Registro CREADO correctamente.' );
        }else{
            return redirect()->back()->with( 'flash_message','Error al guardar el nuevo registro.' );
        }

    }

    public function delete_ficha($id){

        $ficha  = InvFichaProducto::findOrFail($id);
        $result = $ficha->delete();
        if($result){
           return redirect()->back()->with( 'flash_message','Registro ELIMINADO correctamente.' );
        }else{
            return redirect()->back()->with( 'flash_message','Error al eliminar el  registro.' );
        }

    }

    public function  detalle_producto($id){

        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        $inv_producto =  InvProducto::findOrFail($id);
        $inv_producto->precio_venta = ListaPrecioDetalle::get_precio_producto( config('pagina_web.lista_precios_id'), date('Y-m-d'), $id );

        return view('web.tienda.detalle',compact('footer','redes','inv_producto'));
    }

}