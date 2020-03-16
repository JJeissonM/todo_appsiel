<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomContrato extends Model
{
    //protected $table = 'nom_contratos';
		protected $fillable = ['core_tercero_id', 'clase_contrato', 'cargo_id', 'horas_laborales', 'sueldo', 'fecha_ingreso', 'contrato_hasta', 'entidad_salud_id', 'entidad_pension_id', 'entidad_arl_id', 'estado',
            'liquida_subsidio_transporte','planilla_pila_id','es_pasante_sena', 'entidad_cesantias_id', 'entidad_caja_compensacion_id'];

	public $encabezado_tabla = ['Empleado', 'Cargo', 'Sueldo', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
            ->select('core_terceros.descripcion AS campo1', 'nom_cargos.descripcion AS campo2', 'nom_contratos.sueldo AS campo3', 'nom_contratos.estado AS campo4', 'nom_contratos.id AS campo5')
		    ->get()
		    ->toArray();
	    return $registros;
	}

	public static function get_empleados($estado)
	{
		return NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
            ->where('nom_contratos.estado','LIKE',$estado.'%')
            ->select('core_terceros.descripcion AS empleado','core_terceros.id AS core_tercero_id', 'nom_cargos.descripcion AS cargo', 'nom_contratos.sueldo AS salario', 'core_terceros.numero_identificacion AS cedula')
            ->get();
	}

    public static function opciones_campo_select()
    {
        $opciones = NomContrato::leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')->where('nom_contratos.estado','Activo')
                    ->select('core_terceros.id','core_terceros.descripcion')
                    ->orderby('core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
