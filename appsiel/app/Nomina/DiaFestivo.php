<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class DiaFestivo extends Model
{
    protected $table = 'nom_dias_festivos';
	
	protected $fillable = ['fecha', 'observacion'];
	
	public $encabezado_tabla = ['Fecha', 'Observación', 'Acción'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

	public static function consultar_registros()
	{
	    return DiaFestivo::select(
	    							'nom_dias_festivos.fecha AS campo1',
	    							'nom_dias_festivos.observacion AS campo2',
	    							'nom_dias_festivos.id AS campo3')
						    ->get()
						    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = DiaFestivo::where('nom_dias_festivos.estado','Activo')
                    ->select('nom_dias_festivos.id','nom_dias_festivos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function validar_eliminacion($id)
    {
    	return 'ok';
    }
}
