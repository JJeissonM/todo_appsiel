<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use DB;

class ClaseProveedor extends Model
{
    protected $table = 'compras_clases_proveedores';
	
	protected $fillable = ['descripcion', 'cta_x_pagar_id', 'cta_anticipo_id', 'clase_padre_id', 'estado'];

	public $encabezado_tabla = ['Descripción', 'Cta x pagar default', 'Cta anticipo default', 'Clase padre', 'Estado', 'Acción'];
	
	public static function consultar_registros()
	{
	    $registros = ClaseProveedor::leftJoin('contab_cuentas as cta_x_pagar','cta_x_pagar.id','=','compras_clases_proveedores.cta_x_pagar_id')
	    					->leftJoin('contab_cuentas as cta_anticipo','cta_anticipo.id','=','compras_clases_proveedores.cta_anticipo_id')
	    					->select('compras_clases_proveedores.descripcion AS campo1', DB::raw('CONCAT(cta_x_pagar.codigo," ",cta_x_pagar.descripcion) AS campo2'), DB::raw('CONCAT(cta_anticipo.codigo," ",cta_anticipo.descripcion) AS campo3'), 'compras_clases_proveedores.clase_padre_id AS campo4', 'compras_clases_proveedores.estado AS campo5', 'compras_clases_proveedores.id AS campo6')
						    ->get()
						    ->toArray();
	    return $registros;
	}

	    public static function opciones_campo_select()
	    {
	        $opciones = ClaseProveedor::where('compras_clases_proveedores.estado','Activo')
	                    ->select('compras_clases_proveedores.id','compras_clases_proveedores.descripcion')
	                    ->get();

	        $vec['']='';
	        foreach ($opciones as $opcion)
	        {
	            $vec[$opcion->id] = $opcion->descripcion;
	        }

	        return $vec;
	    }
}
