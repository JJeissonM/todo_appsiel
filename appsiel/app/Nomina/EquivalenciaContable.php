<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class EquivalenciaContable extends Model
{
    protected $table = 'nom_equivalencias_contables';
	protected $fillable = ['core_empresa_id', 'nom_concepto_id', 'nom_grupo_empleado_id', 'contab_cuenta_id', 'tipo_movimiento', 'core_tercero_id', 'nom_entidad_id', 'estado'];
	public $encabezado_tabla = ['', 'Concepto', 'Grupo de empleados', 'Cuenta contable', 'Tipo Movimiento', 'Tercero contrapartida', 'Entidad contrapartida', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = EquivalenciaContable::select(, 'nom_equivalencias_contables.nom_concepto_id AS campo1', 'nom_equivalencias_contables.nom_grupo_empleado_id AS campo2', 'nom_equivalencias_contables.contab_cuenta_id AS campo3', 'nom_equivalencias_contables.tipo_movimiento AS campo4', 'nom_equivalencias_contables.core_tercero_id AS campo5', 'nom_equivalencias_contables.nom_entidad_id AS campo6', 'nom_equivalencias_contables.id AS campo7')
	    ->get()
	    ->toArray();
	    return $registros;
	}

	public static function opciones_campo_select()
    {
        $opciones = EquivalenciaContable::where('nom_equivalencias_contables.estado','Activo')
                    ->select('nom_equivalencias_contables.id','nom_equivalencias_contables.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
