<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class PilaRiesgoLaboral extends Model
{
    protected $table = 'nom_pila_liquidacion_riesgos_laborales';
	protected $fillable = ['planilla_generada_id', 'nom_contrato_id', 'fecha_final_mes', 'codigo_arl', 'dias_cotizados_riesgos_laborales', 'ibc_riesgos_laborales', 'tarifa_riesgos_laborales', 'total_cotizacion_riesgos_laborales', 'clase_de_riesgo'];
	public $encabezado_tabla = ['Planilla generada', 'Empleado', 'Fecha PILA', 'Codigo ARL', 'Dias cotizados Riesgos laborales', 'IBC Riesgos laborales', 'Tarifa Riesgos laborales', 'Total Cotizacion Riesgos laborales', 'Clase de Riesgo', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';
    
	public static function consultar_registros()
	{
	    return PilaRiesgoLaboral::leftJoin('nom_contratos','nom_contratos.id','=','nom_pila_liquidacion_riesgos_laborales.nom_contrato_id')
                                ->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
                                ->select('nom_pila_liquidacion_riesgos_laborales.planilla_generada_id AS campo1', 'core_terceros.descripcion AS campo2', 'nom_pila_liquidacion_riesgos_laborales.fecha_final_mes AS campo3', 'nom_pila_liquidacion_riesgos_laborales.codigo_arl AS campo4', 'nom_pila_liquidacion_riesgos_laborales.dias_cotizados_riesgos_laborales AS campo5', 'nom_pila_liquidacion_riesgos_laborales.ibc_riesgos_laborales AS campo6', 'nom_pila_liquidacion_riesgos_laborales.tarifa_riesgos_laborales AS campo7', 'nom_pila_liquidacion_riesgos_laborales.total_cotizacion_riesgos_laborales AS campo8', 'nom_pila_liquidacion_riesgos_laborales.clase_de_riesgo AS campo9', 'nom_pila_liquidacion_riesgos_laborales.id AS campo10')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PilaRiesgoLaboral::where('nom_pila_liquidacion_riesgos_laborales.estado','Activo')
                    ->select('nom_pila_liquidacion_riesgos_laborales.id','nom_pila_liquidacion_riesgos_laborales.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
