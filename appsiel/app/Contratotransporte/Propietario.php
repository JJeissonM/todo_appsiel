<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Propietario extends Model
{
    protected $table = 'cte_propietarios';
    protected $fillable = ['id', 'genera_planilla', 'tercero_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['Tipo Documento', 'Número Documento', 'Propietario', 'Genera Planilla', 'Estado', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros2()
    {
        return Propietario::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                'core_tipos_docs_id.abreviatura AS campo1',
                'core_terceros.numero_identificacion AS campo2',
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'cte_propietarios.genera_planilla AS campo4',
                'core_terceros.estado AS campo5',
                'cte_propietarios.id AS campo6'
            )
            ->orderBy('cte_propietarios.created_at', 'DESC')
            ->paginate(100);
    }
}
