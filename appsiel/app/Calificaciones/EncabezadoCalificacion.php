<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Services\CalificacionDefinitivaService;

class EncabezadoCalificacion extends Model
{
	protected $table = 'sga_calificaciones_encabezados';
	
	protected $fillable = ['columna_calificacion', 'label', 'titulo', 'descripcion', 'peso', 'fecha', 'anio', 'periodo_id', 'curso_id', 'asignatura_id', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Columna', 'Agrupación', 'Label', 'Peso (%)', 'Fecha', 'Año lectivo', 'Periodo', 'Curso', 'Asignatura', 'Detalle'];

	public $urls_acciones = '{"eliminar":"web_eliminar/id_fila","otros_enlaces":""}';

	protected static function boot()
	{
		parent::boot();

		static::saved(function ($encabezado) {
			app(CalificacionDefinitivaService::class)->recalcularPorEncabezado($encabezado);
		});

		static::deleted(function ($encabezado) {
			app(CalificacionDefinitivaService::class)->recalcularPorEncabezado($encabezado);
		});
	}

	public function validar_datos_creacion($request, $controller)
	{
		$this->normalizarFechaRequest($request);
		$this->validarUnicidadPorAlcance($request, $controller);
	}

	public function preparar_request_validacion($request, $id = null)
	{
		$this->normalizarFechaRequest($request, $id);
	}

	public function validar_datos_actualizacion($request, $controller, $id)
	{
		$this->normalizarFechaRequest($request, $id);
		$this->validarUnicidadPorAlcance($request, $controller, $id);
	}

	public function get_campos_adicionales_create($listaCampos)
	{
		foreach ($listaCampos as $indice => $campo) {
			if ($campo['name'] === 'fecha') {
				$listaCampos[$indice]['value'] = '';
			}
		}

		return $listaCampos;
	}

	public function validar_duplicacion($registro)
	{
		return 'No se puede duplicar un encabezado porque la columna ya está configurada para el mismo año, periodo, curso y asignatura.';
	}

	public function getReglaUnicidadPorAlcance($request, $id = null)
	{
		$this->normalizarAlcanceRequest($request, $id);

		$valorId = is_null($id) ? 'NULL' : (int)$id;

		return 'unique:' . $this->getTable()
			. ',columna_calificacion,' . $valorId . ',id'
			. ',anio,' . $this->valorCondicionUnicidad($request->input('anio'))
			. ',periodo_id,' . $this->valorCondicionUnicidad($request->input('periodo_id'))
			. ',curso_id,' . $this->valorCondicionUnicidad($request->input('curso_id'))
			. ',asignatura_id,' . $this->valorCondicionUnicidad($request->input('asignatura_id'));
	}

	protected function validarUnicidadPorAlcance($request, $controller, $id = null)
	{
		$this->normalizarAlcanceRequest($request, $id);
		$reglaFecha = $this->esEncabezadoSoloPorPeriodo($request, $id)
			? 'date_format:Y-m-d'
			: 'required|date_format:Y-m-d';
		$pesoMaximo = max(0, 100 - $this->getSumaPesosDelAlcance($request, $id));

		$controller->validate(
			$request,
			[
				'fecha' => $reglaFecha,
				'peso' => 'numeric|min:0|max:' . $pesoMaximo,
				'columna_calificacion' => [
					'required',
					$this->getReglaUnicidadPorAlcance($request, $id)
				]
			],
			[
				'fecha.required' => 'Debe ingresar la fecha de la actividad.',
				'fecha.date_format' => 'La fecha de la actividad debe tener el formato año-mes-día.',
				'peso.numeric' => 'El peso debe ser un valor numérico.',
				'peso.min' => 'El peso no puede ser negativo.',
				'peso.max' => 'La suma de pesos del alcance no puede superar el 100%.',
				'columna_calificacion.unique' => 'La columna ya está configurada para el mismo año, periodo, curso y asignatura.'
			]
		);
	}

	protected function getSumaPesosDelAlcance($request, $id = null)
	{
		$query = self::where('anio', $request->input('anio'))
			->where('periodo_id', $request->input('periodo_id'));

		foreach (['curso_id', 'asignatura_id'] as $campo) {
			if (is_null($request->input($campo))) {
				$query->whereNull($campo);
			} else {
				$query->where($campo, $request->input($campo));
			}
		}

		if (!is_null($id)) {
			$query->where('id', '<>', (int)$id);
		}

		return (float)$query->sum('peso');
	}

	protected function normalizarFechaRequest($request, $id = null)
	{
		$datosEnviados = $request->all();

		if (!array_key_exists('fecha', $datosEnviados)) {
			if (is_null($id)) {
				$request->merge([
					'fecha' => $this->esEncabezadoSoloPorPeriodo($request, $id) ? null : date('Y-m-d')
				]);
				return;
			}

			$registroActual = self::find($id);
			if (!is_null($registroActual)) {
				$request->merge(['fecha' => $registroActual->fecha]);
			}
			return;
		}

		$fecha = trim((string)$datosEnviados['fecha']);
		$request->merge([
			'fecha' => $fecha === '' && $this->esEncabezadoSoloPorPeriodo($request, $id) ? null : $fecha
		]);
	}

	protected function esEncabezadoSoloPorPeriodo($request, $id = null)
	{
		$datosEnviados = $request->all();
		$cursoId = $request->input('curso_id');
		$asignaturaId = $request->input('asignatura_id');

		if (!is_null($id) && (!array_key_exists('curso_id', $datosEnviados) || !array_key_exists('asignatura_id', $datosEnviados))) {
			$registroActual = self::find($id);
			if (!is_null($registroActual)) {
				$cursoId = array_key_exists('curso_id', $datosEnviados) ? $cursoId : $registroActual->curso_id;
				$asignaturaId = array_key_exists('asignatura_id', $datosEnviados) ? $asignaturaId : $registroActual->asignatura_id;
			}
		}

		return ($cursoId === '' || is_null($cursoId))
			&& ($asignaturaId === '' || is_null($asignaturaId));
	}

	protected function normalizarAlcanceRequest($request, $id = null)
	{
		$columna = strtoupper(trim((string)$request->input('columna_calificacion')));
		$valores = ['columna_calificacion' => $columna];
		$datosEnviados = $request->all();
		$registroActual = is_null($id) ? null : self::find($id);

		foreach (['anio', 'periodo_id', 'curso_id', 'asignatura_id'] as $campo) {
			if (!array_key_exists($campo, $datosEnviados) && !is_null($registroActual)) {
				$valorActual = $registroActual->$campo;
				$valores[$campo] = is_null($valorActual) ? null : (int)$valorActual;
				continue;
			}

			$valor = array_key_exists($campo, $datosEnviados) ? $datosEnviados[$campo] : null;
			$valores[$campo] = $valor === '' || is_null($valor) ? null : (int)$valor;
		}

		$request->merge($valores);
	}

	protected function valorCondicionUnicidad($valor)
	{
		return $valor === '' || is_null($valor) ? 'NULL' : (int)$valor;
	}

	public static function consultar_registros($nro_registros, $search)
	{
		return EncabezadoCalificacion::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones_encabezados.periodo_id')
			->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
			->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones_encabezados.curso_id')
			->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones_encabezados.asignatura_id')
			->select(
				'sga_calificaciones_encabezados.columna_calificacion AS campo1',
				'sga_calificaciones_encabezados.titulo AS campo2',
				'sga_calificaciones_encabezados.label AS campo3',
				'sga_calificaciones_encabezados.peso AS campo4',
				'sga_calificaciones_encabezados.fecha AS campo5',
				'sga_periodos_lectivos.descripcion AS campo6',
				'sga_periodos.descripcion AS campo7',
				'sga_cursos.descripcion AS campo8',
				'sga_asignaturas.descripcion AS campo9',
				'sga_calificaciones_encabezados.descripcion AS campo10',
				'sga_calificaciones_encabezados.id AS campo11'
			)->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
			->orWhere("sga_calificaciones_encabezados.fecha", "LIKE", "%$search%")
			->orWhere("sga_calificaciones_encabezados.columna_calificacion", "LIKE", "%$search%")
			->orWhere("sga_calificaciones_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("sga_calificaciones_encabezados.peso", "LIKE", "%$search%")
			->orderBy('sga_calificaciones_encabezados.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = EncabezadoCalificacion::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones_encabezados.periodo_id')
			->leftJoin('sga_periodos_lectivos', 'sga_periodos_lectivos.id', '=', 'sga_periodos.periodo_lectivo_id')
			->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones_encabezados.curso_id')
			->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones_encabezados.asignatura_id')
			->select(
				'sga_calificaciones_encabezados.columna_calificacion AS campo1',
				'sga_calificaciones_encabezados.titulo AS campo2',
				'sga_calificaciones_encabezados.label AS campo3',
				'sga_calificaciones_encabezados.peso AS campo4',
				'sga_calificaciones_encabezados.fecha AS campo5',
				'sga_periodos_lectivos.descripcion AS campo6',
				'sga_periodos.descripcion AS campo7',
				'sga_cursos.descripcion AS campo8',
				'sga_asignaturas.descripcion AS campo9',
				'sga_calificaciones_encabezados.descripcion AS campo10'
			)->where("sga_periodos_lectivos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_periodos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_cursos.descripcion", "LIKE", "%$search%")
			->orWhere("sga_asignaturas.descripcion", "LIKE", "%$search%")
			->orWhere("sga_calificaciones_encabezados.fecha", "LIKE", "%$search%")
			->orWhere("sga_calificaciones_encabezados.columna_calificacion", "LIKE", "%$search%")
			->orWhere("sga_calificaciones_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("sga_calificaciones_encabezados.peso", "LIKE", "%$search%")
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE ENCABEZADO CALIFICACIÓN";
	}

	/*
		$id_select_padre corresponde a curso_id
	*/
	public static function get_registros_select_hijo($id_select_padre)
	{
		$registros = CursoTieneAsignatura::asignaturas_del_curso($id_select_padre, null, null, null);

		$opciones = '<option value="">Seleccionar...</option>';
		foreach ($registros as $campo) {

			$opciones .= '<option value="' . $campo->id . '">' . $campo->descripcion . '</option>';
		}
		return $opciones;
	}
}
