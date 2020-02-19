<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use App\Matriculas\PeriodoLectivo;

class PlanClaseEstrucPlantilla extends Model
{
    protected $table = 'sga_plan_clases_struc_plantillas';
	protected $fillable = ['periodo_lectivo_id', 'descripcion', 'detalle', 'estado'];
	public $encabezado_tabla = ['Año Lectivo', 'Descripción', 'Detalle', 'Estado', 'Acción'];
	
	public static function consultar_registros()
	{
	    return PlanClaseEstrucPlantilla::leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_plan_clases_struc_plantillas.periodo_lectivo_id')
                            ->select(
                            			'sga_periodos_lectivos.descripcion AS campo1',
                            			'sga_plan_clases_struc_plantillas.descripcion AS campo2',
                            			'sga_plan_clases_struc_plantillas.detalle AS campo3',
                            			'sga_plan_clases_struc_plantillas.estado AS campo4',
                            			'sga_plan_clases_struc_plantillas.id AS campo5')
						    ->get()
						    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PlanClaseEstrucPlantilla::where('sga_plan_clases_struc_plantillas.estado','Activo')
                    ->select('sga_plan_clases_struc_plantillas.id','sga_plan_clases_struc_plantillas.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_actual()
    {
        return PlanClaseEstrucPlantilla::where('periodo_lectivo_id', PeriodoLectivo::get_actual()->id )
                            ->where('estado','Activo')
                            ->get()
                            ->last();
    }
}
