<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class PilaSalud extends Model
{
    protected $table = 'nom_pila_liquidacion_salud';
	protected $fillable = ['planilla_generada_id', 'nom_contrato_id', 'fecha_final_mes', 'codigo_entidad_salud', 'dias_cotizados_salud', 'ibc_salud', 'tarifa_salud', 'cotizacion_salud', 'valor_upc_adicional_salud', 'total_cotizacion_salud'];
	public $encabezado_tabla = ['Planilla generada', 'Empleado', 'Fecha PILA', 'Codigo Entidad', 'Dias cotizados', 'IBC', 'Tarifa', 'Cotizacion', 'Valor UPC Adicional', 'Total Cotizacion', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';
    
	public static function consultar_registros()
	{
	    return PilaSalud::leftJoin('nom_contratos','nom_contratos.id','=','nom_pila_liquidacion_salud.nom_contrato_id')
                                ->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
                                ->select('nom_pila_liquidacion_salud.planilla_generada_id AS campo1', 'core_terceros.descripcion AS campo2', 'nom_pila_liquidacion_salud.fecha_final_mes AS campo3', 'nom_pila_liquidacion_salud.codigo_entidad_salud AS campo4', 'nom_pila_liquidacion_salud.dias_cotizados_salud AS campo5', 'nom_pila_liquidacion_salud.ibc_salud AS campo6', 'nom_pila_liquidacion_salud.tarifa_salud AS campo7', 'nom_pila_liquidacion_salud.cotizacion_salud AS campo8', 'nom_pila_liquidacion_salud.valor_upc_adicional_salud AS campo9', 'nom_pila_liquidacion_salud.total_cotizacion_salud AS campo10', 'nom_pila_liquidacion_salud.id AS campo11')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PilaSalud::where('nom_pila_liquidacion_salud.estado','Activo')
                    ->select('nom_pila_liquidacion_salud.id','nom_pila_liquidacion_salud.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
