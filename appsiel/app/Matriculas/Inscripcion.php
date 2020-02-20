<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class Inscripcion extends Model
{
    protected $table = 'sga_inscripciones';

    protected $fillable = ['codigo','fecha','sga_grado_id','core_tercero_id','genero','fecha_nacimiento','ciudad_nacimiento','origen','enterado_por','observacion','acudiente','colegio_anterior','creado_por','modificado_por'];

    public $encabezado_tabla = ['Candidato','Identificación','Código inscripción','Fecha','Grado','Observación','Acción'];

    public static function consultar_registros()
    {
    	$select_raw = 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1';

    	$select_raw2 = 'CONCAT(core_tipos_docs_id.abreviatura," ", core_terceros.numero_identificacion ) AS campo2';

        $registros = Inscripcion::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_inscripciones.core_tercero_id')
                    ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_inscripciones.sga_grado_id')
                    ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
                    ->select(DB::raw($select_raw), 
                            DB::raw($select_raw2),
                            'sga_inscripciones.codigo AS campo3',
                            'sga_inscripciones.fecha AS campo4',
                            'sga_grados.descripcion AS campo5',
                            'sga_inscripciones.observacion AS campo6',
                            'sga_inscripciones.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function get_opciones_select_inscritos()
    {
        $select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero';

        $registros = Inscripcion::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_inscripciones.core_tercero_id')
                    ->select('sga_inscripciones.id AS id_inscripcion', DB::raw($select_raw))
                    ->get();

        $candidatos['']='';
        foreach ($registros as $opcion){
            $candidatos[$opcion->id_inscripcion] = $opcion->tercero;
        }

        return $candidatos;
    }

    public static function get_registro_impresion($id)
    {  
        return Inscripcion::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_inscripciones.core_tercero_id')
                    ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
                    ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_inscripciones.sga_grado_id')
                    ->where('sga_inscripciones.id',$id)
                    ->select( 'sga_inscripciones.id',
                            'sga_inscripciones.codigo',
                            'sga_inscripciones.core_tercero_id',
                            'sga_inscripciones.fecha',
                            DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo' ),
                            DB::raw( 'CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS tipo_y_numero_documento_identidad' ),
                            'sga_inscripciones.acudiente',
                            'sga_grados.descripcion AS nombre_grado',
                            'sga_inscripciones.enterado_por',
                            'sga_inscripciones.genero',
                            'sga_inscripciones.ciudad_nacimiento',
                            'sga_inscripciones.fecha_nacimiento',
                            'sga_inscripciones.origen',
                            'sga_inscripciones.observacion',
                            'sga_inscripciones.creado_por',
                            'sga_inscripciones.colegio_anterior',
                            'core_terceros.nombre1',
                            'core_terceros.otros_nombres',
                            'core_terceros.apellido1',
                            'core_terceros.apellido2',
                            'core_terceros.telefono1',
                            'core_terceros.id_tipo_documento_id',
                            'core_terceros.numero_identificacion',
                            'core_terceros.direccion1',
                            'core_terceros.barrio',
                            'core_terceros.email')
                    ->get()
                    ->first();
    }
}
