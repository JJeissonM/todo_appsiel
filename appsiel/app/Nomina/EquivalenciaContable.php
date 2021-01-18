<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class EquivalenciaContable extends Model
{
    protected $table = 'nom_equivalencias_contables';
    protected $fillable = ['core_empresa_id', 'nom_concepto_id', 'nom_grupo_empleado_id', 'contab_cuenta_id', 'tipo_movimiento', 'core_tercero_id', 'nom_entidad_id', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Concepto', 'Grupo de empleados', 'Cuenta contable', 'Tipo Movimiento', 'Tercero contrapartida', 'Entidad contrapartida'];
    public static function consultar_registros($nro_registros, $search)
    {
        return EquivalenciaContable::select(
            'nom_equivalencias_contables.nom_concepto_id AS campo1',
            'nom_equivalencias_contables.nom_grupo_empleado_id AS campo2',
            'nom_equivalencias_contables.contab_cuenta_id AS campo3',
            'nom_equivalencias_contables.tipo_movimiento AS campo4',
            'nom_equivalencias_contables.core_tercero_id AS campo5',
            'nom_equivalencias_contables.nom_entidad_id AS campo6',
            'nom_equivalencias_contables.id AS campo7'
        )
            ->where("nom_equivalencias_contables.nom_concepto_id", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.nom_grupo_empleado_id", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.contab_cuenta_id", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.tipo_movimiento", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.core_tercero_id", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.nom_entidad_id", "LIKE", "%$search%")
            ->orderBy('nom_equivalencias_contables.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = EquivalenciaContable::select(
            'nom_equivalencias_contables.nom_concepto_id AS CONCEPTO',
            'nom_equivalencias_contables.nom_grupo_empleado_id AS GRUPO_DE_EMPLEADOS',
            'nom_equivalencias_contables.contab_cuenta_id AS CUENTA_CONTABLE',
            'nom_equivalencias_contables.tipo_movimiento AS TIPO_MOVIMIENTO',
            'nom_equivalencias_contables.core_tercero_id AS TERCERO_CONTRAPARTIDA',
            'nom_equivalencias_contables.nom_entidad_id AS ENTIDAD_CONTRAPARTIDA'
        )
            ->where("nom_equivalencias_contables.nom_concepto_id", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.nom_grupo_empleado_id", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.contab_cuenta_id", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.tipo_movimiento", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.core_tercero_id", "LIKE", "%$search%")
            ->orWhere("nom_equivalencias_contables.nom_entidad_id", "LIKE", "%$search%")
            ->orderBy('nom_equivalencias_contables.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE EQUIVALENCIA CONTABLE";
    }

    public static function opciones_campo_select()
    {
        $opciones = EquivalenciaContable::where('nom_equivalencias_contables.estado', 'Activo')
            ->select('nom_equivalencias_contables.id', 'nom_equivalencias_contables.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
