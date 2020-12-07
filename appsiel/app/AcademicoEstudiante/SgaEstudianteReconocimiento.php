<?php

namespace App\AcademicoEstudiante;

use Illuminate\Database\Eloquent\Model;

class SgaEstudianteReconocimiento extends Model
{
    protected $fillable = [ 'estudiante_id', 'curso_id', 'periodo_lectivo_id', 'descripcion', 'resumen', 'archivo_adjunto', 'estado'];
	
    public $encabezado_tabla = [ 'Año lectivo', 'Estudiante', 'Curso', 'Descripción', 'Resumen', 'Estado', 'Acción' ];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public function estudiante()
    {
    	return $this->belongsTo( 'App\Matriculas\Estudiante', 'estudiante_id' );
    }

    public function periodo_lectivo()
    {
    	return $this->belongsTo( 'App\Matriculas\PeriodoLectivo', 'periodo_lectivo_id' );
    }

    public function curso()
    {
    	return $this->belongsTo( 'App\Matriculas\Curso', 'curso_id' );
    }
	
	public static function consultar_registros()
	{
	    return SgaEstudianteReconocimiento::leftJoin( 'sga_periodos_lectivos', 'sga_periodos_lectivos.id','=', 'sga_estudiante_reconocimientos.periodo_lectivo_id' )
                                    ->leftJoin( 'sga_cursos', 'sga_cursos.id', '=', 'sga_estudiante_reconocimientos.curso_id')
                                    ->leftJoin( 'sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_estudiante_reconocimientos.estudiante_id')
                                    ->leftJoin( 'core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                                    ->select(
		                                        'sga_periodos_lectivos.descripcion AS campo1',
		                                        'core_terceros.descripcion AS campo2',
		                                        'sga_cursos.descripcion AS campo3',
		                                        'sga_estudiante_reconocimientos.descripcion AS campo4',
		                                        'sga_estudiante_reconocimientos.resumen AS campo5',
		                                        'sga_estudiante_reconocimientos.estado AS campo6',
		                                        'sga_estudiante_reconocimientos.id AS campo7'
		                                    )
                            	    ->get()
                            	    ->toArray();
	}
}
