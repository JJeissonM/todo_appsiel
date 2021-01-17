<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class WidgetsElementsDesign extends Model
{
    protected $table = 'pw_widgets_elements_designs';
    protected $fillable = [ 'links', 'estilos', 'scripts', 'widget_id'];


    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Página', 'Sección', 'Links', 'Estilos', 'Scripts'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    
    /**/
    public static function consultar_registros($nro_registros, $search)
    {
        $registros = WidgetsElementsDesign::leftJoin('pw_widget', 'pw_widget.id', '=', 'pw_widgets_elements_designs.widget_id')
            ->leftJoin('pw_paginas', 'pw_paginas.id', '=', 'pw_widget.pagina_id')
            ->leftJoin('pw_seccion', 'pw_seccion.id', '=', 'pw_widget.seccion_id')
            ->select(
                'pw_paginas.descripcion AS campo1',
                'pw_seccion.nombre AS campo2',
                'pw_widgets_elements_designs.links AS campo3',
                'pw_widgets_elements_designs.estilos AS campo4',
                'pw_widgets_elements_designs.scripts AS campo5',
                'pw_widgets_elements_designs.id AS campo6'
            )->where("pw_paginas.descripcion", "LIKE", "%$search%")
            ->orWhere("pw_seccion.nombre", "LIKE", "%$search%")
            ->orWhere("pw_widgets_elements_designs.links", "LIKE", "%$search%")
            ->orWhere("pw_widgets_elements_designs.estilos", "LIKE", "%$search%")
            ->orWhere("pw_widgets_elements_designs.scripts", "LIKE", "%$search%")
            ->orderBy('pw_widgets_elements_designs.created_at', 'DESC')
            ->paginate($nro_registros);
            return $registros;
    }

    public static function sqlString($search)
    {
        $string = WidgetsElementsDesign::leftJoin('pw_widget', 'pw_widget.id', '=', 'pw_widgets_elements_designs.widget_id')
            ->leftJoin('pw_paginas', 'pw_paginas.id', '=', 'pw_widget.pagina_id')
            ->leftJoin('pw_seccion', 'pw_seccion.id', '=', 'pw_widget.seccion_id')
            ->select(
                'pw_paginas.descripcion AS DESCRIPCIÓN',
                'pw_seccion.nombre AS NOMBRE',
                'pw_widgets_elements_designs.links AS ENLACES',
                'pw_widgets_elements_designs.estilos AS ESTILOS',
                'pw_widgets_elements_designs.scripts AS SCRIPTS'
            )->where("pw_paginas.descripcion", "LIKE", "%$search%")
            ->orWhere("pw_seccion.nombre", "LIKE", "%$search%")
            ->orWhere("pw_widgets_elements_designs.links", "LIKE", "%$search%")
            ->orWhere("pw_widgets_elements_designs.estilos", "LIKE", "%$search%")
            ->orWhere("pw_widgets_elements_designs.scripts", "LIKE", "%$search%")
            ->orderBy('pw_widgets_elements_designs.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ELEMENTOS DE DISEÑO DE LOS WIDGETS";
    }

    public function generar_array_links()
    {
        $links = [];

        if ( $this->links != '' )
        {
            $enlaces = json_decode( $this->links );
            foreach ($enlaces as $enlace)
            {
                $links[] = '<link href="'.$enlace->href.'" rel="'.$enlace->rel.'">';
            }
        }
        	 
        return $links;
    }

}
