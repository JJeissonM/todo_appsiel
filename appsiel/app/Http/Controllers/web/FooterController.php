<?php

namespace App\Http\Controllers\web;

use App\web\CategoriaFooter;
use App\web\Contactenos;
use App\web\EnlaceFooter;
use App\web\Icon;
use App\web\Pagina;
use App\web\RedesSociales;
use App\web\Widget;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\web\Footer;

class FooterController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){

        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Pie de página'
            ]
        ];

        $variables_url = '?id=' . Input::get('id');
        $footer = Footer::all()->first();
        $redes = RedesSociales::all();
        $iconos = Icon::all();
        $paginas = Pagina::all();
        $contactenos = Contactenos::all()->first();
        return view('web.footer.footer',compact('footer','variables_url','miga_pan','iconos','redes','contactenos','paginas'));
    }

    public function store(Request $request){

        $request->ubicacion =  str_replace('width="600"','width="300"',$request->ubicacion);
        $footer = new Footer($request->all());

        $footer->ubicacion = $request->ubicacion;
        $flag = $footer->save();

        if($flag){
           return redirect()->back()
               ->with('flash_message','configuración almacenada correctamente');
        }else{
            return redirect()->back()
                ->with('error_message','Error al guardar la configuración, por favor intente nuevamente más tarde.');
        }

    }

    public function update(Request $request, $id){

        $footer = Footer::find($id);

        if($footer){

            $request->ubicacion =  str_replace("width=\"600\"","width=\"300\"",$request->ubicacion);
            $footer->fill($request->all());
            $footer->ubicacion = $request->ubicacion;
            $flag = $footer->save();

            if($flag){
                return redirect()->back()
                    ->with('flash_message','configuración almacenada correctamente');
            }else{
                return redirect()->back()
                    ->with('error_message','Error al guardar la configuración, por favor intente nuevamente más tarde.');
            }

        }else{
            return redirect()->back()
                ->with('error_message','Error al guardar la configuración que intenta editar no se encuentra en nuestros registros, por favor verifique e intente nuevamente.');
        }

    }

    public function footerstoreCategoria(Request $request){

        $categoria = new CategoriaFooter($request->all());
        $flag = $categoria->save();

        if($flag){
            return redirect()->back()
                ->with('flash_message','Sección almacenada correctamente');
        }else{
            return redirect()->back()
                ->with('error_message','Error al guardar la sección, por favor intente nuevamente más tarde.');
        }

    }


    public function categorias($id){

        $categoria = CategoriaFooter::find($id);

        if($categoria){

            return response()->json([
                'status' => 'ok',
                'categoria' => $categoria,
                'enlaces' => $categoria->enlaces
            ]);

        }else {
            return response()->json([
                 'status' => 'error',
                 'message' => 'el item que intenta editar no se encuentra en nuestros registros.'
            ]);
        }

    }

    public function updateCategoria(Request $request, $id){

        $categoria = CategoriaFooter::find($id);

        if($categoria){

            $categoria->fill($request->all());
            $flag = $categoria->save();

            if($flag){
                return redirect()->back()
                    ->with('flash_message','Sección almacenada correctamente');
            }else{
                return redirect()->back()
                    ->with('error_message','Error al guardar la Sección, por favor intente nuevamente más tarde.');
            }

        }else {
            return redirect()->back()
                ->with('error_message','Error al guardar la seccion que intenta editar no se encuentra en nuestros registros, por favor verifique e intente nuevamente.');
        }

    }


    public function newEnlace(Request $request){

        $enlace = new EnlaceFooter($request->all());

        if($request->tipo_enlace == 'pagina' )
        {
            if($request->seccion == 'principio' )
            {
                $pagina = Pagina::find($request->pagina);
                $enlace->enlace = url('/'.$pagina->slug);
            }else {
                $widget = Widget::find($request->seccion);
                $enlace->enlace = url('/'. $widget->pagina->slug.'#'.$widget->seccion->nombre);
            }
        }else {
            $enlace->enlace =  $request->url;
        }

        $flag = $enlace->save();

        if($flag){
            $enlaces =  EnlaceFooter::where('categoria_id',$request->categoria_id)->get();
            return response()->json([
                'status' => 'ok',
                'message' => 'Enlace guardado correctamente.',
                'enlaces' => $enlaces
            ]);

        }else {
             return response()->json([
                 'status' => 'error',
                 'message' => 'Error al intentar guardar el enlace, por favor intente nuevamente más tarde.'
             ]);
        }

    }

    public function eliminarEnlace($id){

        $enlace = EnlaceFooter::find($id);
        $categoria =  $enlace->categoria;
        $flag =  $enlace->delete();

        if ($flag) {
            $enlaces =  $categoria->enlaces;
            return response()->json([
                'status' => 'ok',
                'enlaces' => $enlaces
            ]);

        } else {

            return response()->json([
                'status' => 'error',
                'message' => 'Error inesperado, por favor intentelo más tarde.'
            ]);

        }
    }

    public function eliminarSeccion($id){

        $categoria = CategoriaFooter::find($id);
        $flag =  $categoria->delete();

        if ($flag) {
            return response()->json([
                'status' => 'ok',
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Error inesperado, por favor intentelo más tarde.'
            ]);
        }

    }

}
