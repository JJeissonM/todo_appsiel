<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class PilaParafiscales extends Model
{
    protected $table = 'nom_pila_liquidacion_parafiscales';
	protected $fillable = ['planilla_generada_id', 'nom_contrato_id', 'fecha_final_mes', 'cotizante_exonerado_de_aportes_parafiscales', 'codigo_entidad_ccf', 'dias_cotizados', 'ibc_parafiscales', 'tarifa_ccf', 'cotizacion_ccf', 'tarifa_sena', 'cotizacion_sena', 'tarifa_icbf', 'cotizacion_icbf', 'total_cotizacion'];
	public $encabezado_tabla = ['Planilla generada', 'Empleado', 'Fecha PILA', 'Cotizante Exonerado de aportes Parafiscales', 'Codigo Entidad CCF', 'Dias cotizados', 'IBC Parafiscales', 'Tarifa CCF', 'Cotizacion CCF', 'Tarifa SENA', 'Cotizacion SENA', 'Tarifa ICBF', 'Cotizacion ICBF', 'Total Cotizacion', 'Acción'];
	public static function consultar_registros()
	{
	    return PilaParafiscales::select('nom_pila_liquidacion_parafiscales.nom_contrato_id AS campo1', 'nom_pila_liquidacion_parafiscales.fecha_final_mes AS campo2', 'nom_pila_liquidacion_parafiscales.cotizante_exonerado_de_aportes_parafiscales AS campo3', 'nom_pila_liquidacion_parafiscales.codigo_entidad_ccf AS campo4', 'nom_pila_liquidacion_parafiscales.dias_cotizados AS campo5', 'nom_pila_liquidacion_parafiscales.ibc_parafiscales AS campo6', 'nom_pila_liquidacion_parafiscales.tarifa_ccf AS campo7', 'nom_pila_liquidacion_parafiscales.cotizacion_ccf AS campo8', 'nom_pila_liquidacion_parafiscales.tarifa_sena AS campo9', 'nom_pila_liquidacion_parafiscales.cotizacion_sena AS campo10', 'nom_pila_liquidacion_parafiscales.tarifa_icbf AS campo11', 'nom_pila_liquidacion_parafiscales.cotizacion_icbf AS campo12', 'nom_pila_liquidacion_parafiscales.total_cotizacion AS campo13', 'nom_pila_liquidacion_parafiscales.id AS campo14')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PilaParafiscales::where('nom_pila_liquidacion_parafiscales.estado','Activo')
                    ->select('nom_pila_liquidacion_parafiscales.id','nom_pila_liquidacion_parafiscales.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
