<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use App\Matriculas\PeriodoLectivo;
use App\AcademicoDocente\PlanClaseEstrucElemento;

use DB;

class PlanClaseEstrucPlantilla extends Model
{
    protected $table = 'sga_plan_clases_struc_plantillas';

    // tipo_plantilla: { planeador | guia_academica }
    protected $fillable = ['periodo_lectivo_id', 'tipo_plantilla', 'descripcion', 'detalle', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Año Lectivo', 'Descripción', 'Detalle', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila","eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return PlanClaseEstrucPlantilla::leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_plan_clases_struc_plantillas.periodo_lectivo_id')
            ->select(
                'sga_periodos_lectivos.descripcion AS campo1',
                'sga_plan_clases_struc_plantillas.descripcion AS campo2',
                'sga_plan_clases_struc_plantillas.detalle AS campo3',
                'sga_plan_clases_struc_plantillas.estado AS campo4',
                'sga_plan_clases_struc_plantillas.id AS campo5'
            )
            ->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_struc_plantillas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_struc_plantillas.detalle", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_struc_plantillas.estado", "LIKE", "%$search%")
            ->orderBy('sga_plan_clases_struc_plantillas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = PlanClaseEstrucPlantilla::leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_plan_clases_struc_plantillas.periodo_lectivo_id')
            ->select(
                'sga_periodos_lectivos.descripcion AS AÑO_LECTIVO',
                'sga_plan_clases_struc_plantillas.descripcion AS DESCRIPCIÓN',
                'sga_plan_clases_struc_plantillas.detalle AS DETALLE',
                'sga_plan_clases_struc_plantillas.estado AS ESTADO'
            )
            ->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_struc_plantillas.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_struc_plantillas.detalle", "LIKE", "%$search%")
            ->orWhere("sga_plan_clases_struc_plantillas.estado", "LIKE", "%$search%")
            ->orderBy('sga_plan_clases_struc_plantillas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PLANTILLAS DE PLANES DE CLASES";
    }

    public static function opciones_campo_select()
    {
        $opciones = PlanClaseEstrucPlantilla::where('sga_plan_clases_struc_plantillas.estado', 'Activo')
            ->select('sga_plan_clases_struc_plantillas.id', 'sga_plan_clases_struc_plantillas.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_actual($periodo_lectivo_id = null)
    {
        if (is_null($periodo_lectivo_id)) {
            $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;
        }

        return PlanClaseEstrucPlantilla::where('periodo_lectivo_id', $periodo_lectivo_id)
            ->where('estado', 'Activo')
            ->where('tipo_plantilla', 'planeador')
            ->get()
            ->last();
    }

    public function elementos()
    {
        return $this->hasMany(PlanClaseEstrucElemento::class, 'plantilla_plan_clases_id');
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"sga_plan_clases_encabezados",
                                    "llave_foranea":"plantilla_plan_clases_id",
                                    "mensaje":"Plantilla ya tiene registros en planes de clases y/o guías académicas."
                                }
                        }';
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
