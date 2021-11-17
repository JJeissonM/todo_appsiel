<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

class ConsolidadoPrestacionesSociales extends Model
{
	protected $table = 'nom_consolidados_prestaciones_sociales';

	// tipo_prestacion = { vacaciones | prima_legal | cesantias | intereses_cesantias }
	protected $fillable = [ 'nom_contrato_id', 'tipo_prestacion', 'fecha_fin_mes', 'valor_acumulado_mes_anterior', 'valor_pagado_mes', 'valor_consolidado_mes', 'dias_consolidado_mes', 'dias_totales_laborados', 'valor_acumulado', 'dias_acumulados', 'observacion', 'estado'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empleado', 'Prestación',  'Mes', 'Acumulado mes anterior', 'Vlr. pagado mes', 'Vlr. consolidado mes', 'Días consol. mes', 'Vlr. acumulado'];

	//public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';
	public $urls_acciones = '{"show":"no"}';

	//public $archivo_js = 'assets/js/nomina/novedades_tnl.js';

	public function contrato()
	{
		return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
	}

	public function get_descripcion_prestacion()
	{
		switch ( $this->tipo_prestacion )
		{
			case 'vacaciones':
				return 'Vacaciones'; 
				break;
			
			
			case 'prima_legal':
				return 'Prima de servicios'; 
				break;
			
			
			case 'cesantias':
				return 'Cesantías'; 
				break;
			
			
			case 'intereses_cesantias':
				return 'Intereses de cesantías'; 
				break;
			
			default:
				// code...
				break;
		}
	}

	public static function consultar_registros($nro_registros, $search)
	{
		$collection =  ConsolidadoPrestacionesSociales::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_consolidados_prestaciones_sociales.nom_contrato_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
			->select(
				'core_terceros.descripcion AS campo1',
				'nom_consolidados_prestaciones_sociales.tipo_prestacion AS campo2',
				'nom_consolidados_prestaciones_sociales.fecha_fin_mes AS campo3',
				'nom_consolidados_prestaciones_sociales.valor_acumulado_mes_anterior AS campo4',
				'nom_consolidados_prestaciones_sociales.valor_pagado_mes AS campo5',
				'nom_consolidados_prestaciones_sociales.valor_consolidado_mes AS campo6',
				'nom_consolidados_prestaciones_sociales.dias_consolidado_mes AS campo7',
				'nom_consolidados_prestaciones_sociales.valor_acumulado AS campo8',
				'nom_consolidados_prestaciones_sociales.id AS campo9'
			)
			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.tipo_prestacion", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.fecha_fin_mes", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.valor_acumulado_mes_anterior", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.valor_pagado_mes", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.valor_consolidado_mes", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.dias_consolidado_mes", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.dias_acumulados", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.valor_acumulado", "LIKE", "%$search%")
			->orderBy('nom_consolidados_prestaciones_sociales.created_at', 'DESC')
			->paginate($nro_registros);

    	if (count($collection) > 0)
        {
            foreach ($collection as $c)
            {
                $c->campo4 = '$' . number_format( $c->campo4, 0, ',', '.' );
                $c->campo5 = '$' . number_format( $c->campo5, 0, ',', '.' );
                $c->campo6 = '$' . number_format( $c->campo6, 0, ',', '.' );
                $c->campo7 = number_format( $c->campo7, 2, ',', '.' );
                $c->campo8 = '$' . number_format( $c->campo8, 0, ',', '.' );
            }
        }
        
        return $collection;
	}

	public static function sqlString($search)
	{
		$string = ConsolidadoPrestacionesSociales::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_consolidados_prestaciones_sociales.nom_contrato_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
			->select(
				'core_terceros.descripcion AS EMPLEADO',
				'nom_consolidados_prestaciones_sociales.tipo_prestacion AS PRESTACIÓN',
				'nom_consolidados_prestaciones_sociales.fecha_fin_mes AS MES',
				'nom_consolidados_prestaciones_sociales.valor_acumulado_mes_anterior AS CONSOLIDADO_MES_ANTERIOR',
				'nom_consolidados_prestaciones_sociales.valor_pagado_mes AS VLR_PAGADO_MES',
				'nom_consolidados_prestaciones_sociales.valor_consolidado_mes AS VLR_CONSOLIDADO_MES',
				'nom_consolidados_prestaciones_sociales.dias_consolidado_mes AS DÍAS_CONSOL_MES',
				'nom_consolidados_prestaciones_sociales.dias_acumulados AS DÍAS_ACUMULADOS',
				'nom_consolidados_prestaciones_sociales.valor_acumulado AS VLR_ACUMULADO'
			)
			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.tipo_prestacion", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.fecha_fin_mes", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.valor_acumulado_mes_anterior", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.valor_pagado_mes", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.valor_consolidado_mes", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.dias_consolidado_mes", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.dias_acumulados", "LIKE", "%$search%")
			->orWhere("nom_consolidados_prestaciones_sociales.valor_acumulado", "LIKE", "%$search%")
			->orderBy('nom_consolidados_prestaciones_sociales.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE CSOLIDADO PRESTACIONES SOCIALES";
	}
}
