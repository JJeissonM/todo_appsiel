<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

class PlanClaseEstrucElemento extends Model
{
	protected $table = 'sga_plan_clases_struc_elementos';
	protected $fillable = ['plantilla_plan_clases_id', 'descripcion', 'orden', 'estado'];
	public $encabezado_tabla = ['Plan de clases', 'Descripción', 'Orden', 'Estado', 'Acción'];
	
	public static function consultar_registros()
	{
	    return PlanClaseEstrucElemento::leftJoin('sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id', '=', 'sga_plan_clases_struc_elementos.plantilla_plan_clases_id')
                            		->select(
                            					'sga_plan_clases_struc_plantillas.descripcion AS campo1',
                            					'sga_plan_clases_struc_elementos.descripcion AS campo2',
                            					'sga_plan_clases_struc_elementos.orden AS campo3',
                            					'sga_plan_clases_struc_elementos.estado AS campo4',
                            					'sga_plan_clases_struc_elementos.id AS campo5')
								    ->get()
								    ->toArray();
	}

	public static function opciones_campo_select()
    {
        $opciones = PlanClaseEstrucElemento::where('sga_plan_clases_struc_elementos.estado','Activo')
                    ->select('sga_plan_clases_struc_elementos.id','sga_plan_clases_struc_elementos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
