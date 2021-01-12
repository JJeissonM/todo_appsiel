<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

use App\Matriculas\Matricula;
use App\Matriculas\Estudiante;

use App\Core\Colegio;

class ObservadorEstudiante extends Estudiante
{
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Estudiante', 'Documento', 'Género', 'Fecha nacimiento', 'Dirección', 'Teléfono', 'Email'];

    public static function consultar_registros($nro_registros, $search)
    {
        return Estudiante::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS campo2'),
                'sga_estudiantes.genero AS campo3',
                'sga_estudiantes.fecha_nacimiento AS campo4',
                'core_terceros.direccion1 AS campo5',
                'core_terceros.telefono1 AS campo6',
                'core_terceros.email AS campo7',
                'sga_estudiantes.id AS campo8'
            )->where("sga_estudiantes.genero", "LIKE", "%$search%")
            ->orWhere("sga_estudiantes.fecha_nacimiento", "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_tipos_docs_id.abreviatura,' ',core_terceros.numero_identificacion)"), "LIKE", "%$search%")
            ->orWhere("core_terceros.direccion1", "LIKE", "%$search%")
            ->orWhere("core_terceros.email", "LIKE", "%$search%")
            ->orderBy('sga_estudiantes.id', 'desc')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Estudiante::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ESTUDIANTE'),
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS NÚMERO_DE_IDENTIFICACIÓN'),
                'sga_estudiantes.genero AS GENERO',
                'sga_estudiantes.fecha_nacimiento AS FECHA_DE_NACIMIENTO',
                'core_terceros.direccion1 AS DIRECCIÓN',
                'core_terceros.telefono1 AS TELEFONO',
                'core_terceros.email AS EMAIL'
            )->where("sga_estudiantes.genero", "LIKE", "%$search%")
            ->orWhere("sga_estudiantes.fecha_nacimiento", "LIKE", "%$search%")
            ->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_tipos_docs_id.abreviatura,' ',core_terceros.numero_identificacion)"), "LIKE", "%$search%")
            ->orWhere("core_terceros.direccion1", "LIKE", "%$search%")
            ->orWhere("core_terceros.email", "LIKE", "%$search%")
            ->orderBy('sga_estudiantes.id', 'desc')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ESTUDIANTES EN OBSERVADOR";
    }
}
