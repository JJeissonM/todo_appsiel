<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

/*
        -------------------  OJO ----------------
    CORREGIR PARA LOS CLIENTES NO LOGUEADOS EN LA WEB
    SE COMENTÓ LA LÍNEA DE PEDIR AUTENCIACIÓN
*/

use Illuminate\Support\Facades\DB;

class ItemDesarmeAutomatico extends Model
{
    protected $table = 'inv_items_desarmes_automaticos'; 

    // item_consumir_id: el que se compra (para dar salida)
    // item_producir_id: el que se vende (para dar entrada)
    // cantidad_proporcional: cantidad a producir por cada unidad (1) de item a consumir
    protected $fillable = [ 'item_consumir_id', 'item_producir_id', 'cantidad_proporcional', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Item a consumir (SE COMPRA)', 'Item a producir (SE VENDE)', 'Cantidad proporcional', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public function item_consumir()
    {
      return $this->belongsTo(InvProducto::class,'item_consumir_id');
    }

    public function item_producir()
    {
      return $this->belongsTo(InvProducto::class,'item_producir_id');
    }

    public static function consultar_registros($nro_registros, $search)
  {
    return ItemDesarmeAutomatico::leftJoin('inv_productos AS items_consumir', 'items_consumir.id', '=', 'inv_items_desarmes_automaticos.item_consumir_id')
      ->leftJoin('inv_productos AS items_producir', 'items_producir.id', '=', 'inv_items_desarmes_automaticos.item_producir_id')
      ->select(
        DB::raw('CONCAT(items_consumir.id," - ",items_consumir.descripcion," (",items_consumir.unidad_medida1,")") AS campo1'),
        DB::raw('CONCAT(items_producir.id," - ",items_producir.descripcion," (",items_producir.unidad_medida1,")") AS campo2'),
        'inv_items_desarmes_automaticos.cantidad_proporcional AS campo3',
        'inv_items_desarmes_automaticos.estado AS campo4',
        'inv_items_desarmes_automaticos.id AS campo5'
      )
      ->where("items_consumir.descripcion", "LIKE", "%$search%")
      ->orWhere("items_producir.descripcion", "LIKE", "%$search%")
      ->orWhere("inv_items_desarmes_automaticos.cantidad_proporcional", "LIKE", "%$search%")
      ->orWhere("inv_items_desarmes_automaticos.estado", "LIKE", "%$search%")
      ->orderBy('inv_items_desarmes_automaticos.created_at', 'DESC')
      ->paginate($nro_registros);
  }
  
  public static function sqlString($search)
  {
    $string = ItemDesarmeAutomatico::leftJoin('inv_productos AS items_consumir', 'items_consumir.id', '=', 'inv_items_desarmes_automaticos.item_consumir_id')
      ->leftJoin('inv_productos AS items_producir', 'items_producir.id', '=', 'inv_items_desarmes_automaticos.item_producir_id')
      ->select(
        'items_consumir.descripcion AS campo1',
        'items_producir.descripcion AS campo2',
        'inv_items_desarmes_automaticos.cantidad_proporcional AS campo3',
        'inv_items_desarmes_automaticos.estado AS campo4',
        'inv_items_desarmes_automaticos.id AS campo5'
      )
      ->where("items_consumir.descripcion", "LIKE", "%$search%")
      ->orWhere("items_producir.descripcion", "LIKE", "%$search%")
      ->orWhere("inv_items_desarmes_automaticos.cantidad_proporcional", "LIKE", "%$search%")
      ->orWhere("inv_items_desarmes_automaticos.estado", "LIKE", "%$search%")
      ->orderBy('inv_items_desarmes_automaticos.created_at', 'DESC')
      ->toSql();
    return str_replace('?', '"%' . $search . '%"', $string);
  }

  //Titulo para la exportación en PDF y EXCEL
  public static function tituloExport()
  {
    return "LISTADO DE CONFIG. ITEMS PARA DESARME AUTOMÁTICO";
  }
}
