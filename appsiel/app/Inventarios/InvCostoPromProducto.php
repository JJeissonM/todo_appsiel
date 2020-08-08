<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use DB;

class InvCostoPromProducto extends Model
{
	protected $fillable = ['inv_bodega_id','inv_producto_id','costo_promedio'];

	public $encabezado_tabla = ['Bodega','Producto', 'Costo promedio','Fecha creación','Fecha actualización','Acción'];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

	// Se consultan los documentos para la empresa que tiene asignada el usuario
    public static function consultar_registros()
    {
        $registros = InvCostoPromProducto::leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_costo_prom_productos.inv_bodega_id')
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_costo_prom_productos.inv_producto_id')
                    ->select(
                                'inv_bodegas.descripcion AS campo1', 
                                DB::raw('CONCAT(inv_productos.id, " - ",inv_productos.descripcion, " (",inv_productos.unidad_medida1,")") as campo2'),
                                'inv_costo_prom_productos.costo_promedio AS campo3',
                                'inv_costo_prom_productos.created_at AS campo4',
                                'inv_costo_prom_productos.updated_at AS campo5',
                                'inv_costo_prom_productos.id AS campo6')
                    ->get()
                    ->toArray();

        return $registros;
    }


    public static function get_costo_promedio( $bodega_id, $producto_id )
    {
        $costo_prom = InvCostoPromProducto::where('inv_bodega_id','=',$bodega_id)
                                    ->where('inv_producto_id','=',$producto_id )
                                    ->value('costo_promedio');

        if ( is_null( $costo_prom ) || $costo_prom < 0 )
        {
            $item = InvProducto::find( $producto_id );
            $costo_prom = 0;
            if ( !is_null( $item ) )
            {
                $costo_prom = $item->precio_compra;
            }
        }

        return $costo_prom;
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{}';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
