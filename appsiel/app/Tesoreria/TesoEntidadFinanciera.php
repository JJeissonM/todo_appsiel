<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoEntidadFinanciera extends Model
{
    protected $table = 'teso_entidades_financieras';

    protected $fillable = ['id', 'descripcion', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = TesoEntidadFinanciera::select(
            'teso_entidades_financieras.id AS campo1',
            'teso_entidades_financieras.descripcion AS campo2',
            'teso_entidades_financieras.estado AS campo3',
            'teso_entidades_financieras.id AS campo4'
        )
            ->where("teso_entidades_financieras.id", "LIKE", "%$search%")
            ->orWhere("teso_entidades_financieras.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_entidades_financieras.estado", "LIKE", "%$search%")
            ->orderBy('teso_entidades_financieras.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = TesoEntidadFinanciera::select(
            'teso_entidades_financieras.id AS CÓDIGO',
            'teso_entidades_financieras.descripcion AS DESCRIPCIÓN',
            'teso_entidades_financieras.estado AS ESTADO'
        )
            ->where("teso_entidades_financieras.id", "LIKE", "%$search%")
            ->orWhere("teso_entidades_financieras.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_entidades_financieras.estado", "LIKE", "%$search%")
            ->orderBy('teso_entidades_financieras.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ENTIDADES FINANCIERAS";
    }
}
