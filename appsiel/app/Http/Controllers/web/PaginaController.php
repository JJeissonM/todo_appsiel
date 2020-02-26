<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\web\services\FactoryCompents;
use App\web\Pagina;

use App\Http\Controllers\Controller;
use App\web\Seccion;
use App\web\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Form;

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
                 'seccion' => $widget->seccion->nombre,
                 'tipo' => $widget->seccion->tipo
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

        }

        $variables_url = '?id='.Input::get('id');
        return redirect('paginas'.$variables_url);

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
        $variables_url = '?id='.Input::get('id');
        return view('web.paginas.secciones.addSeccion',compact('secciones','miga_pan','pagina','variables_url'));

    }

    public function nuevaSeccion(Request $request){

        $exist = Widget::where([
            ['pagina_id',$request->pagina_id],
            ['seccion_id',$request->seccion_id]
        ])->first();

        if($exist){
            $message = "Sección ya registrada en la pagina actual selecionada";
            return redirect()->back()->with('mensaje_error',$message)->withInput($request->input());
        }

        $widget =  new Widget();
        $widget->pagina_id = $request->pagina_id;
        $widget->seccion_id = $request->seccion_id;
        $orden = Widget::where('pagina_id',$request->pagina_id )->count();
        $widget->orden = $orden+1;
        $widget->estado = 'ACTIVO';

        $widget->save();

        return redirect()->back()->withInput($request->input());

    }

    public function showPage($slug){

       $pagina = Pagina::where('slug',$slug)->first();
       $widgets = $pagina->widgets;
       $widgets->sortBy('orden');
       //dd($widgets);
       $view = [];

       foreach ($widgets as $widget){
           $factory = new FactoryCompents($widget->seccion->nombre,$widget->id);
           $componente = $factory();
           if($componente=== false) continue;
           $view[] = $componente->DrawComponent();
       }

       return view('web.index',compact('view'));

    }

    public function edit($id){

        $pagina =  Pagina::find($id);

        if($pagina){

            $miga_pan = [
                [
                    'url' => 'pagina_web'.'?id='. Input::get('id'),
                    'etiqueta' => 'Web'
                ],
                [
                    'url' => 'pagina/administrar?id='.Input::get('id'),
                    'etiqueta' => 'Administración de paginas'
                ],
                [
                    'url' => 'NO',
                    'etiqueta' => 'Editando Página'
                ]
            ];

            $variables_url = '?id='.Input::get('id');
            return view('web.paginas.edit',compact('variables_url','pagina','miga_pan'));

        }else{

            $message = 'El registro que intenta observar no se encuentra registrado, por favor verifique e intente nuevamente.';
            return redirect()->back()
                ->with('mensaje_error',$message);

        }

    }

    public function update(Request $request,$id){

        $pagina =  Pagina::find($id);

        if($pagina){

            if($request->pagina_inicio){

                $principal = Pagina::where('pagina_inicio',true)->get()->first();
                if($principal){
                    $principal->pagina_inicio = !$principal->pagina_inicio;
                    $principal->save();
                }

            }

            $pagina->fill($request->all());
            $pagina->slug = self::generar_slug($request->titulo);
            $flag = $pagina->save();

            if($flag){

                if($request->hasFile('favicon')){

                    $file = $request->file('favicon');
                    $name = time().$file->getClientOriginalName();

                    $filename = storage_path("app/public/iconos/").$name;
                    $flag = file_put_contents($filename,file_get_contents($file->getRealPath()),LOCK_EX);

                    if($flag !== false){
                        $pagina->fill(['favicon' =>$filename])->save();
                    }

                }

                return redirect()->back()->withInput($request->all())
                    ->with('flash_message',"página actualizada correctamente");

            }else{
                return redirect()->back()->withInput($request->all())
                    ->with('mensaje_error',"Error inesperado, la pagina no pudo ser almacenada intente nuevamente más tarde");
            }

        }

    }

    public function destroy($id){

       $pagina = Pagina::find($id);

       if($pagina->widgets->count() > 0){
           return response()->json([
               'status' => 'error',
               'message' => 'la pagina selecionada tiene secciones agregadas'
           ]);
       }

       $flag =  $pagina->delete();

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

    }

}
