<?php


namespace App\Http\Controllers\web\services;

use App\web\Article;
use App\web\Articlecategory;
use App\web\Articlesetup;
use Form;
use Illuminate\Support\Facades\Input;

class ArticleComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $setup = Articlesetup::where('widget_id', $this->widget)->first();
        $articles = null;
        //definir si el componente tiene un solo artículo o una categoría
        if ($setup != null) {
            if ($setup->article_id != null) {
                //si es un solo articulo
                $articles = $setup->article;
            } elseif ($setup->articlecategory_id != null) {
                //si es una categoria
                $articles = Article::where('articlecategory_id', $setup->articlecategory_id)->orderBy('created_at', $setup->orden)->get();
            }
        }
        return Form::articles($articles, $setup);
    }

    function viewComponent()
    {
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'paginas?id=' . Input::get('id'),
                'etiqueta' => 'Paginas y secciones'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Artículos'
            ]
        ];
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $setup = Articlesetup::where('widget_id', $widget)->first();
        $articles = null;
        if ($setup != null) {
            //definir si el componente tiene un solo artículo o una categoría
            if ($setup != null) {
                if ($setup->article_id != null) {
                    //si es un solo articulo
                    $articles = $setup->article;
                } elseif ($setup->articlecategory_id != null) {
                    //si es una categoria
                    $articles = Article::where('articlecategory_id', $setup->articlecategory_id)->orderBy('created_at', $setup->orden)->get();
                }
            }
            //$articles->setPath($variables_url);
        }
        //todas las categorias
        $categorias = Articlecategory::all();
        //todos los articulos
        $articulos = Article::all();
        return view('web.components.articles', compact('articulos', 'categorias', 'miga_pan', 'variables_url', 'widget', 'setup', 'articles'));
    }
}
