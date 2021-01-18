<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Auth;

class ContabNotaEeff extends Model
{
    protected $table = 'contab_notas_eeff';

    protected $fillable = ['core_empresa_id', 'numero', 'descripcion', 'periodo_ejercicio_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Número', 'Descripción', 'Periodo contable'];


    public static function consultar_registros($nro_registros, $search)
    {
        $registros = ContabNotaEeff::where('contab_notas_eeff.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'contab_notas_eeff.numero AS campo1',
                'contab_notas_eeff.descripcion AS campo2',
                'contab_notas_eeff.periodo_ejercicio_id AS campo3',
                'contab_notas_eeff.id AS campo4'
            )
            ->orWhere("contab_notas_eeff.numero", "LIKE", "%$search%")
            ->orWhere("contab_notas_eeff.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_notas_eeff.periodo_ejercicio_id", "LIKE", "%$search%")
            ->orderBy('contab_notas_eeff.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }
    public static function sqlString($search)
    {
        $string = ContabNotaEeff::where('contab_notas_eeff.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'contab_notas_eeff.numero AS NÚMERO',
                'contab_notas_eeff.descripcion AS DESCRIPCIÓN',
                'contab_notas_eeff.periodo_ejercicio_id AS PERIODO_CONTABLE'
            )
            ->orWhere("contab_notas_eeff.numero", "LIKE", "%$search%")
            ->orWhere("contab_notas_eeff.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_notas_eeff.periodo_ejercicio_id", "LIKE", "%$search%")
            ->orderBy('contab_notas_eeff.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE NOTAS EEFF";
    }
}
