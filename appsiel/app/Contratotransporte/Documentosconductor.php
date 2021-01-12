<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Documentosconductor extends Model
{
    protected $table = 'cte_documentosconductors';
    protected $fillable = ['id', 'conductor_id', 'licencia', 'documento', 'categoria', 'recurso', 'nro_documento', 'vigencia_inicio', 'vigencia_fin', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nro. Documento', 'Documento', 'Categoría', 'Inicio Vigencia', 'Vence', 'Documento Conductor', 'Conductor'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros2($nro_registros, $search)
    {
        return Documentosconductor::leftJoin('cte_conductors', 'cte_conductors.id', '=', 'cte_documentosconductors.conductor_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
            ->select(
                'cte_documentosconductors.nro_documento AS campo1',
                'cte_documentosconductors.documento AS campo2',
                'cte_documentosconductors.categoria AS campo3',
                'cte_documentosconductors.vigencia_inicio AS campo4',
                'cte_documentosconductors.vigencia_fin AS campo5',
                'core_terceros.numero_identificacion AS campo6',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo7'),
                'cte_documentosconductors.id AS campo8'
            )->where("cte_documentosconductors.nro_documento", "LIKE", "%$search%")
            ->orWhere("cte_documentosconductors.documento", "LIKE", "%$search%")
            ->orWhere("cte_documentosconductors.categoria", "LIKE", "%$search%")
            ->orWhere("cte_documentosconductors.vigencia_inicio", "LIKE", "%$search%")
            ->orWhere("cte_documentosconductors.vigencia_fin", "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orderBy('cte_documentosconductors.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Documentosconductor::leftJoin('cte_conductors', 'cte_conductors.id', '=', 'cte_documentosconductors.conductor_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
            ->select(
                'cte_documentosconductors.nro_documento AS NRO_DOCUMENTO',
                'cte_documentosconductors.documento AS DOCUMENTO',
                'cte_documentosconductors.categoria AS CATEGORÍA',
                'cte_documentosconductors.vigencia_inicio AS VIGENCIA_INICIO',
                'cte_documentosconductors.vigencia_fin AS VIGENCIA_FIN',
                'core_terceros.numero_identificacion AS IDENTIFICACIÓN',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS CONDUCTOR')
            )->where("cte_documentosconductors.nro_documento", "LIKE", "%$search%")
            ->orWhere("cte_documentosconductors.documento", "LIKE", "%$search%")
            ->orWhere("cte_documentosconductors.categoria", "LIKE", "%$search%")
            ->orWhere("cte_documentosconductors.vigencia_inicio", "LIKE", "%$search%")
            ->orWhere("cte_documentosconductors.vigencia_fin", "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orderBy('cte_documentosconductors.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCUMENTOS DE LOS CONDUCTORES";
    }


    public function conductor()
    {
        return $this->belongsTo(Conductor::class);
    }
}
