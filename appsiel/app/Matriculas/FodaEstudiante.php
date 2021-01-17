<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;

class FodaEstudiante extends Model
{
    protected $table = 'sga_foda_estudiantes';

    protected $fillable = ['id_estudiante','fecha_novedad','tipo_caracteristica','descripcion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Fecha novedad', 'Tipo característica', 'Descripción'];

    public static function consultar_registros($nro_registros, $search)
    {
        return FodaEstudiante::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_foda_estudiantes.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                'sga_foda_estudiantes.fecha_novedad AS campo2',
                'sga_foda_estudiantes.tipo_caracteristica AS campo3',
                'sga_foda_estudiantes.descripcion AS campo4',
                'sga_foda_estudiantes.id AS campo5'
            )->where("sga_foda_estudiantes.fecha_novedad", "LIKE", "%$search%")
            ->orWhere("sga_foda_estudiantes.tipo_caracteristica", "LIKE", "%$search%")
            ->orWhere("sga_foda_estudiantes.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orderBy('sga_foda_estudiantes.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = FodaEstudiante::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_foda_estudiantes.id_estudiante')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTES'),
                'sga_foda_estudiantes.fecha_novedad AS FECHA',
                'sga_foda_estudiantes.tipo_caracteristica AS TIPO_CARACTERISTICA',
                'sga_foda_estudiantes.descripcion AS DESCRIPCIÓN'
            )->where("sga_foda_estudiantes.fecha_novedad", "LIKE", "%$search%")
            ->orWhere("sga_foda_estudiantes.tipo_caracteristica", "LIKE", "%$search%")
            ->orWhere("sga_foda_estudiantes.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orderBy('sga_foda_estudiantes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO ANALISIS FODA - OBSERVADOR";
    }

    public static function get_foda_un_estudiante( $estudiante_id )
    {
        return FodaEstudiante::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_foda_estudiantes.id_estudiante')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                    ->where('sga_foda_estudiantes.id_estudiante',$estudiante_id)
                    ->select(
                        DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1' ),
                        'sga_foda_estudiantes.fecha_novedad AS campo2',
                        'sga_foda_estudiantes.tipo_caracteristica AS campo3',
                        'sga_foda_estudiantes.descripcion AS campo4',
                        'sga_foda_estudiantes.id AS campo5')
                    ->get()
                    ->toArray();
    }
}
