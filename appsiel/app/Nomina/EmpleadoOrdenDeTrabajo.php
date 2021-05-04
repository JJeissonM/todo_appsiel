<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class EmpleadoOrdenDeTrabajo extends Model
{
    protected $table = 'nom_empleados_ordenes_de_trabajo';
    protected $fillable = ['orden_trabajo_id', 'nom_contrato_id', 'nom_concepto_id', 'cantidad_horas', 'valor_por_hora', 'valor_devengo', 'estado', 'creado_por', 'modificado_por'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Orden de trabajo', 'Empleado', 'Concepto', 'Cant. horas', 'Vlr. hora', 'Total devengo', 'Estado'];

    public function orden_trabajo()
    {
        return $this->belongsTo(OrdenDeTrabajo::class, 'orden_trabajo_id');
    }

    public function contrato()
    {
        return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
    }

    public function concepto()
    {
        return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return EmpleadoOrdenDeTrabajo::select('nom_empleados_ordenes_de_trabajo.orden_trabajo_id AS campo1', 'nom_empleados_ordenes_de_trabajo.nom_contrato_id AS campo2', 'nom_empleados_ordenes_de_trabajo.nom_concepto_id AS campo3', 'nom_empleados_ordenes_de_trabajo.cantidad_horas AS campo4', 'nom_empleados_ordenes_de_trabajo.valor_por_hora AS campo5', 'nom_empleados_ordenes_de_trabajo.valor_devengo AS campo6', 'nom_empleados_ordenes_de_trabajo.estado AS campo7', 'nom_empleados_ordenes_de_trabajo.id AS campo8')
        ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $string = EmpleadoOrdenDeTrabajo::select('nom_empleados_ordenes_de_trabajo.orden_trabajo_id AS campo1', 'nom_empleados_ordenes_de_trabajo.nom_contrato_id AS campo2', 'nom_empleados_ordenes_de_trabajo.nom_concepto_id AS campo3', 'nom_empleados_ordenes_de_trabajo.cantidad_horas AS campo4', 'nom_empleados_ordenes_de_trabajo.valor_por_hora AS campo5', 'nom_empleados_ordenes_de_trabajo.valor_devengo AS campo6', 'nom_empleados_ordenes_de_trabajo.estado AS campo7', 'nom_empleados_ordenes_de_trabajo.id AS campo8')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE EMPLEADOS EN LAS ÓRDENES DE TRABAJO";
    }

    public static function opciones_campo_select()
    {
        $opciones = EmpleadoOrdenDeTrabajo::where('nom_empleados_ordenes_de_trabajo.estado','Activo')
                    ->select('nom_empleados_ordenes_de_trabajo.id','nom_empleados_ordenes_de_trabajo.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
