<?php

namespace App\Http\Controllers\web;

use App\web\Icon;
use App\web\RedesSociales;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class RedesSocialesController extends Controller
{

    public function index(){

        $miga_pan =   [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Redes Sociales'
            ]
        ];
        $variables_url = '?id='.Input::get('id');
        $redes = RedesSociales::all();
        return view('web.redesSociales.admin',compact('miga_pan','variables_url','redes'));
    }

    public function create(){

        $miga_pan =   [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'sociales'.'?id='. Input::get('id'),
                'etiqueta' => 'Redes Sociales'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'añadir'
            ]
        ];
        $variables_url = '?id='.Input::get('id');
        $iconos = Icon::all();
        return view('web.redesSociales.create',compact('miga_pan','variables_url','iconos'));

    }

    public function store(Request $request){
       $redSocial  = new RedesSociales($request->all());
        $flag = $redSocial->save();

        if($flag){
             $variables_url = '?id='.Input::get('id');
             return redirect(url('sociales').$variables_url)->with('flash_message','item almacenado correctamente');
        }

    }

    public function edit($id){

        $red = RedesSociales::find($id);

        if($red){
            $miga_pan =   [
                [
                    'url' => 'pagina_web'.'?id='. Input::get('id'),
                    'etiqueta' => 'Web'
                ],
                [
                    'url' => 'sociales'.'?id='. Input::get('id'),
                    'etiqueta' => 'Redes Sociales'
                ],
                [
                    'url' => 'NO',
                    'etiqueta' => 'Editando Red Social'
                ]
            ];
            $variables_url = '?id='.Input::get('id');
            $iconos = Icon::all();
            return view('web.redesSociales.edit',compact('miga_pan','variables_url','red','iconos'));
        }else {
            $message = 'El registro que intenta editar no se encuentra registrado, por favor verifique e intente nuevamente.';
            return redirect()->back()
                ->with('mensaje_error',$message);
        }


    }

    public function update(Request $request, $id){

        $redSocial = RedesSociales::find($id);

        if($redSocial){
            $redSocial->fill($request->all());
            $flag = $redSocial->save();
            if($flag){
                $variables_url = $request->variables_url;
                return redirect(url('sociales').$variables_url)->with('flash_message','item almacenado correctamente');
            }else{
                $message = 'Error intente nuevamente más tarde.';
                return redirect()->back()
                    ->with('mensaje_error',$message);
            }
        }else {
            $message = 'El registro que intenta editar no se encuentra registrado, por favor verifique e intente nuevamente.';
            return redirect()->back()
                ->with('mensaje_error',$message);
        }
    }

    public function destroy($id){

        $red = RedesSociales::find($id);

       if($red){

           $flag =  $red->delete();

           if($flag){

               return response()->json([
                   'status' => 'ok'
               ]);

           }else {

               return response()->json([
                   'status' => 'error',
                   'message' => 'Error inesperado, por favor intentelo más tarde.'
               ]);

           }

       }else {

           return response()->json([
               'status' => 'error',
               'message' => 'El registro que intenta eliminar no existe, por favor verifique.'
           ]);

       }

    }

}
