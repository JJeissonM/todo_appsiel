<?php

namespace App\AcademicoEstudiante;

use Illuminate\Database\Eloquent\Model;

class SgaEstudianteReconocimiento extends Model
{
	protected $fillable = ['estudiante_id', 'curso_id', 'periodo_lectivo_id', 'descripcion', 'resumen', 'archivo_adjunto', 'estado'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Año lectivo', 'Estudiante', 'Curso', 'Descripción', 'Resumen', 'Estado'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

	public function estudiante()
	{
		return $this->belongsTo('App\Matriculas\Estudiante', 'estudiante_id');
	} 

	public function periodo_lectivo()
	{
		return $this->belongsTo('App\Matriculas\PeriodoLectivo', 'periodo_lectivo_id');
	}

	public function curso()
	{
		return $this->belongsTo('App\Matriculas\Curso', 'curso_id');
	}

	public static function consultar_registros($nro_registros, $search)
	{
		return SgaEstudianteReconocimiento::leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_estudiante_reconocimientos.periodo_lectivo_id')
			->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_estudiante_reconocimientos.curso_id')
			->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_estudiante_reconocimientos.estudiante_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
			->select(
				'sga_periodos_lectivos.descripcion AS campo1',
				'core_terceros.descripcion AS campo2',
				'sga_cursos.descripcion AS campo3',
				'sga_estudiante_reconocimientos.descripcion AS campo4',
				'sga_estudiante_reconocimientos.resumen AS campo5',
				'sga_estudiante_reconocimientos.estado AS campo6',
				'sga_estudiante_reconocimientos.id AS campo7'
			)
			->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
			->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_estudiante_reconocimientos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_estudiante_reconocimientos.resumen", "LIKE", "%$search%")
			->orWhere("sga_estudiante_reconocimientos.estado", "LIKE", "%$search%")
			->orderBy('sga_estudiante_reconocimientos.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = SgaEstudianteReconocimiento::leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_estudiante_reconocimientos.periodo_lectivo_id')
		->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_estudiante_reconocimientos.curso_id')
		->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_estudiante_reconocimientos.estudiante_id')
		->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
		->select(
			'sga_periodos_lectivos.descripcion AS PERÍODO',
			'core_terceros.descripcion AS ESTUDIANTE',
			'sga_cursos.descripcion AS CURSO',
			'sga_estudiante_reconocimientos.descripcion AS RECONOCIMIENTO',
			'sga_estudiante_reconocimientos.resumen AS RESUMEN',
			'sga_estudiante_reconocimientos.estado AS ESTADO'
		)
		->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
		->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
		->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
		->orWhere("sga_estudiante_reconocimientos.descripcion", "LIKE", "%$search%")
		->orWhere("sga_estudiante_reconocimientos.resumen", "LIKE", "%$search%")
		->orWhere("sga_estudiante_reconocimientos.estado", "LIKE", "%$search%")
		->orderBy('sga_estudiante_reconocimientos.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE RECONOCIMIENTOS A LOS ESTUDIANTES";
	}
}
