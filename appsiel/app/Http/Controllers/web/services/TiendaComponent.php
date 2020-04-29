<?php


namespace App\Http\Controllers\web\services;

use App\Inventarios\InvGrupo;
use App\Inventarios\InvProducto;
use App\web\Correo;
use App\web\Pedidoweb;
use App\web\Tienda;
use Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class TiendaComponent implements IDrawComponent
{
    public function __construct($widget)
    {
        $this->widget = $widget;
    }

    function DrawComponent()
    {
        $todos = InvProducto::get_datos_pagina_web('', 'Activo');
        
        $items = $todos->where('mostrar_en_pagina_web',1)->all();

        $grupos = $todos->groupBy('grupo_descripcion')->keys()->all();
        
        return Form::tienda($items,$grupos);
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
                'etiqueta' => 'Productos'
            ]
        ];
        $widget = $this->widget;
        $variables_url = '?id=' . Input::get('id');
        $pedido = Pedidoweb::where('widget_id', $widget)->first();
        $items = null;
        if ($pedido != null) {
            $items = InvProducto::where([['mostrar_en_pagina_web', 1]])->orderBy('created_at', 'DESC')->get();
            if (count($items) > 0) {
                foreach ($items as $i) {
                    $i->grupo = "---";
                    $g = InvGrupo::find($i->inv_grupo_id);
                    if ($g != null) {
                        $i->grupo = $g->descripcion;
                    }
                }
            }
        }
        $paises = DB::table('core_paises')->get();
        $correo = Correo::all()->first();
        $tienda = Tienda::where('widget_id',$widget)->first();
        return view('web.components.productos', compact('miga_pan', 'variables_url', 'widget', 'pedido', 'paises','correo','tienda', 'items'));
    }
}
