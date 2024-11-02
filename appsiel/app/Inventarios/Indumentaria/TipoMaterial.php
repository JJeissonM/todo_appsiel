<?php

namespace App\Inventarios\Indumentaria;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Pagination\LengthAwarePaginator;

class TipoMaterial extends Model
{
    protected $table = 'inv_indum_tipos_materiales';

    protected $fillable = ['codigo','descripcion','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código',  'Descripción', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila","store":"web","update":"web/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $collection =  TipoMaterial::select(
                'inv_indum_tipos_materiales.codigo AS campo1',
                'inv_indum_tipos_materiales.descripcion AS campo2',
                'inv_indum_tipos_materiales.estado AS campo3',
                'inv_indum_tipos_materiales.id AS campo4'
            )
            ->orderBy('inv_indum_tipos_materiales.created_at', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if (empty($nuevaColeccion)) {
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
        foreach ($valores_campos_seleccionados as $valor_campo) {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false) {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

    public static function sqlString($search)
    {
        $string = TipoMaterial::select(
                'inv_indum_tipos_materiales.id AS CÓDIGO',
                'inv_indum_tipos_materiales.descripcion AS DESCRIPCIÓN',
                'inv_indum_tipos_materiales.estado AS ESTADO'
            )
            ->where("inv_indum_tipos_materiales.id", "LIKE", "%$search%")
            ->orWhere("inv_indum_tipos_materiales.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_indum_tipos_materiales.codigo", "LIKE", "%$search%")
            ->orWhere("inv_indum_tipos_materiales.estado", "LIKE", "%$search%")
            ->orderBy('inv_indum_tipos_materiales.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TIPOS DE PRENDAS";
    }  

    public static function opciones_campo_select()
    {
        $opciones = TipoMaterial::where('estado','Activo')
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->codigo.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
