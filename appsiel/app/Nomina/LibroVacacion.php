<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class LibroVacacion extends Model
{
	protected $table = 'nom_libro_vacaciones';

	// novedad_tnl_id se llena desde Programación de vacaciones
	protected $fillable = ['nom_contrato_id', 'nom_doc_encabezado_id', 'novedad_tnl_id', 'periodo_pagado_desde', 'periodo_pagado_hasta', 'periodo_disfrute_vacacion_desde', 'periodo_disfrute_vacacion_hasta', 'dias_pagados', 'dias_compensados', 'dias_disfrutados', 'dias_no_habiles', 'valor_vacaciones'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empleado', 'Doc. Nómina', 'Pagada desde', 'Pagada hasta', 'Disfrutada desde', 'Disfrutada hasta', 'd. pagados', 'd. compensados', 'd. disfrutados', 'd. No háb.', 'Valor'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

	public static function consultar_registros($nro_registros, $search)
	{
		return LibroVacacion::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_libro_vacaciones.nom_contrato_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
			->leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_libro_vacaciones.nom_doc_encabezado_id')
			->select(
				'core_terceros.descripcion AS campo1',
				'nom_doc_encabezados.descripcion AS campo2',
				'nom_libro_vacaciones.periodo_pagado_desde AS campo3',
				'nom_libro_vacaciones.periodo_pagado_hasta AS campo4',
				'nom_libro_vacaciones.periodo_disfrute_vacacion_desde AS campo5',
				'nom_libro_vacaciones.periodo_disfrute_vacacion_hasta AS campo6',
				'nom_libro_vacaciones.dias_pagados AS campo7',
				'nom_libro_vacaciones.dias_compensados AS campo8',
				'nom_libro_vacaciones.dias_disfrutados AS campo9',
				'nom_libro_vacaciones.dias_no_habiles AS campo10',
				'nom_libro_vacaciones.valor_vacaciones AS campo11',
				'nom_libro_vacaciones.id AS campo12'
			)

			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_doc_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.periodo_pagado_desde", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.periodo_pagado_hasta", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.periodo_disfrute_vacacion_desde", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.periodo_disfrute_vacacion_hasta", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.dias_pagados", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.dias_compensados", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.dias_disfrutados", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.dias_no_habiles", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.valor_vacaciones", "LIKE", "%$search%")
			->orderBy('nom_prestamos.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = LibroVacacion::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_libro_vacaciones.nom_contrato_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
			->leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_libro_vacaciones.nom_doc_encabezado_id')
			->select(
				'core_terceros.descripcion AS EMPLEADO',
				'nom_doc_encabezados.descripcion AS DOC_NÓMINA',
				'nom_libro_vacaciones.periodo_pagado_desde AS PAGADA_DESDE',
				'nom_libro_vacaciones.periodo_pagado_hasta AS PAGADA_HASTA',
				'nom_libro_vacaciones.periodo_disfrute_vacacion_desde AS DISFRUTADA_DESDE',
				'nom_libro_vacaciones.periodo_disfrute_vacacion_hasta AS DISFRUTADA_HASTA',
				'nom_libro_vacaciones.dias_pagados AS D_PAGADOS',
				'nom_libro_vacaciones.dias_compensados AS D_COMPENSADOS',
				'nom_libro_vacaciones.dias_disfrutados AS D_DISFRUTADOS',
				'nom_libro_vacaciones.dias_no_habiles AS D_NO_HÁB.',
				'nom_libro_vacaciones.valor_vacaciones AS VALOR'
			)

			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_doc_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.periodo_pagado_desde", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.periodo_pagado_hasta", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.periodo_disfrute_vacacion_desde", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.periodo_disfrute_vacacion_hasta", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.dias_pagados", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.dias_compensados", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.dias_disfrutados", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.dias_no_habiles", "LIKE", "%$search%")
			->orWhere("nom_libro_vacaciones.valor_vacaciones", "LIKE", "%$search%")
			->orderBy('nom_prestamos.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE LIBRO VACACIÓN";
	}

	public static function opciones_campo_select()
	{
		$opciones = LibroVacacion::where('nom_libro_vacaciones.estado', 'Activo')
			->select('nom_libro_vacaciones.id', 'nom_libro_vacaciones.descripcion')
			->get();

		$vec[''] = '';
		foreach ($opciones as $opcion) {
			$vec[$opcion->id] = $opcion->descripcion;
		}

		return $vec;
	}
}
