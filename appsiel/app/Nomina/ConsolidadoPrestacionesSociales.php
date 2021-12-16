<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

class ConsolidadoPrestacionesSociales extends Model
{
	protected $table = 'nom_consolidados_prestaciones_sociales';

	// tipo_prestacion = { vacaciones | prima_legal | cesantias | intereses_cesantias }

	// MISMO ENCABEZADO DE CONTRATOS
	protected $fillable = [ 'nom_contrato_id', 'tipo_prestacion', 'fecha_fin_mes', 'valor_acumulado_mes_anterior', 'valor_pagado_mes', 'valor_consolidado_mes', 'dias_consolidado_mes', 'dias_totales_laborados', 'valor_acumulado', 'dias_acumulados', 'observacion', 'estado'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Núm. identificación', 'Empleado', 'Grupo Empleado', 'Cargo', 'Sueldo', 'Fecha ingreso', 'Contrato hasta', 'Estado'];

	//public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';
	public $urls_acciones = '{"show":"nom_consolidado_empleado/id_fila"}';

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
		return NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
            ->leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_contratos.grupo_empleado_id')
            ->select(
                'core_terceros.numero_identificacion AS campo1',
                'core_terceros.descripcion AS campo2',
                'nom_grupos_empleados.descripcion AS campo3',
                'nom_cargos.descripcion AS campo4',
                'nom_contratos.sueldo AS campo5',
                'nom_contratos.fecha_ingreso AS campo6',
                'nom_contratos.contrato_hasta AS campo7',
                'nom_contratos.estado AS campo8',
                'nom_contratos.id AS campo9'
            )
            ->where("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_cargos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_contratos.sueldo", "LIKE", "%$search%")
            ->orWhere("nom_contratos.fecha_ingreso", "LIKE", "%$search%")
            ->orWhere("nom_contratos.contrato_hasta", "LIKE", "%$search%")
            ->orWhere("nom_contratos.estado", "LIKE", "%$search%")
            ->orderBy('nom_contratos.created_at', 'DESC')
            ->paginate($nro_registros);
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
