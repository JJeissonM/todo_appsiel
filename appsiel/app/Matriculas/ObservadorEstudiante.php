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
    public $encabezado_tabla = ['Estudiante','Documento','Género','Fecha nacimiento','Dirección','Teléfono','Email','Acción'];

    public static function consultar_registros()
    {
        return Estudiante::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
                            ->select( 
                                        DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1' ),
                                        DB::raw( 'CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS campo2' ),
                                        'sga_estudiantes.genero AS campo3',
                                        'sga_estudiantes.fecha_nacimiento AS campo4',
                                        'core_terceros.direccion1 AS campo5',
                                        'core_terceros.telefono1 AS campo6',
                                        'core_terceros.email AS campo7',
                                        'sga_estudiantes.id AS campo8')
                            ->orderBy('sga_estudiantes.id','desc')
                            ->get()
                            ->toArray();
    }
}