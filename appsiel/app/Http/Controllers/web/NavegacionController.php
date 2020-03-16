<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\web\Icon;
use App\web\Menunavegacion;
use App\web\Navegacion;
use App\web\Pagina;
use App\web\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;


class NavegacionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(){
        $miga_pan = self::migapan();
        $paginas = Pagina::all();
        $nav = Navegacion::all()->first();
        $variables_url = '?id='.Input::get('id');
        $iconos = Icon::all();
        return view('web.navegacion.navegacion',compact('miga_pan','nav','paginas','variables_url','iconos'));
    }

    public function migapan() {
       return [
           [
               'url' => 'pagina_web'.'?id='. Input::get('id'),
               'etiqueta' => 'Web'
           ],
           [
               'url' => 'NO',
               'etiqueta' => 'Navegación'
           ]
       ];
    }

    public function store(Request $request){

        $this->validate($request,[
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
        ]);

       $menu = new Menunavegacion($request->all());

        if($request->hasFile('icono')){

            $file = $request->file('icono');
            $name = time().$file->getClientOriginalName();

            $filename = storage_path("app/public/iconos/").$name;
            $flag = file_put_contents($filename,file_get_contents($file->getRealPath()),LOCK_EX);

            if($flag !== false){
                $menu->fill(['icono' =>$filename]);
            }else {
                $message = 'Error inesperado al intentar guardar el icono, por favor intente nuevamente mas tarde';
                return redirect()->back()->withInput($request->input())
                    ->with('mensaje_error',$message);
            }

        }

       if($request->tipo_enlace == 'pagina' ){
           if($request->seccion == 'principio' ){
             $pagina = Pagina::find($request->pagina);
             $menu->enlace = url('/'.$pagina->slug);
           }else {
               $widget = Widget::find($request->seccion);
               $menu->enlace = url('/'. $widget->pagina->slug.'#'.str_slug($widget->seccion->nombre));
           }
       }else {
           $menu->enlace =  $request->url;
       }

       $nav = Navegacion::all()->first();
       $menu->navegacion_id = $nav->id;

       $flag = $menu->save();

       if($flag){
           $message = 'item almacenado correctamente';
           $variables_url = '?id='.Input::get('id');
           return redirect(url('navegacion/create').$variables_url)->with('flash_message',$message);
       }else {
           $message = 'Error inesperado, por favor intente nuevamente mas tarde';
           return redirect()->back()
                       ->withInput($request->all())
                       ->with('mensaje_error',$message);
       }

    }

    public function update(Request $request, $id){

        $nav = Navegacion::find($id);

        if($nav){
            $nav->fill($request->all());
            if($request->hasFile('logo')){

                $file = $request->file('logo');
                $name = time().str_slug($file->getClientOriginalName());

                $filename = 'img/logos/'.$name;
                $flag = file_put_contents($filename,file_get_contents($file->getRealPath()),LOCK_EX);

                if($flag !== false){
                    $nav->fill(['logo' =>$filename]);
                }else {
                    $message = 'Error inesperado al intentar guardar el logo, por favor intente nuevamente más tarde';
                    return redirect()->back()->withInput($request->input())
                        ->with('mensaje_error',$message);
                }

            }


            $nav->fixed = $request->fixed == 'on' ? 1 : 0;
            $flag = $nav->save();

            if($flag){
                return redirect()->back()->with('flash_message','Configuraciones Almacenadas Correctamente.');
            }else{
                return redirect()->back() ->with('mensaje_error', "Error inesperado, la Configuración no pudo ser almacenada. Intente nuevamente más tarde");
            }

        }
    }

    public function storeNav(Request $request){

        $nav =new Navegacion($request->all());

        if($nav){

            if($request->hasFile('logo')){

                $file = $request->file('logo');
                $name = time().str_slug($file->getClientOriginalName());

                $filename = 'img/logos/'.$name;
                $flag = file_put_contents($filename,file_get_contents($file->getRealPath()),LOCK_EX);

                if($flag !== false){
                    $nav->fill(['icono' =>$filename]);
                }else {
                    $message = 'Error inesperado al intentar guardar el logo, por favor intente nuevamente más tarde';
                    return redirect()->back()->withInput($request->input())
                        ->with('mensaje_error',$message);
                }

            }

            $nav->fill($request->all());
            $nav->fixed = $request->fixed == 'on' ? 1 : 0;
            $flag = $nav->save();

            if($flag){
                return redirect()->back()->with('flash_message','Configuraciones Almacenadas Correctamente.');
            }else{
                return redirect()->back() ->with('mensaje_error', "Error inesperado, la Configuración no pudo ser almacenada. Intente nuevamente más tarde");
            }

        }
    }


}
