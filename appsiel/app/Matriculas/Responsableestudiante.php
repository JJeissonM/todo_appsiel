<?php

namespace App\Matriculas;

use App\Core\Tercero;
use App\Matriculas\Tiporesponsable;
use App\Matriculas\Estudiante;
use Illuminate\Database\Eloquent\Model;

use DB;

class Responsableestudiante extends Model
{
    protected $table = 'sga_responsableestudiantes';

    protected $fillable = ['id', 'direccion_trabajo', 'telefono_trabajo', 'puesto_trabajo', 'empresa_labora', 'jefe_inmediato', 'telefono_jefe', 'descripcion_trabajador_independiente', 'ocupacion', 'tiporesponsable_id', 'estudiante_id', 'tercero_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Responsable', 'Tipo', 'Dir.', 'Teléfono', 'Ocupación'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function tiporesponsable()
    {
        return $this->belongsTo(Tiporesponsable::class);
    }

    public function tercero()
    {
        return $this->belongsTo(Tercero::class);
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return Responsableestudiante::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_responsableestudiantes.estudiante_id')
                            ->leftJoin('core_terceros AS Estudiantes', 'Estudiantes.id', '=', 'sga_estudiantes.core_tercero_id')
                            ->leftJoin('core_terceros AS Responsables', 'Responsables.id', '=', 'sga_responsableestudiantes.tercero_id')
                            ->leftJoin('sga_tiporesponsables', 'sga_tiporesponsables.id', '=', 'sga_responsableestudiantes.tiporesponsable_id')
                            ->select(
                                        DB::raw('CONCAT(Estudiantes.numero_identificacion," - ",Estudiantes.apellido1," ",Estudiantes.apellido2," ",Estudiantes.nombre1," ",Estudiantes.otros_nombres) AS campo1'),
                                        DB::raw('CONCAT(Responsables.numero_identificacion," - ",Responsables.apellido1," ",Responsables.apellido2," ",Responsables.nombre1," ",Responsables.otros_nombres) AS campo2'),
                                        'sga_tiporesponsables.descripcion AS campo3',
                                        'Responsables.direccion1 AS campo4',
                                        'Responsables.telefono1 AS campo5',
                                        'sga_responsableestudiantes.ocupacion AS campo6',
                                        'sga_responsableestudiantes.id AS campo7'
                                    )
                            ->where(DB::raw('CONCAT(Estudiantes.apellido1," ",Estudiantes.apellido2," ",Estudiantes.nombre1," ",Estudiantes.otros_nombres)'), "LIKE", "%$search%")
                            ->orWhere(DB::raw('CONCAT(Responsables.apellido1," ",Responsables.apellido2," ",Responsables.nombre1," ",Responsables.otros_nombres)'), "LIKE", "%$search%")
                            ->orWhere("sga_tiporesponsables.descripcion", "LIKE", "%$search%")
                            ->orWhere("Responsables.direccion1", "LIKE", "%$search%")
                            ->orWhere("Responsables.telefono1", "LIKE", "%$search%")
                            ->orWhere("sga_responsableestudiantes.ocupacion", "LIKE", "%$search%")
                            ->orderBy('sga_responsableestudiantes.created_at', 'DESC')
                            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Responsableestudiante::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_responsableestudiantes.estudiante_id')
            ->leftJoin('core_terceros AS Estudiantes', 'Estudiantes.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('core_terceros AS Responsables', 'Responsables.id', '=', 'sga_responsableestudiantes.tercero_id')
            ->leftJoin('sga_tiporesponsables', 'sga_tiporesponsables.id', '=', 'sga_responsableestudiantes.tiporesponsable_id')
            ->select(
                        DB::raw('CONCAT(Estudiantes.numero_identificacion," - ",Estudiantes.apellido1," ",Estudiantes.apellido2," ",Estudiantes.nombre1," ",Estudiantes.otros_nombres) AS ESTUDIANTE'),
                        DB::raw('CONCAT(Responsables.numero_identificacion," - ",Responsables.apellido1," ",Responsables.apellido2," ",Responsables.nombre1," ",Responsables.otros_nombres) AS RESPONSABLE'),
                        'sga_tiporesponsables.descripcion AS TIPO',
                        'Responsables.direccion1 AS DIRECCIÓN',
                        'Responsables.telefono1 AS TELÉFONO',
                        'sga_responsableestudiantes.ocupacion AS OCUPACIÓN',
                        'sga_estudiantes.id AS ESTUDIANTE_ID',
                        'Responsables.id AS TERCERO_RESPONSABLE_ID',
                        'Estudiantes.id AS TERCERO_ESTUDIANTE_ID'
                    )
            ->where(DB::raw('CONCAT(Estudiantes.apellido1," ",Estudiantes.apellido2," ",Estudiantes.nombre1," ",Estudiantes.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(Responsables.apellido1," ",Responsables.apellido2," ",Responsables.nombre1," ",Responsables.otros_nombres)'), "LIKE", "%$search%")
            ->orWhere("sga_tiporesponsables.descripcion", "LIKE", "%$search%")
            ->orWhere("Responsables.direccion1", "LIKE", "%$search%")
            ->orWhere("Responsables.telefono1", "LIKE", "%$search%")
            ->orWhere("sga_responsableestudiantes.ocupacion", "LIKE", "%$search%")
            ->orderBy('sga_responsableestudiantes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE FAMILIARES DEL ESTUDIANTE";
    }
}
