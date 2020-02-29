<?php


namespace App\Http\Controllers\web\services;

use App\web\Article;
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
        if ($setup != null) {
            $articles = Article::where('articlesetup_id', $setup->id)->orderBy('created_at', $setup->orden)->paginate(4);
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
            $articles = Article::where('articlesetup_id', $setup->id)->orderBy('created_at', $setup->orden)->paginate(4);
            $articles->setPath($variables_url);
        }
        return view('web.components.articles', compact('miga_pan', 'variables_url', 'widget', 'setup', 'articles'));
    }
}
