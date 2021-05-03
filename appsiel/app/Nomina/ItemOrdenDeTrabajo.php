<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ItemOrdenDeTrabajo extends Model
{
    protected $table = 'nom_items_ordenes_de_trabajo';
    protected $fillable = ['orden_trabajo_id', 'inv_producto_id', 'cantidad', 'costo_unitario', 'costo_total', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Orden de trabajo', 'Item', 'Cantidad', 'Costo unitario', 'Costo total', 'Estado'];

    public function item()
    {
        return $this->belongsTo( 'App\Inventarios\InvProducto', 'inv_producto_id' );
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = ItemOrdenDeTrabajo::select('nom_items_ordenes_de_trabajo.orden_trabajo_id AS campo1', 'nom_items_ordenes_de_trabajo.inv_producto_id AS campo2', 'nom_items_ordenes_de_trabajo.cantidad AS campo3', 'nom_items_ordenes_de_trabajo.costo_unitario AS campo4', 'nom_items_ordenes_de_trabajo.costo_total AS campo5', 'nom_items_ordenes_de_trabajo.estado AS campo6', 'nom_items_ordenes_de_trabajo.id AS campo7')
                                    ->orderBy('compras_doc_encabezados.fecha', 'DESC')
                                    ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if ( self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if ( empty($nuevaColeccion) )
        {
            return $array = new LengthAwarePaginator([], 1, 1, 1, [
                                                                    'path' => $request->url(),
                                                                    'query' => $request->query(),
                                                                ]);
        }
        
        //obtenemos el numero de la página actual, por defecto 1
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $total = count($nuevaColeccion); //Total para contar los registros mostrados
        $starting_point = ($page * $nro_registros) - $nro_registros; // punto de inicio para mostrar registros
        $array = $nuevaColeccion->slice($starting_point, $nro_registros); //indicamos desde donde y cuantos registros mostrar
        $array = new LengthAwarePaginator($array, $total, $nro_registros, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]); //finalmente se pagina y organiza la coleccion a devolver con todos los datos

        return $array;
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param array $valores_campos_seleccionados de campos donde se busca
     * @param string $searchTerm termino de busqueda
     * @return bool
     */
    public static function likePhp($valores_campos_seleccionados, $searchTerm)
    {
        $encontrado = false;
        $searchTerm = str_slug($searchTerm); // Para eliminar acentos
        foreach ($valores_campos_seleccionados as $valor_campo)
        {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false)
            {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    public static function sqlString($search)
    {
        $string = ItemOrdenDeTrabajo::select('nom_items_ordenes_de_trabajo.orden_trabajo_id AS campo1', 'nom_items_ordenes_de_trabajo.inv_producto_id AS campo2', 'nom_items_ordenes_de_trabajo.cantidad AS campo3', 'nom_items_ordenes_de_trabajo.costo_unitario AS campo4', 'nom_items_ordenes_de_trabajo.costo_total AS campo5', 'nom_items_ordenes_de_trabajo.estado AS campo6', 'nom_items_ordenes_de_trabajo.id AS campo7')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE ITEMS EN LAS ÓRDENES DE TRABAJO";
    }

    public static function opciones_campo_select()
    {
        $opciones = ItemOrdenDeTrabajo::where('nom_items_ordenes_de_trabajo.estado','Activo')
                    ->select('nom_items_ordenes_de_trabajo.id','nom_items_ordenes_de_trabajo.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
