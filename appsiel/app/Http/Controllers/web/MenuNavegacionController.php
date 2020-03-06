<?php

namespace App\Http\Controllers\web;

use App\web\Menunavegacion;
use App\web\Navegacion;
use App\web\Pagina;
use App\web\Widget;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\web\Icon;

class MenuNavegacionController extends Controller
{

    public function destroy($menu_id){

        $menu = Menunavegacion::find($menu_id);
        $message = "";

        if($menu){
            DB::delete('delete from pw_menunavegacion where parent_id = ?',[$menu->id]);

            $menu->delete();

            $message = 'El item selecionado fue eliminado de forma exitosa.';
            return redirect()->back()->with('flash_message',$message);
        }

        $message = 'El item selecionado no se encuentra en nuestros registros, por favor verifique y vuelva a intetar.';
        return redirect()->back()->with('flash_message',$message);

    }

    public function edit($menu){

       $menu = Menunavegacion::find($menu);
       $paginas = Pagina::all();
       $iconos = Icon::all();

       if($menu){

           $miga_pan = [
               [
                   'url' => 'pagina_web'.'?id='. Input::get('id'),
                   'etiqueta' => 'Web'
               ],
               [
                   'url' => 'navegacion/create?id='. Input::get('id'),
                   'etiqueta' => 'Navegación'
               ],[
                   'url' => 'NO',
                   'etiqueta' => 'Editando Item'
               ]
           ];

           $variables_url = '?id='.Input::get('id');
           return view('web.navegacion.edit',compact('miga_pan','variables_url','menu','paginas','iconos'));

       }else {

           $message = 'El item selecionado no se encuentra en nuestros registros, por favor verifique y vuelva a intetar.';
           return redirect()->back()->with('flash_message',$message);

       }

    }

    public function update(Request $request, $id){

        $menu = Menunavegacion::find($id);

        $this->validate($request,[
            'titulo' => 'required|string',
            'descripcion' => 'required|string',
        ]);

        $menu->fill($request->all());

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
                $menu->enlace = url('/'. $widget->pagina->slug.'#'.$widget->seccion->nombre);
            }
        }else {
            $menu->enlace =  $request->url;
        }

        $flag = $menu->save();

        if($flag){
            $message = 'item almacenada correctamente';
            $variables_url = '?id='.Input::get('id');
            return redirect()->back()->withInput($request->all())->with('flash_message',$message);
        }else {
            $message = 'Error inesperado, por favor intente nuevamente mas tarde';
            return redirect()->back()
                ->withInput($request->all())
                ->with('mensaje_error',$message);
        }

    }

    public function show($menu){

      $menu =  Menunavegacion::find($menu);

      if($menu){



      }else {
          $message = 'El registro que intenta observar no se encuentra en nuestros registros, por favor verifique e intente nuevamente.';
          return redirect()->back()
              ->with('mensaje_error',$message);
      }

    }


}
