<?php


namespace App\Http\Controllers\web\services;

use App\web\Archivo;
use App\web\Archivoitem;
use App\web\Article;
use App\web\Articlesetup;
use Form;
use Illuminate\Support\Facades\Input;

class ArchivosComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $archivo = Archivo::where('widget_id', $this->widget)->first();
        $items = null;
        if ($archivo != null) {
            $items = Archivoitem::where([['archivo_id', $archivo->id], ['estado', 'VISIBLE']])->orderBy('created_at', 'DESC')->get();
        }
        //dd( $items );
        return Form::archivos($items, $archivo);
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
                'etiqueta' => 'Archivos'
            ]
        ];
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $archivo = Archivo::where('widget_id', $widget)->first();
        $items = null;
        if ($archivo != null) {
            $items = Archivoitem::where('archivo_id', $archivo->id)->orderBy('created_at', 'DESC')->get();
        }
        return view('web.components.archivos', compact('miga_pan', 'variables_url', 'widget', 'archivo', 'items'));
    }
}
