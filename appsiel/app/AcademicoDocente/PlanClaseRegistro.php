<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

class PlanClaseRegistro extends Model
{
    protected $table = 'sga_plan_clases_registros';

    protected $fillable = ['plan_clase_encabezado_id', 'plan_clase_estruc_elemento_id', 'contenido', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Plan de clases', 'Elemento', 'Contenido', 'Estado'];
    
    public function elemento_plantilla()
    {
        return $this->belongsTo(PlanClaseEstrucElemento::class, 'plan_clase_estruc_elemento_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return PlanClaseRegistro::select(
            'sga_plan_clases_registros.plan_clase_encabezado_id AS campo1',
            'sga_plan_clases_registros.plan_clase_estruc_elemento_id AS campo2',
            'sga_plan_clases_registros.contenido AS campo3',
            'sga_plan_clases_registros.estado AS campo4',
            'sga_plan_clases_registros.id AS campo5'
        )
            ->where("sga_plan_clases_registros.plan_clase_encabezado_id", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_registros.plan_clase_estruc_elemento_id", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_registros.contenido", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_registros.estado", "LIKE", "%$search%")
            ->orderBy('sga_plan_clases_registros.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = PlanClaseRegistro::select(
            'sga_plan_clases_registros.plan_clase_encabezado_id AS PLAN_DE_CLASE',
            'sga_plan_clases_registros.plan_clase_estruc_elemento_id AS ELEMENTO',
            'sga_plan_clases_registros.contenido AS CONTENIDO',
            'sga_plan_clases_registros.estado AS ESTADO'
        )
            ->where("sga_plan_clases_registros.plan_clase_encabezado_id", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_registros.plan_clase_estruc_elemento_id", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_registros.contenido", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_registros.estado", "LIKE", "%$search%")
            ->orderBy('sga_plan_clases_registros.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE REGISTROS DE PLANES DE CLASES";
    }


    public static function opciones_campo_select()
    {
        $opciones = PlanClaseRegistro::where('sga_plan_clases_registros.estado', 'Activo')
            ->select('sga_plan_clases_registros.id', 'sga_plan_clases_registros.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_registros_impresion($plan_clase_encabezado_id)
    {
        return PlanClaseRegistro::leftJoin('sga_plan_clases_struc_elementos', 'sga_plan_clases_struc_elementos.id', '=', 'sga_plan_clases_registros.plan_clase_estruc_elemento_id')
            ->where('sga_plan_clases_registros.plan_clase_encabezado_id', $plan_clase_encabezado_id)
            ->where('sga_plan_clases_struc_elementos.estado', 'Activo')
            ->select(
                'sga_plan_clases_struc_elementos.descripcion AS elemento_descripcion',
                'sga_plan_clases_struc_elementos.id AS elemento_id',
                'sga_plan_clases_registros.plan_clase_encabezado_id',
                'sga_plan_clases_registros.contenido',
                'sga_plan_clases_registros.estado',
                'sga_plan_clases_registros.id'
            )
            ->orderBy('orden')
            ->get();
    }

    public static function get_registros_impresion_guia($plan_clase_encabezado_id)
    {
        return PlanClaseRegistro::where('sga_plan_clases_registros.plan_clase_encabezado_id', $plan_clase_encabezado_id)
            ->select(
                'sga_plan_clases_registros.plan_clase_encabezado_id',
                'sga_plan_clases_registros.contenido',
                'sga_plan_clases_registros.estado',
                'sga_plan_clases_registros.id'
            )
            ->get();
    }
}
