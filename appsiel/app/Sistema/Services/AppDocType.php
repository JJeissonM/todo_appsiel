<?php

namespace App\Sistema\Services;

use App\Core\TipoDocApp;

class AppDocType
{
    public static function get_consecutivo_actual($core_empresa_id, $tipo_doc_app_id)
    {
        return TipoDocApp::get_consecutivo_actual($core_empresa_id, $tipo_doc_app_id);
    }

    public static function reservar_consecutivo($core_empresa_id, $tipo_doc_app_id)
    {
        return TipoDocApp::reservar_consecutivo($core_empresa_id, $tipo_doc_app_id);
    }

    /** @deprecated Use reservar_consecutivo and its returned number directly. */
    public static function aumentar_consecutivo($core_empresa_id, $tipo_doc_app_id)
    {
        return TipoDocApp::aumentar_consecutivo($core_empresa_id, $tipo_doc_app_id);
    }
}
