<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class PilaPension extends Model
{
    protected $table = 'nom_pila_liquidacion_pension';
	protected $fillable = ['planilla_generada_id', 'nom_contrato_id', 'fecha_final_mes', 'codigo_entidad_pension', 'dias_cotizados_pension', 'ibc_pension', 'tarifa_pension', 'cotizacion_pension', 'afp_voluntario_rais_empleado', 'afp_voluntatio_rais_empresa', 'subcuenta_solidaridad_fsp', 'subcuenta_subsistencia_fsp', 'total_cotizacion_pension', 'valor_cotizacion_pension'];
	public $encabezado_tabla = ['Planilla generada', 'Empleado', 'Fecha PILA', 'Codigo Entidad Pension', 'Dias cotizados Pension', 'IBC Pension', 'Tarifa Pension', 'Cotizacion Pension', 'AFP. Voluntario RAIS Empleado', 'AFP. Voluntatio RAIS Empresa', 'Subcuenta Solidaridad FSP', 'Subcuenta Subsistencia FSP', 'Total Cotizacion Pension', 'Valor Cotizacion Pension', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';
    
    
	public static function consultar_registros()
	{
	    return PilaPension::leftJoin('nom_contratos','nom_contratos.id','=','nom_pila_liquidacion_pension.nom_contrato_id')
                                ->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
                                ->select(
                                        'nom_pila_liquidacion_pension.planilla_generada_id AS campo1', 'core_terceros.descripcion AS campo2', 'nom_pila_liquidacion_pension.fecha_final_mes AS campo3', 'nom_pila_liquidacion_pension.codigo_entidad_pension AS campo4', 'nom_pila_liquidacion_pension.dias_cotizados_pension AS campo5', 'nom_pila_liquidacion_pension.ibc_pension AS campo6', 'nom_pila_liquidacion_pension.tarifa_pension AS campo7', 'nom_pila_liquidacion_pension.cotizacion_pension AS campo8', 'nom_pila_liquidacion_pension.afp_voluntario_rais_empleado AS campo9', 'nom_pila_liquidacion_pension.afp_voluntatio_rais_empresa AS campo10', 'nom_pila_liquidacion_pension.subcuenta_solidaridad_fsp AS campo11', 'nom_pila_liquidacion_pension.subcuenta_subsistencia_fsp AS campo12', 'nom_pila_liquidacion_pension.total_cotizacion_pension AS campo13', 'nom_pila_liquidacion_pension.valor_cotizacion_pension AS campo14', 'nom_pila_liquidacion_pension.id AS campo15')
                        	    ->get()
                        	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PilaPension::where('nom_pila_liquidacion_pension.estado','Activo')
                    ->select('nom_pila_liquidacion_pension.id','nom_pila_liquidacion_pension.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
