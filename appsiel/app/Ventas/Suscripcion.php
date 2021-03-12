<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    protected $table = 'vtas_suscripciones';
    
    protected $fillable = ['cliente_id', 'fecha_desde', 'fecha_hasta', 'plantilla_suscripcion_id', 'inv_producto_id', 'creado_por', 'modificado_por', 'estado'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Cliente', 'Fecha inicio', 'Fecha terminación', 'Plan suscrito', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return Suscripcion::leftJoin('vtas_clientes', 'vtas_clientes.id', '=', 'vtas_suscripciones.cliente_id')
            					->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            					->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_suscripciones.inv_producto_id')
            					->select(
            								'core_terceros.descripcion AS campo1',
            								'vtas_suscripciones.fecha_desde AS campo2',
            								'vtas_suscripciones.fecha_hasta AS campo3',
            								'inv_productos.descripcion AS campo4',
            								'vtas_suscripciones.estado AS campo5',
            								'vtas_suscripciones.id AS campo6')
        						->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Suscripcion::select('vtas_suscripciones.cliente_id AS campo1', 'vtas_suscripciones.fecha_desde AS campo2', 'vtas_suscripciones.fecha_hasta AS campo3', 'vtas_suscripciones.inv_producto_id AS campo4', 'vtas_suscripciones.estado AS campo5', 'vtas_suscripciones.id AS campo6')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE SUSCRIPCIONES";
    }

    public static function opciones_campo_select()
    {
        $opciones = Suscripcion::where('vtas_suscripciones.estado','Activo')
                    ->select('vtas_suscripciones.id','vtas_suscripciones.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
