<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use Auth;

class ConsecutivoDocumento extends Model
{
    protected $table = 'core_consecutivos_documentos';

    protected $fillable = ['core_empresa_id', 'core_documento_app_id', 'consecutivo_actual'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'ID consecutivo', 'ID Tipo Documento', 'Tipo Documento', 'Consecutivo actual'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = ConsecutivoDocumento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'core_consecutivos_documentos.core_documento_app_id')
            ->where('core_consecutivos_documentos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'core_consecutivos_documentos.id AS campo1',
                'core_tipos_docs_apps.id AS campo2',
                'core_tipos_docs_apps.descripcion AS campo3',
                'core_consecutivos_documentos.consecutivo_actual AS campo4',
                'core_consecutivos_documentos.id AS campo5'
            )
            ->orWhere("core_tipos_docs_apps.id", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.descripcion", "LIKE", "%$search%")
            ->orWhere("core_consecutivos_documentos.consecutivo_actual", "LIKE", "%$search%")
            ->orderBy('core_consecutivos_documentos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }
    public static function sqlString($search)
    {
        $string = ConsecutivoDocumento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'core_consecutivos_documentos.core_documento_app_id')
            ->where('core_consecutivos_documentos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'core_consecutivos_documentos.id AS ID consecutivo',
                'core_tipos_docs_apps.id AS ID Tipo Documento',
                'core_tipos_docs_apps.descripcion AS Tipo Documento',
                'core_consecutivos_documentos.consecutivo_actual AS Consecutivo actual'
            )
            ->orWhere("core_consecutivos_documentos.id", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.id", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.descripcion", "LIKE", "%$search%")
            ->orWhere("core_consecutivos_documentos.consecutivo_actual", "LIKE", "%$search%")
            ->orderBy('core_consecutivos_documentos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONSECITIVO DOCUMENTOS";
    }
}
