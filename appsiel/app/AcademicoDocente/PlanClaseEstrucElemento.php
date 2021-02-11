<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

class PlanClaseEstrucElemento extends Model
{
	protected $table = 'sga_plan_clases_struc_elementos';
	protected $fillable = ['plantilla_plan_clases_id', 'descripcion', 'orden', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Plan de clases', 'Descripción', 'Orden', 'Estado'];

	public $urls_acciones = '{"cambiar_estado":"a_i/id_fila"}';

	public static function consultar_registros($nro_registros, $search)
	{
		return PlanClaseEstrucElemento::leftJoin('sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id', '=', 'sga_plan_clases_struc_elementos.plantilla_plan_clases_id')
			->select(
				'sga_plan_clases_struc_plantillas.descripcion AS campo1',
				'sga_plan_clases_struc_elementos.descripcion AS campo2',
				'sga_plan_clases_struc_elementos.orden AS campo3',
				'sga_plan_clases_struc_elementos.estado AS campo4',
				'sga_plan_clases_struc_elementos.id AS campo5'
			)
			->where("sga_plan_clases_struc_plantillas.descripcion", "LIKE", "%$search%")
			->orWhere("sga_plan_clases_struc_elementos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_plan_clases_struc_elementos.orden", "LIKE", "%$search%")
			->orWhere("sga_plan_clases_struc_elementos.estado", "LIKE", "%$search%")
			->orderBy('sga_plan_clases_struc_elementos.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = PlanClaseEstrucElemento::leftJoin('sga_plan_clases_struc_plantillas', 'sga_plan_clases_struc_plantillas.id', '=', 'sga_plan_clases_struc_elementos.plantilla_plan_clases_id')
			->select(
				'sga_plan_clases_struc_plantillas.descripcion AS PLAN_DE_CLASES',
				'sga_plan_clases_struc_elementos.descripcion AS DESCRIPCIÓN',
				'sga_plan_clases_struc_elementos.orden AS ORDEN',
				'sga_plan_clases_struc_elementos.estado AS ESTADO'
			)
			->orderBy('sga_plan_clases_struc_elementos.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE SECCIONES DE PLANES DE CLASES";
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
