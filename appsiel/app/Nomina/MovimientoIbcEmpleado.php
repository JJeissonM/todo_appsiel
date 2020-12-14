<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

//  IBC = Ingreso Base de Cotización
class MovimientoIbcEmpleado extends Model
{
    protected $table = 'nom_movimientos_ibc_empleados';
	
    protected $fillable = ['nom_contrato_id', 'fecha_final_mes', 'valor_ibc_mes', 'observaciones', 'creado_por', 'modificado_por', 'estado'];

	public $encabezado_tabla = ['Empleado', 'Mes', 'IBC', 'Observaciones', 'Estado', 'Acción'];

	public static function consultar_registros()
	{
	    return MovimientoIbcEmpleado::select('nom_movimientos_ibc_empleados.nom_contrato_id AS campo1', 'nom_movimientos_ibc_empleados.fecha_final_mes AS campo2', 'nom_movimientos_ibc_empleados.valor_ibc_mes AS campo3', 'nom_movimientos_ibc_empleados.observaciones AS campo4', 'nom_movimientos_ibc_empleados.estado AS campo5', 'nom_movimientos_ibc_empleados.id AS campo6')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = MovimientoIbcEmpleado::where('nom_movimientos_ibc_empleados.estado','Activo')
                    ->select('nom_movimientos_ibc_empleados.id','nom_movimientos_ibc_empleados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
