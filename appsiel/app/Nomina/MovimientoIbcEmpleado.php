<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

//  IBC = Ingreso Base de Cotización
class MovimientoIbcEmpleado extends Model
{
    protected $table = 'nom_movimientos_ibc_empleados';

    protected $fillable = ['nom_contrato_id', 'fecha_final_mes', 'valor_ibc_mes', 'observaciones', 'creado_por', 'modificado_por', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empleado', 'Mes', 'IBC', 'Observaciones', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        return MovimientoIbcEmpleado::select(
            'nom_movimientos_ibc_empleados.nom_contrato_id AS campo1',
            'nom_movimientos_ibc_empleados.fecha_final_mes AS campo2',
            'nom_movimientos_ibc_empleados.valor_ibc_mes AS campo3',
            'nom_movimientos_ibc_empleados.observaciones AS campo4',
            'nom_movimientos_ibc_empleados.estado AS campo5',
            'nom_movimientos_ibc_empleados.id AS campo6'
        )
            ->where("nom_movimientos_ibc_empleados.nom_contrato_id", "LIKE", "%$search%")
            ->orWhere("nom_movimientos_ibc_empleados.fecha_final_mes", "LIKE", "%$search%")
            ->orWhere("nom_movimientos_ibc_empleados.valor_ibc_mes", "LIKE", "%$search%")
            ->orWhere("nom_movimientos_ibc_empleados.observaciones", "LIKE", "%$search%")
            ->orWhere("nom_movimientos_ibc_empleados.estado", "LIKE", "%$search%")
            ->orderBy('nom_movimientos_ibc_empleados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = MovimientoIbcEmpleado::select(
            'nom_movimientos_ibc_empleados.nom_contrato_id AS EMPLEADO',
            'nom_movimientos_ibc_empleados.fecha_final_mes AS MES',
            'nom_movimientos_ibc_empleados.valor_ibc_mes AS IBC',
            'nom_movimientos_ibc_empleados.observaciones AS OBSERVACIONES',
            'nom_movimientos_ibc_empleados.estado AS ESTADO'
        )
            ->where("nom_movimientos_ibc_empleados.nom_contrato_id", "LIKE", "%$search%")
            ->orWhere("nom_movimientos_ibc_empleados.fecha_final_mes", "LIKE", "%$search%")
            ->orWhere("nom_movimientos_ibc_empleados.valor_ibc_mes", "LIKE", "%$search%")
            ->orWhere("nom_movimientos_ibc_empleados.observaciones", "LIKE", "%$search%")
            ->orWhere("nom_movimientos_ibc_empleados.estado", "LIKE", "%$search%")
            ->orderBy('nom_movimientos_ibc_empleados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMEINTOS DE IBC DE EMPLEADOS";
    }

    public static function opciones_campo_select()
    {
        $opciones = MovimientoIbcEmpleado::where('nom_movimientos_ibc_empleados.estado', 'Activo')
            ->select('nom_movimientos_ibc_empleados.id', 'nom_movimientos_ibc_empleados.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
