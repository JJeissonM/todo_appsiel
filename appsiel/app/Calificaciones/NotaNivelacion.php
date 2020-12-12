<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

class NotaNivelacion extends Model
{
    protected $table = 'sga_notas_nivelaciones';
	
	protected $fillable = [ 'colegio_id', 'matricula_id', 'periodo_id', 'curso_id', 'asignatura_id', 'estudiante_id', 'calificacion', 'observacion', 'creado_por', 'modificado_por' ];	

	public $encabezado_tabla = [ 'Estudiante', 'Año lectivo', 'Curso', 'Periodo', 'Asignatura', 'Calificación de nivelación', 'Observaciones', 'Acción'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

	public function periodo()
	{
		return $this->belongsTo( 'App\Calificaciones\Periodo', 'periodo_id' );
	}

	public function curso()
	{
		return $this->belongsTo( 'App\Matriculas\Curso', 'curso_id' );
	}

	public function asignatura()
	{
		return $this->belongsTo( 'App\Calificaciones\Asignatura', 'asignatura_id' );
	}

	public function estudiante()
	{
		return $this->belongsTo( 'App\Matriculas\Estudiante', 'estudiante_id' );
	}

	public static function consultar_registros()
	{
	    return NotaNivelacion::leftJoin('sga_estudiantes','sga_estudiantes.id','=','sga_notas_nivelaciones.estudiante_id')
	    				->leftJoin('core_terceros','core_terceros.id','=','sga_estudiantes.core_tercero_id')
	    				->leftJoin('sga_cursos','sga_cursos.id','=','sga_notas_nivelaciones.curso_id')
	    				->leftJoin('sga_periodos','sga_periodos.id','=','sga_notas_nivelaciones.periodo_id')
            			->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
	    				->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_notas_nivelaciones.asignatura_id')
	    				->select(
	    						'core_terceros.descripcion AS campo1',
	    						'sga_periodos_lectivos.descripcion AS campo2',
	    						'sga_cursos.descripcion AS campo3',
	    						'sga_periodos.descripcion AS campo4',
	    						'sga_asignaturas.descripcion AS campo5',
	    						'sga_notas_nivelaciones.calificacion AS campo6',
	    						'sga_notas_nivelaciones.observacion AS campo7',
	    						'sga_notas_nivelaciones.id AS campo8')
					    ->get()
					    ->toArray();
	}

    public static function get_registros( $curso_id, $asignatura_id )
    {
        $array_wheres = [];

        if ( $curso_id != null ) {
            $array_wheres = array_merge($array_wheres, ['sga_notas_nivelaciones.curso_id' => $curso_id]);
        }

        if ( $asignatura_id != null ) {
            $array_wheres = array_merge( $array_wheres, ['sga_notas_nivelaciones.asignatura_id' => $asignatura_id] );
        }

        return NotaNivelacion::where($array_wheres)
                        ->leftJoin('sga_estudiantes','sga_estudiantes.id','=','sga_notas_nivelaciones.estudiante_id')
	    				->leftJoin('core_terceros','core_terceros.id','=','sga_estudiantes.core_tercero_id')
	    				->leftJoin('sga_cursos','sga_cursos.id','=','sga_notas_nivelaciones.curso_id')
	    				->leftJoin('sga_periodos','sga_periodos.id','=','sga_notas_nivelaciones.periodo_id')
            			->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
	    				->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_notas_nivelaciones.asignatura_id')
	    				->select(
	    						'core_terceros.descripcion AS campo1',
	    						'sga_periodos_lectivos.descripcion AS campo2',
	    						'sga_cursos.descripcion AS campo3',
	    						'sga_periodos.descripcion AS campo4',
	    						'sga_asignaturas.descripcion AS campo5',
	    						'sga_notas_nivelaciones.calificacion AS campo6',
	    						'sga_notas_nivelaciones.observacion AS campo7',
	    						'sga_notas_nivelaciones.id AS campo8')
                        ->get()
                        ->toArray();
    }
}
