<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class WidgetsElementsDesign extends Model
{
    protected $table = 'pw_widgets_elements_designs';
    protected $fillable = [ 'links', 'estilos', 'scripts', 'widget_id'];


    public $encabezado_tabla = [ 'ID Widget', 'Página', 'Sección', 'Links', 'Estilos', 'Scripts', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    
    /**/
    public static function consultar_registros()
    {
    	$registros = WidgetsElementsDesign::leftJoin('pw_widget', 'pw_widget.id', '=', 'pw_widgets_elements_designs.widget_id')
                    ->leftJoin('pw_paginas', 'pw_paginas.id', '=', 'pw_widget.pagina_id')
                    ->leftJoin('pw_seccion', 'pw_seccion.id', '=', 'pw_widget.seccion_id')
                    ->select(
                            'pw_widgets_elements_designs.id AS campo1',
                            'pw_paginas.descripcion AS campo2',
                            'pw_seccion.nombre AS campo3',
                            'pw_widgets_elements_designs.links AS campo4',
                            'pw_widgets_elements_designs.estilos AS campo5',
                            'pw_widgets_elements_designs.scripts AS campo6',
                            'pw_widgets_elements_designs.id AS campo7' )
                    ->get()
                    ->toArray();

        return $registros;
    }



    public function generar_array_links()
    {
    	$enlaces = json_decode( $this->links );
    	$links = [];
    	foreach ($enlaces as $enlace)
    	{
    	 	$links[] = '<link href="'.$enlace->href.'" rel="'.$enlace->rel.'">';
    	} 
        return $links;
    }

}
