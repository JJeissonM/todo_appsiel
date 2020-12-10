<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NovedadTnl extends Model
{
	protected $table = 'nom_novedades_tnl';
	
	protected $fillable = ['nom_concepto_id', 'nom_contrato_id', 'fecha_inicial_tnl', 'fecha_final_tnl', 'cantidad_dias_tnl', 'cantidad_horas_tnl', 'tipo_novedad_tnl', 'codigo_diagnostico_incapacidad', 'numero_incapacidad', 'fecha_expedicion_incapacidad', 'origen_incapacidad', 'clase_incapacidad', 'fecha_incapacidad', 'valor_a_pagar_eps', 'valor_a_pagar_arl', 'valor_a_pagar_empresa', 'observaciones', 'estado', 'cantidad_dias_amortizados'];
	
	public $encabezado_tabla = ['Concepto', 'Empleado', 'Tipo novedad', 'Fecha inicial TNL', 'Cantidad días TNL', 'Observaciones', 'Estado', 'Acción'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

	public $archivo_js = 'assets/js/nomina/novedades_tnl.js';

	public static function consultar_registros()
	{
	    return NovedadTnl::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_novedades_tnl.nom_concepto_id')
	    				->leftJoin('nom_contratos','nom_contratos.id','=','nom_novedades_tnl.nom_contrato_id')
	    				->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
	    				->select(
	    						'nom_conceptos.descripcion AS campo1',
	    						'core_terceros.descripcion AS campo2',
	    						'nom_novedades_tnl.tipo_novedad_tnl AS campo3',
	    						'nom_novedades_tnl.fecha_inicial_tnl AS campo4',
	    						'nom_novedades_tnl.cantidad_dias_tnl AS campo5',
	    						'nom_novedades_tnl.observaciones AS campo6',
	    						'nom_novedades_tnl.estado AS campo7',
	    						'nom_novedades_tnl.id AS campo8')
					    ->get()
					    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = NovedadTnl::where('nom_novedades_tnl.estado','Activo')
                    ->select('nom_novedades_tnl.id','nom_novedades_tnl.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
