<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;

class Impuesto extends Model
{
    protected $table = 'contab_impuestos';
	protected $fillable = ['descripcion', 'tasa_impuesto', 'cta_ventas_id', 'cta_ventas_devol_id', 'cta_compras_id', 'cta_compras_devol_id', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Tasa', 'Cta. ventas', 'Cta. ventas devoluciones', 'Cta. compras', 'Cta. compras devoluciones', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = Impuesto::leftJoin('contab_cuentas AS ctas_ventas','ctas_ventas.id','=','contab_impuestos.cta_ventas_id')->leftJoin('contab_cuentas AS ctas_ventas_dev','ctas_ventas_dev.id','=','contab_impuestos.cta_ventas_devol_id')->leftJoin('contab_cuentas AS ctas_compras','ctas_compras.id','=','contab_impuestos.cta_compras_id')->leftJoin('contab_cuentas AS ctas_compras_dev','ctas_compras_dev.id','=','contab_impuestos.cta_compras_devol_id')->select('contab_impuestos.descripcion AS campo1', 'contab_impuestos.tasa_impuesto AS campo2', DB::raw( "CONCAT( ctas_ventas.codigo,' ',ctas_ventas.descripcion ) AS campo3"),DB::raw( "CONCAT( ctas_ventas_dev.codigo,' ',ctas_ventas_dev.descripcion ) AS campo4"), DB::raw( "CONCAT( ctas_compras.codigo,' ',ctas_compras.descripcion ) AS campo5"), DB::raw( "CONCAT( ctas_compras_dev.codigo,' ',ctas_compras_dev.descripcion ) AS campo6"), 'contab_impuestos.estado AS campo7', 'contab_impuestos.id AS campo8')
	    ->get()
	    ->toArray();
	    return $registros;
	}
	public static function opciones_campo_select()
    {
        $opciones = Impuesto::where('contab_impuestos.estado','Activo')
                    ->select('contab_impuestos.id','contab_impuestos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
