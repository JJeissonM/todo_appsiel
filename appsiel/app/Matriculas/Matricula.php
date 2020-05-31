<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;
use App\Matriculas\Estudiante;

use Auth;
use DB;
use App\Matriculas\Curso;

class Matricula extends Model
{
    protected $table = 'sga_matriculas';

    protected $fillable = ['periodo_lectivo_id','id_colegio','codigo','fecha_matricula','id_estudiante','curso_id','cedula_acudiente','acudiente','telefono_acudiente','email_acudiente','requisitos','estado'];

    public $encabezado_tabla = [ 'Código', 'Fecha matricula', 'Año lectivo', 'Nombres', 'Apellidos', 'Doc. Identidad', 'Email/Usuario', 'Acudiente', 'Curso', 'Estado', 'Acción'];

    public static function consultar_registros()
    {
        return Matricula::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                            ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
                            ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_matriculas.periodo_lectivo_id')
                            ->select('sga_matriculas.codigo AS campo1',
                                    'sga_matriculas.fecha_matricula AS campo2',
                                    'sga_periodos_lectivos.descripcion AS campo3',
                                    DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo4' ),
                                    DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2) AS campo5' ),
                                    'core_terceros.numero_identificacion AS campo6',
                                    'core_terceros.email AS campo7',
                                    'sga_matriculas.acudiente AS campo8',
                                    'sga_cursos.descripcion AS campo9',
                                    'sga_matriculas.estado AS campo10',
                                    'sga_matriculas.id AS campo11')
                            ->get()
                            ->toArray();
    }
	
	/**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id_estudiante' => 'int',
    ];
	
	/**
     * Obtener el estudiante de una matricula.
     */
    public function estudiante()
    {
        return $this->belongsTo('App\Matriculas\Estudiante','id_estudiante');
    }

    public static function estudiantes_matriculados( $curso_id, $periodo_lectivo_id, $estado_matricula )
    {
        $array_wheres = [ ['sga_matriculas.id' ,'>', 0] ];

        if ( $curso_id != null ) {
            $array_wheres = array_merge($array_wheres, ['sga_matriculas.curso_id' => $curso_id]);
        }

        if ( $periodo_lectivo_id != null ) {
            $array_wheres = array_merge($array_wheres, ['sga_matriculas.periodo_lectivo_id' => $periodo_lectivo_id]);
        }

        if ( $estado_matricula != null ) {
            $array_wheres = array_merge($array_wheres, ['sga_matriculas.estado' => $estado_matricula ]);
        }

        return Matricula::where($array_wheres)
                    ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
                    ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                    ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
                    ->select(
                            DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo' ),
                            'sga_matriculas.id AS matricula_id',
                            'sga_matriculas.codigo',
                            'sga_matriculas.id_colegio',
                            'sga_matriculas.id_estudiante',
                            'sga_matriculas.id_estudiante AS id',
                            'sga_matriculas.acudiente',
                            'sga_matriculas.curso_id',
                            'sga_cursos.descripcion AS curso_descripcion',
                            'sga_estudiantes.genero',
                            'sga_estudiantes.imagen',
                            'sga_estudiantes.fecha_nacimiento',
                            DB::raw( 'CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS tipo_y_numero_documento_identidad' ),
                            'core_terceros.nombre1',
                            'core_terceros.otros_nombres',
                            'core_terceros.apellido1',
                            'core_terceros.apellido2',
                            'core_terceros.id_tipo_documento_id',
                            'core_terceros.numero_identificacion',
                            'core_terceros.direccion1',
                            'core_terceros.barrio',
                            'core_terceros.telefono1',
                            'core_terceros.email')
                    ->OrderBy('core_terceros.apellido1', 'ASC')
                    ->get();
    }

    public static function get_matriculas_un_estudiante( $estudiante_id, $estado = null )
    {
        $array_wheres = [ ['sga_matriculas.id_estudiante',$estudiante_id] ];

        if ( $estado != null ) {
            $array_wheres = array_merge( $array_wheres, ['sga_matriculas.estado' => $estado ] );
        }

        return Matricula::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                    ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
                    ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
                    ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_matriculas.periodo_lectivo_id')
                    ->where( $array_wheres )
                    ->select('sga_matriculas.codigo',
                            'sga_matriculas.id_estudiante',
                            'sga_matriculas.periodo_lectivo_id',
                            'sga_periodos_lectivos.descripcion',
                            'sga_matriculas.fecha_matricula',
                            DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_estudiante' ),
                            'sga_matriculas.cedula_acudiente',
                            'sga_matriculas.acudiente',
                            'sga_cursos.descripcion AS nombre_curso',
                            'sga_cursos.id AS curso_id',
                            'sga_matriculas.estado',
                            'sga_matriculas.requisitos',
                            'sga_matriculas.id')
                    ->get();
    }

    public static function get_matricula_activa_un_estudiante( $estudiante_id )
    {
        return Matricula::get_matriculas_un_estudiante( $estudiante_id, 'Activo' )->last();
    }

    public static function get_matricula_periodo_lectivo_un_estudiante( $estudiante_id, $periodo_lectivo_id )
    {
        $array_wheres = [];

        $array_wheres = array_merge($array_wheres, ['periodo_lectivo_id' => $periodo_lectivo_id ]);
        $array_wheres = array_merge($array_wheres, ['id_estudiante' => $estudiante_id ]);
        $array_wheres = array_merge($array_wheres, ['estado' => 'Activo' ]);

        return Matricula::where( $array_wheres )->get()->first();
    }

    public static function inactivar( $matricula_id )
    {
        Matricula::find( $matricula_id )->update( [ 'estado' => 'Inactivo' ] );
    }

    public static function get_nombre_curso($codigo)
    {
        $vec = Matricula::where('sga_matriculas.codigo',$codigo)
                    ->leftJoin('sga_cursos','sga_cursos.id','=','sga_matriculas.curso_id')->select('sga_cursos.descripcion')->get();
        return $vec[0]->descripcion;
    }


    public static function get_registro_impresion($id)
    {  
        return Matricula::leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_matriculas.id_estudiante')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                    ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
                    ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_matriculas.curso_id')
                    ->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_matriculas.periodo_lectivo_id')
                    ->where('sga_matriculas.id',$id)
                    ->select('sga_matriculas.codigo',
                            'sga_matriculas.id_estudiante',
                            'sga_matriculas.periodo_lectivo_id',
                            'sga_periodos_lectivos.descripcion',
                            'sga_matriculas.fecha_matricula',
                            DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_estudiante' ),
                            'core_terceros.numero_identificacion',
                            'sga_estudiantes.core_tercero_id',
                            'sga_matriculas.cedula_acudiente',
                            'sga_matriculas.acudiente',
                            'sga_cursos.descripcion AS nombre_curso',
                            'sga_matriculas.estado',
                            'sga_matriculas.requisitos',
                            'sga_matriculas.id')
                    ->get()
                    ->first();
    }

    
    public static function get_registros_select_hijo($id_select_padre)
    {
        $cursos = Curso::where('sga_grado_id',$id_select_padre)->get();

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($cursos as $campo) {
            $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }
	
}
