<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RecetaCocina extends Model
{
    protected $table = 'inv_recetas_cocina';
	
    protected $fillable = ['item_platillo_id','item_ingrediente_id','cantidad_porcion'];

	  public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Producto terminado', 'Insumo', 'Cant. porción'];

    public $vistas = '{"show":"inventarios.recetas.show"}';

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public function item_platillo()
    {
      return $this->belongsTo(InvProducto::class,'item_platillo_id');
    }

    public function item_ingrediente()
    {
      return $this->belongsTo(InvProducto::class,'item_ingrediente_id');
    }

    public function ingredientes()
    {
        $lista = RecetaCocina::where('item_platillo_id',$this->item_platillo_id)->get();

        $data = [];
        foreach ($lista as $platillo) {
            $data[] = [
                    'id' => $platillo->id,
                    'ingrediente' => $platillo->item_ingrediente,
                    'cantidad_porcion' => $platillo->cantidad_porcion
                ];
        }
      return $data;
    }

    public static function consultar_registros($nro_registros, $search)
    {
      $collection =  RecetaCocina::leftJoin('inv_productos AS items_terminados', 'items_terminados.id', '=', 'inv_recetas_cocina.item_platillo_id')
        ->leftJoin('inv_productos AS items_insumos', 'items_insumos.id', '=', 'inv_recetas_cocina.item_ingrediente_id')
        ->select(
          DB::raw('CONCAT(items_terminados.id," - ",items_terminados.descripcion," (",items_terminados.unidad_medida1,")") AS campo1'),
          DB::raw('CONCAT(items_insumos.id," - ",items_insumos.descripcion," (",items_insumos.unidad_medida1,")") AS campo2'),
          'inv_recetas_cocina.cantidad_porcion AS campo3',
          'inv_recetas_cocina.id AS campo4'
        )
        ->where("items_terminados.descripcion", "LIKE", "%$search%")
        ->orWhere("items_terminados.id", "LIKE", "%$search%")
        ->orWhere("items_insumos.id", "LIKE", "%$search%")
        ->orWhere("items_insumos.descripcion", "LIKE", "%$search%")
        ->orWhere("inv_recetas_cocina.cantidad_porcion", "LIKE", "%$search%")
        ->orderBy('inv_recetas_cocina.created_at', 'DESC')
        ->groupBy('inv_recetas_cocina.item_platillo_id')
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

        foreach( $nuevaColeccion AS $register_collect )
        {
            $item_terminado =  InvProducto::find( (int)explode( '-', $register_collect->campo1 )[0] );
            
            $item_insumo =  InvProducto::find( (int)explode( '-', $register_collect->campo2 )[0] );

            $register_collect->campo1 = $item_terminado->get_value_to_show();
            $register_collect->campo2 = $item_insumo->get_value_to_show();
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
    $string = RecetaCocina::leftJoin('inv_productos AS items_consumir', 'items_consumir.id', '=', 'inv_recetas_cocina.item_platillo_id')
      ->leftJoin('inv_productos AS items_producir', 'items_producir.id', '=', 'inv_recetas_cocina.item_ingrediente_id')
      ->select(
        DB::raw('CONCAT(items_consumir.id," - ",items_consumir.descripcion," (",items_consumir.unidad_medida1,")") AS PRODUCTO_TERMINADO'),
        DB::raw('CONCAT(items_producir.id," - ",items_producir.descripcion," (",items_producir.unidad_medida1,")") AS INSUMO'),
        'inv_recetas_cocina.cantidad_porcion AS CANTIDAD_PORCION'
      )
      ->where("items_consumir.descripcion", "LIKE", "%$search%")
      ->orWhere("items_consumir.id", "LIKE", "%$search%")
      ->orWhere("items_producir.id", "LIKE", "%$search%")
      ->orWhere("items_producir.descripcion", "LIKE", "%$search%")
      ->orWhere("inv_recetas_cocina.cantidad_porcion", "LIKE", "%$search%")
      ->orderBy('inv_recetas_cocina.created_at', 'DESC')
      ->toSql();
    return str_replace('?', '"%' . $search . '%"', $string);
  }

  //Titulo para la exportación en PDF y EXCEL
  public static function tituloExport()
  {
    return "ASIGNACIÓN DE INGREDIENTES A RECETAS DE COCINA";
  }   

  public static function opciones_campo_select()
  {
      $opciones = RecetaCocina::groupBy('item_platillo_id')
                          ->get();

      $vec['']='';
      foreach ($opciones as $opcion){

          if ($opcion->item_platillo == null) {
            continue;
          }
          
          $vec[$opcion->id] = $opcion->item_platillo->get_value_to_show();
      }

      return $vec;
  }
}
