<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

/*
        -------------------  OJO ----------------
    CORREGIR PARA LOS CLIENTES NO LOGUEADOS EN LA WEB
    SE COMENTÓ LA LÍNEA DE PEDIR AUTENCIACIÓN
*/
use Auth;


use App\Inventarios\InvGrupo;

use App\Contabilidad\Impuesto;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;

class ItemDesarmeAutomatico extends Model
{
    protected $table = 'inv_items_desarmes_automaticos'; 

    protected $fillable = [ 'item_consumir_id', 'item_producir_id', 'cantidad_proporcional', 'estado'];

    public $encabezado_tabla = [ 'Item a consumir', 'Item a producir', 'Cantidad proporcional', 'Estado', 'Acciones'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public function item_consumir()
    {
      return $this->belongsTo(InvProducto::class,'item_consumir_id');
    }

    public function item_producir()
    {
      return $this->belongsTo(InvProducto::class,'item_producir_id');
    }

    public static function consultar_registros()
    {
        return ItemDesarmeAutomatico::leftJoin( 'inv_productos AS items_consumir', 'items_consumir.id', '=', 'inv_items_desarmes_automaticos.item_consumir_id')
                                ->leftJoin( 'inv_productos AS items_producir', 'items_producir.id', '=', 'inv_items_desarmes_automaticos.item_producir_id')
                                ->select(
                                            'items_consumir.descripcion AS campo1',
                                            'items_producir.descripcion AS campo2',
                                            'inv_items_desarmes_automaticos.cantidad_proporcional AS campo3',
                                            'inv_items_desarmes_automaticos.estado AS campo4',
                                            'inv_items_desarmes_automaticos.id AS campo5'
                                        )
                                ->get()
                                ->toArray();
    }

}