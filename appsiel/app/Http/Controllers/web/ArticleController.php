<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\PaginaWeb\Categoria;
use App\web\Article;
use App\web\Articlecategory;
use App\web\Articlesetup;
use Illuminate\Support\Facades\Input;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorias = Articlecategory::all();
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Artículos - Índice'
            ]
        ];
        $categorias[] = new Articlecategory(['id' => 0, 'titulo' => 'Sin Categoría', 'descripcion' => 'Artículos creados sin categoría']);
        $variables_url = '?id=' . Input::get('id');
        return view('web.components.articulos.index', compact('miga_pan', 'variables_url', 'categorias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $as = new Articlesetup($request->all());
        $variables_url = $request->variables_url;
        if ($request->mostrara == 'ARTICULO') {
            $as->article_id = $request->article_id;
            $as->articlecategory_id = null;
        } else if ($request->mostrara == 'CATEGORIA') {
            $as->articlecategory_id = $request->articlecategory_id;
            $as->article_id = null;
        } else {
            $message = 'Debe indicar el artículo o la categoría para esta sección.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('mensaje_error', $message);
        }
        $result = $as->save();
        if ($result) {
            $message = 'La configuración de la sección fue almacenada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser almacenada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('mensaje_error', $message);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('web.components.articles_show_one')->with('articulo', Article::find($id));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show2($id)
    {
        $miga_pan = [
            [
                'url' => 'pagina_web?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'articles?id=' . Input::get('id'),
                'etiqueta' => 'Artículos - Índice'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver Artículo'
            ]
        ];
        $variables_url = '?id=' . Input::get('id');
        $articulo = Article::find($id);
        return view('web.components.articulos.show', compact('miga_pan', 'variables_url', 'articulo'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $as = Articlesetup::find($id);
        $as->fill($request->all());
        $variables_url = $request->variables_url;
        if ($request->mostrara2 == 'ARTICULO') {
            $as->article_id = $request->article_id;
            $as->articlecategory_id = null;
        } else if ($request->mostrara2 == 'CATEGORIA') {
            $as->articlecategory_id = $request->articlecategory_id;
            $as->article_id = null;
        } else {
            $message = 'Debe indicar el artículo o la categoría para esta sección.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('mensaje_error', $message);
        }
        $result = $as->save();
        if ($result) {
            $message = 'La configuración de la sección fue modificada correctamente.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('flash_message', $message);
        } else {
            $message = 'La configuración no pudo ser modificada, intente mas tarde.';
            return redirect(url('seccion/' . $request->widget_id) . $variables_url)->with('mensaje_error', $message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $article = Article::find($id);
        if ($article) {
            if ($article->imagen != '')
                if ( file_exists( $article->imagen ) )
                { unlink( $article->imagen ); }

            $flag =  $article->delete();
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
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Error inesperado, por favor intentelo más tarde.'
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function articlestore(Request $request)
    {
        $a = new Article($request->all());
        if ($a->articlecategory_id == '0') {
            $a->articlecategory_id = null;
        }
        $result = $a->save();
        if ($request->hasFile('imagen')) {

            $file = $request->file('imagen');

            $name = str_slug($file->getClientOriginalName()) . '-' . time() . '.' . $file->clientExtension();

            $filename = "img/articles/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                $a->fill(['imagen' => $filename])->save();
            }
        }
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'El artículo fue almacenado correctamente.';
            return redirect(url('articles' . $variables_url))->with('flash_message', $message);
        } else {
            $message = 'El artículo no pudo ser almacenado, intente mas tarde.';
            return redirect(url('articles' .  $variables_url))->with('mensaje_error', $message);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function articleupdate(Request $request)
    {
        $a = Article::find($request->article_id);
        $old_image = $a->imagen;
        $a->titulo = $request->titulo;
        $a->estado = $request->estado;
        $a->contenido = $request->contenido;
        $a->descripcion = $request->descripcion;
        $result = $a->save();
        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $name = str_slug($file->getClientOriginalName()) . '-' . time() . '.' . $file->clientExtension();
            $filename = "img/articles/" . $name;
            $flag = file_put_contents($filename, file_get_contents($file->getRealPath()), LOCK_EX);
            if ($flag !== false) {
                if ($old_image != '') {
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }
                $a->fill(['imagen' => $filename])->save();
            }
        }
        $variables_url = $request->variables_url;
        if ($result) {
            $message = 'El artículo fue modificado correctamente.';
            return redirect(url('articles' . $variables_url))->with('flash_message', $message);
        } else {
            $message = 'El artículo no pudo ser modificado, intente mas tarde.';
            return redirect(url('articles' .  $variables_url))->with('mensaje_error', $message);
        }
    }

    //obtiene los artículos de una categoría
    public function articlesCategory($id)
    {
        $articulos = null;
        if ($id == '0') {
            $articulos = Article::where('articlecategory_id', null)->get();
        }
        if ($id != '0') {
            $articulos = Article::where('articlecategory_id', $id)->get();
        }
        if ($articulos != null) {
            if (count($articulos) > 0) {
                return json_encode($articulos);
            } else {
                return "null";
            }
        } else {
            return "null";
        }
    }
}
