<?php

namespace App\Tesoreria\Services;

use App\VentasPos\FacturaPos;

class PdvResolver
{
    public static function resolveFromArray(array $data)
    {
        $pdv_id = self::normalize($data['pdv_id'] ?? null);
        if ( !is_null($pdv_id) ) {
            return $pdv_id;
        }

        $request_pdv_id = self::normalize(request()->input('pdv_id'));
        if ( !is_null($request_pdv_id) ) {
            return $request_pdv_id;
        }

        return self::resolveFromPosDocument($data);
    }

    public static function normalize($value)
    {
        if ( is_null($value) || $value === '' ) {
            return null;
        }

        $pdv_id = (int)$value;
        return $pdv_id > 0 ? $pdv_id : null;
    }

    protected static function resolveFromPosDocument(array $data)
    {
        $core_tipo_transaccion_id = (int)($data['core_tipo_transaccion_id'] ?? 0);
        $core_tipo_doc_app_id = (int)($data['core_tipo_doc_app_id'] ?? 0);
        $consecutivo = (int)($data['consecutivo'] ?? 0);

        if ( $core_tipo_transaccion_id === 0 || $core_tipo_doc_app_id === 0 || $consecutivo === 0 ) {
            return null;
        }

        return FacturaPos::where('core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $core_tipo_doc_app_id)
            ->where('consecutivo', $consecutivo)
            ->value('pdv_id');
    }
}
