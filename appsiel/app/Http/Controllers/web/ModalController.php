<?php

namespace App\Http\Controllers\web;

use App\web\Modal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ModalController extends Controller
{
    public function store(Request $request){

        $modal = new Modal($request->all());
        $modal->tipo_recurso = 'imagen';
        $file = $request->file('path');

        if($file){
          $extension =  $file->getClientOriginalExtension();
          if($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png' || $extension == 'gif'){
              $name = time() . $file->getClientOriginalName();
              $filename = "img/" . $name;
              $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
              $modal->path = $flag ? $filename : '';
          }else{
              $message = 'La extension del archivo no es la adecuada.';
              return redirect()->back()
                     ->withInput($request->all())
                     ->with('flash_message', $message);
          }
        }

        $result = $modal->save();

        if ($result) {
            $message = 'El Modal fue creado de forma exitosa.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            Storage::delete($modal->path);
            $message = 'hubo error inesperado, por favor intentelo más tarde';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }

    public function updated(Request $request,$id){
        $modal = Modal::findOrFail($id);
        $modal->fill($request->all());
        $file = $request->file('path');

        if($file){
            $extension =  $file->getClientOriginalExtension();
            if($extension == 'jpeg' || $extension == 'jpg' || $extension == 'png' || $extension == 'gif'){
                $name = time() . $file->getClientOriginalName();
                $filename = "img/" . $name;
                $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
                $modal->path = $flag ? $filename : '';
            }else{
                $message = 'La extension del archivo no es la adecuada.';
                return redirect()->back()
                    ->withInput($request->all())
                    ->with('flash_message', $message);
            }
        }

        $result = $modal->save();

        if ($result) {
            $message = 'El Modal fue creado de forma exitosa.';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        } else {
            Storage::delete($modal->path);
            $message = 'hubo error inesperado, por favor intentelo más tarde';
            return redirect(url('seccion/' . $request->widget_id) . $request->variables_url)->with('flash_message', $message);
        }
    }
}
