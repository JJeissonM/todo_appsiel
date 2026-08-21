<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class NomCuota extends Model
{
	//protected $table = 'nom_cuotas';
	protected $fillable = ['core_tercero_id', 'nom_concepto_id', 'fecha_inicio', 'periodicidad_mensual', 'valor_cuota', 'tope_maximo', 'valor_acumulado', 'estado', 'detalle'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empleado', 'Concepto', 'Fecha inicio', 'Valor cuota', 'Tope Máximo', 'Valor acumulado', 'Estado', 'Detalle'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","cambiar_estado":"a_i/id_fila","eliminar":"web_eliminar/id_fila"}';

	// El archivo js debe estar en la carpeta public
	public $archivo_js = 'assets/js/nom_cuotas.js';

	public static function consultar_registros($nro_registros, $search)
	{
		return self::aplicar_filtros_index(self::query_listado()
			->select(
				'core_terceros.descripcion AS campo1',
				'nom_conceptos.descripcion AS campo2',
				'nom_cuotas.fecha_inicio AS campo3',
				'nom_cuotas.valor_cuota AS campo4',
				'nom_cuotas.tope_maximo AS campo5',
				'nom_cuotas.valor_acumulado AS campo6',
				'nom_cuotas.estado AS campo7',
				'nom_cuotas.detalle AS campo8',
				'nom_cuotas.id AS campo9'
			), $search)
			->orderBy('nom_cuotas.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$query = self::aplicar_filtros_index(self::query_listado()
			->select(
				'core_terceros.descripcion AS EMPLEADO',
				'nom_conceptos.descripcion AS CONCEPTO',
				'nom_cuotas.fecha_inicio AS FECHA_INICIO',
				'nom_cuotas.valor_cuota AS VALOR_CUOTA',
				'nom_cuotas.tope_maximo AS TOPE_MÁXIMO',
				'nom_cuotas.valor_acumulado AS VALOR_ACUMULADO',
				'nom_cuotas.estado AS ESTADO',
				'nom_cuotas.detalle AS DETALLE'
			), $search)
			->orderBy('nom_cuotas.created_at', 'DESC');

		return self::sql_con_bindings($query);
	}

	/**
	 * Configuracion del formulario de filtros que se muestra en el index generico.
	 */
	public static function get_filtros_avanzados_index()
	{
		$empleados = self::opciones_filtro('core_terceros.id', 'core_terceros.descripcion');
		$conceptos = self::opciones_filtro('nom_conceptos.id', 'nom_conceptos.descripcion');
		$estados = self::opciones_filtro('nom_cuotas.estado', 'nom_cuotas.estado');

		return [
			'filtro_empleado' => ['label' => 'Empleado', 'type' => 'combobox', 'options' => ['' => 'Todos'] + $empleados],
			'filtro_concepto' => ['label' => 'Concepto', 'type' => 'combobox', 'options' => ['' => 'Todos'] + $conceptos],
			'filtro_fecha_inicio' => ['label' => 'Fecha inicio', 'type' => 'date'],
			'filtro_estado' => ['label' => 'Estado', 'type' => 'combobox', 'options' => ['' => 'Todos'] + $estados]
		];
	}

	protected static function query_listado()
	{
		$query = NomCuota::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_cuotas.core_tercero_id')
			->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_cuotas.nom_concepto_id');

		if (Auth::check()) {
			$query->where('core_terceros.core_empresa_id', Auth::user()->empresa_id);
		}

		return $query;
	}

	protected static function aplicar_filtros_index($query, $search)
	{
		if ($search !== '') {
			$query->where(function ($subquery) use ($search) {
				$like = '%' . $search . '%';
				$subquery->where('core_terceros.descripcion', 'LIKE', $like)
					->orWhere('nom_conceptos.descripcion', 'LIKE', $like)
					->orWhere('nom_cuotas.fecha_inicio', 'LIKE', $like)
					->orWhere('nom_cuotas.valor_cuota', 'LIKE', $like)
					->orWhere('nom_cuotas.tope_maximo', 'LIKE', $like)
					->orWhere('nom_cuotas.valor_acumulado', 'LIKE', $like)
					->orWhere('nom_cuotas.estado', 'LIKE', $like)
					->orWhere('nom_cuotas.detalle', 'LIKE', $like)
					->orWhere('nom_cuotas.id', 'LIKE', $like);
			});
		}

		$filtros = [
			'filtro_empleado' => 'nom_cuotas.core_tercero_id',
			'filtro_concepto' => 'nom_cuotas.nom_concepto_id',
			'filtro_fecha_inicio' => 'nom_cuotas.fecha_inicio',
			'filtro_estado' => 'nom_cuotas.estado'
		];

		foreach ($filtros as $parametro => $columna) {
			$valor = trim((string) Input::get($parametro, ''));
			if ($valor !== '') {
				$query->where($columna, $valor);
			}
		}

		return $query;
	}

	protected static function opciones_filtro($id, $descripcion)
	{
		return self::query_listado()
			->whereNotNull($id)
			->select($id, $descripcion)
			->distinct()
			->orderBy($descripcion)
			->lists($descripcion, $id)
			->all();
	}

	protected static function sql_con_bindings($query)
	{
		$sql = $query->toSql();
		$pdo = DB::connection()->getPdo();

		foreach ($query->getBindings() as $binding) {
			$valor = is_numeric($binding) ? $binding : $pdo->quote($binding);
			$sql = preg_replace('/\?/', $valor, $sql, 1);
		}

		return $sql;
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE CUOTAS";
	}

	public function validar_eliminacion($id)
	{
		$tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_doc_registros",
                                    "llave_foranea":"nom_cuota_id",
                                    "mensaje":"Tienes registros en documentos de nómina."
                                }
                        }';
		$tablas = json_decode($tablas_relacionadas);
		foreach ($tablas as $una_tabla) {
			$registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

			if (!empty($registro)) {
				return $una_tabla->mensaje;
			}
		}

		return 'ok';
	}
}
