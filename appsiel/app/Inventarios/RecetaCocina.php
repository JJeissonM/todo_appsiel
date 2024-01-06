<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class RecetaCocina extends Model
{
    protected $table = 'inv_recetas_cocina';
	
    protected $fillable = ['item_platillo_id','item_ingrediente_id','cantidad_porcion'];

	  public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Platillo', 'Ingrediente', 'Cant. porción'];

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
      return RecetaCocina::leftJoin('inv_productos AS items_consumir', 'items_consumir.id', '=', 'inv_recetas_cocina.item_platillo_id')
        ->leftJoin('inv_productos AS items_producir', 'items_producir.id', '=', 'inv_recetas_cocina.item_ingrediente_id')
        ->select(
          DB::raw('CONCAT(items_consumir.id," - ",items_consumir.descripcion," (",items_consumir.unidad_medida1,")") AS campo1'),
          DB::raw('CONCAT(items_producir.id," - ",items_producir.descripcion," (",items_producir.unidad_medida1,")") AS campo2'),
          'inv_recetas_cocina.cantidad_porcion AS campo3',
          'inv_recetas_cocina.id AS campo4'
        )
        ->where("items_consumir.descripcion", "LIKE", "%$search%")
        ->orWhere("items_producir.descripcion", "LIKE", "%$search%")
        ->orWhere("inv_recetas_cocina.cantidad_porcion", "LIKE", "%$search%")
        ->orderBy('inv_recetas_cocina.created_at', 'DESC')
        ->groupBy('inv_recetas_cocina.item_platillo_id')
        ->paginate($nro_registros);
    }
  
  public static function sqlString($search)
  {
    $string = RecetaCocina::leftJoin('inv_productos AS items_consumir', 'items_consumir.id', '=', 'inv_recetas_cocina.item_platillo_id')
      ->leftJoin('inv_productos AS items_producir', 'items_producir.id', '=', 'inv_recetas_cocina.item_ingrediente_id')
      ->select(
        DB::raw('CONCAT(items_consumir.id," - ",items_consumir.descripcion," (",items_consumir.unidad_medida1,")") AS campo1'),
        DB::raw('CONCAT(items_producir.id," - ",items_producir.descripcion," (",items_producir.unidad_medida1,")") AS campo2'),
        'inv_recetas_cocina.cantidad_porcion AS campo3',
        'inv_recetas_cocina.id AS campo4'
      )
      ->where("items_consumir.descripcion", "LIKE", "%$search%")
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
          $vec[$opcion->id] = $opcion->item_platillo->id.' '.$opcion->item_platillo->descripcion;
      }

      return $vec;
  }
}
