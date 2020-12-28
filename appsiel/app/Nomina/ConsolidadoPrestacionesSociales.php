<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

class ConsolidadoPrestacionesSociales extends Model
{
	protected $table = 'nom_consolidados_prestaciones_sociales';
	
	protected $fillable = [ 'nom_contrato_id', 'tipo_prestacion', 'fecha_fin_mes', 'valor_consolidado_mes_anterior', 'valor_pagado_mes', 'valor_consolidado_mes', 'dias_consolidado_mes', 'valor_acumulado', 'dias_acumulados', 'observacion', 'estado'  ];
	
	public $encabezado_tabla = ['Empleado', 'Prestación',  'Mes', 'Consolidado mes anterior', 'Vlr. pagado mes', 'Vlr. consolidado mes', 'Días consol. mes', 'Días acumulados', 'Vlr. acumulado', 'Acción'];

	//public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';
    public $urls_acciones = '{"show":"no"}';

	//public $archivo_js = 'assets/js/nomina/novedades_tnl.js';

	public function contrato()
	{
		return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
	}

	public static function consultar_registros()
	{
	    return ConsolidadoPrestacionesSociales::leftJoin('nom_contratos','nom_contratos.id','=','nom_consolidados_prestaciones_sociales.nom_contrato_id')
                	    				->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
                	    				->select(
                	    						'core_terceros.descripcion AS campo1',
                                                'nom_consolidados_prestaciones_sociales.tipo_prestacion AS campo2',
                                                'nom_consolidados_prestaciones_sociales.fecha_fin_mes AS campo3',
                	    						'nom_consolidados_prestaciones_sociales.valor_consolidado_mes_anterior AS campo4',
                	    						'nom_consolidados_prestaciones_sociales.valor_pagado_mes AS campo5',
                                                'nom_consolidados_prestaciones_sociales.valor_consolidado_mes AS campo6',
                	    						'nom_consolidados_prestaciones_sociales.dias_consolidado_mes AS campo7',
                	    						'nom_consolidados_prestaciones_sociales.dias_acumulados AS campo8',
                	    						'nom_consolidados_prestaciones_sociales.valor_acumulado AS campo9',
                	    						'nom_consolidados_prestaciones_sociales.id AS campo10')
                					    ->get()
                					    ->toArray();
	}
}
