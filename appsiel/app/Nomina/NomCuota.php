<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

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

		$registros = NomCuota::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_cuotas.core_tercero_id')
			->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_cuotas.nom_concepto_id')
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
			)
			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_conceptos.descripcion", "LIKE", "%$search%")
			->orWhere("nom_cuotas.fecha_inicio", "LIKE", "%$search%")
			->orWhere("nom_cuotas.valor_cuota", "LIKE", "%$search%")
			->orWhere("nom_cuotas.tope_maximo", "LIKE", "%$search%")
			->orWhere("nom_cuotas.valor_acumulado", "LIKE", "%$search%")
			->orWhere("nom_cuotas.estado", "LIKE", "%$search%")
			->orWhere("nom_cuotas.detalle", "LIKE", "%$search%")
			->orderBy('nom_cuotas.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	public static function sqlString($search)
	{
		$string = NomCuota::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_cuotas.core_tercero_id')
			->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_cuotas.nom_concepto_id')
			->select(
				'core_terceros.descripcion AS EMPLEADO',
				'nom_conceptos.descripcion AS CONCEPTO',
				'nom_cuotas.fecha_inicio AS FECHA_INICIO',
				'nom_cuotas.valor_cuota AS VALOR_CUOTA',
				'nom_cuotas.tope_maximo AS TOPE_MÁXIMO',
				'nom_cuotas.valor_acumulado AS VALOR_ACUMULADO',
				'nom_cuotas.estado AS ESTADO',
				'nom_cuotas.detalle AS DETALLE'
			)
			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_conceptos.descripcion", "LIKE", "%$search%")
			->orWhere("nom_cuotas.fecha_inicio", "LIKE", "%$search%")
			->orWhere("nom_cuotas.valor_cuota", "LIKE", "%$search%")
			->orWhere("nom_cuotas.tope_maximo", "LIKE", "%$search%")
			->orWhere("nom_cuotas.valor_acumulado", "LIKE", "%$search%")
			->orWhere("nom_cuotas.estado", "LIKE", "%$search%")
			->orWhere("nom_cuotas.detalle", "LIKE", "%$search%")
			->orderBy('nom_cuotas.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
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
