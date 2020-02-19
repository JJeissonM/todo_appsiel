<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class ArqueoCaja extends Model
{
    protected $table = 'teso_arqueos_caja';
	
    protected $fillable = ['fecha', 'core_empresa_id', 'teso_caja_id', 'billetes_contados', 'monedas_contadas', 'detalle', 'estado','creado_por','modificado_por'];
	
    public $encabezado_tabla = ['Fecha', 'Caja', 'Observaciones', 'Estado', 'Acción'];
	
    public static function consultar_registros()
	{
	    $registros = ArqueoCaja::select('teso_arqueos_caja.fecha AS campo1', 'teso_arqueos_caja.teso_caja_id AS campo2', 'teso_arqueos_caja.detalle AS campo3', 'teso_arqueos_caja.estado AS campo4', 'teso_arqueos_caja.id AS campo5')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = ArqueoCaja::where('teso_arqueos_caja.estado','Activo')
                    ->select('teso_arqueos_caja.id','teso_arqueos_caja.detalle')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->detalle;
        }

        return $vec;
    }
}
