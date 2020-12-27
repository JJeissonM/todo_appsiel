<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use Auth;

class ConsecutivoDocumento extends Model
{
    protected $table = 'core_consecutivos_documentos';

    protected $fillable = ['core_empresa_id','core_documento_app_id','consecutivo_actual'];

    public $encabezado_tabla = ['ID consecutivo','ID Tipo Documento','Tipo Documento','Consecutivo actual','Acción'];

    public static function consultar_registros()
    {
    	$registros = ConsecutivoDocumento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'core_consecutivos_documentos.core_documento_app_id')
                    ->where('core_consecutivos_documentos.core_empresa_id',Auth::user()->empresa_id)
                    ->select('core_consecutivos_documentos.id AS campo1','core_tipos_docs_apps.id AS campo2','core_tipos_docs_apps.descripcion AS campo3','core_consecutivos_documentos.consecutivo_actual AS campo4','core_consecutivos_documentos.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }

}
