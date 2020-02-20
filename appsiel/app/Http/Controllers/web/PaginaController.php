<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\PaginaWeb\SlugController;
use App\web\Pagina;

use App\Http\Controllers\Controller;
use App\web\Seccion;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaginaController extends Controller
{

    public function index(){

        $miga_pan = [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Paginas y Secciones'
            ]
        ];

        $paginas = Pagina::all();
        $variables_url = '?id='.Input::get('id');
        return view('web.paginas.index',compact('miga_pan','paginas','variables_url'));

    }

    public function admin(){

        $miga_pan = [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'paginas?id='.Input::get('id'),
                'etiqueta' => 'Paginas y secciones'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Administacion de páginas'
            ]
        ];

        $paginas = Pagina::all();
        $variables_url = '?id='.Input::get('id');
        return view('web.paginas.admin',compact('paginas','miga_pan','variables_url'));
    }

    public function create(){

        $miga_pan = [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'paginas?id='.Input::get('id'),
                'etiqueta' => 'Paginas y secciones'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Nueva página'
            ]
        ];

        $variables_url = '?id='.Input::get('id');
        return view('web.paginas.create',compact('miga_pan','variables_url'));

    }

    public function secciones($id){

         $pagina = Pagina::find($id);
         $widgets = $pagina->widgets;
         $secciones = [];
         foreach ($widgets as $widget){
             $secciones[] = [
                 'widget_id' => $widget->id,
                 'seccion' => $widget->seccion->nombre
             ];
         }

         return response()->json(['secciones' => $secciones]);
     }

    public function store(Request $request){

        if($request->pagina_inicio){

            $principal = Pagina::where('pagina_inicio',true)->get()->first();
            if($principal){
                $principal->pagina_inicio = !$principal->pagina_inicio;
                $principal->save();
            }

        }

        $pagina = Pagina::create($request->all());
        $pagina->slug = self::generar_slug($request->titulo);
        $pagina->save();

        if($request->hasFile('favicon')){

           $file = $request->file('favicon');
           $name = time().$file->getClientOriginalName();

           $filename = storage_path("app/public/iconos/").$name;
           $flag = file_put_contents($filename,file_get_contents($file->getRealPath()),LOCK_EX);
           if($flag !== false){
                $pagina->fill(['favicon' =>$filename])->save();
           }

            $variables_url = '?id='.Input::get('id');
            return redirect('paginas'.$variables_url);

        }

     }

    public function generar_slug( $cadena )
    {
        $slug_original = str_slug( $cadena );

        $slug_nuevo = $slug_original;

        $existe = true;
        $i = 2;
        while ( $existe )
        {
            $registro = Pagina::where('slug', $slug_nuevo)->get()->first();

            if ( !is_null( $registro ) )
            {
                $slug_nuevo = $slug_original.'-'.$i;
                $i++;
            }else{
                $existe = false;
            }
        }

        return $slug_nuevo;
    }

    public function addSeccion($id){

        $miga_pan = [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'paginas?id='.Input::get('id'),
                'etiqueta' => 'Paginas y secciones'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Agregando nueva sección'
            ]
        ];

        $pagina =  $id;
        $secciones = Seccion::all();
        return view('web.paginas.secciones.addSeccion',compact('secciones','miga_pan','pagina'));

    }

}
