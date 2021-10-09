<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

use App\Core\Tercero;
use App\Contabilidad\ContabCuenta;

class EquivalenciaContable extends Model
{
    protected $table = 'nom_equivalencias_contables';

    /*
        NOTA: Falta validar que solo haya una (1) sola equivalencia por cada concepto y grupo de empleado.
    */

    /*
        tipo_causacion : Para el movimiento contable { causacion | crear_cxp | crear_cxc | anticipo_cxp | anticipo_cxc }
        tipo_movimiento : { debito | credito  }
        tercero_movimiento : { empleado | entidad_relacionada | tercero_especifico  }
        core_tercero_id: Tercero específico para el movimiento
    */
    protected $fillable = ['core_empresa_id', 'nom_concepto_id', 'nom_grupo_empleado_id', 'contab_cuenta_id', 'tipo_movimiento', 'tercero_movimiento', 'tipo_causacion', 'core_tercero_id', 'estado'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Concepto', 'Grupo de empleados', 'Cuenta contable', 'Tipo Movimiento', 'Causación',  'Tercero mov.', 'Tercero'];

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/nomina/equivalencias_contables.js';

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","store":"web","update":"web/id_fila","eliminar":"web_eliminar/id_fila"}';

    public function concepto()
    {
        return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
    }

    public function tercero_especifico()
    {
        return $this->belongsTo(Tercero::class, 'core_tercero_id');
    }

    public function cuenta_contable()
    {
        return $this->belongsTo(ContabCuenta::class, 'contab_cuenta_id');
    }
    
    public static function consultar_registros($nro_registros, $search)
    {
        return EquivalenciaContable::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_equivalencias_contables.nom_concepto_id')
                ->leftJoin('nom_grupos_empleados','nom_grupos_empleados.id','=','nom_equivalencias_contables.nom_grupo_empleado_id')
                ->leftJoin('contab_cuentas','contab_cuentas.id','=','nom_equivalencias_contables.contab_cuenta_id')
                ->leftJoin('core_terceros','core_terceros.id','=','nom_equivalencias_contables.core_tercero_id')
                ->select(
                            'nom_conceptos.descripcion AS campo1',
                            'nom_grupos_empleados.descripcion AS campo2',
                            DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo3'),
                            'nom_equivalencias_contables.tipo_movimiento AS campo4',
                            'nom_equivalencias_contables.tipo_causacion AS campo5',
                            'nom_equivalencias_contables.tercero_movimiento AS campo6',
                            'core_terceros.descripcion AS campo7',
                            'nom_equivalencias_contables.id AS campo8'
                        )
            ->where("nom_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_cuentas.codigo", "LIKE", "%$search%")
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
