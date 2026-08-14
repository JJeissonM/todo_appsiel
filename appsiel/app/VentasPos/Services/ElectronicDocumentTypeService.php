<?php

namespace App\VentasPos\Services;

use App\Core\TipoDocApp;
use App\Ventas\ResolucionFacturacion;
use App\VentasPos\Pdv;

class ElectronicDocumentTypeService
{
    /**
     * Obtiene el tipo de documento FE del punto de venta. Los PDV que no lo
     * tengan configurado conservan el comportamiento historico de la config.
     */
    public function resolveId(Pdv $pdv = null)
    {
        if (!is_null($pdv) && !is_null($pdv->document_type_id_default) && (int)$pdv->document_type_id_default > 0) {
            return (int)$pdv->document_type_id_default;
        }

        return (int)config('facturacion_electronica.document_type_id_default');
    }

    public function resolve(Pdv $pdv = null)
    {
        return TipoDocApp::find($this->resolveId($pdv));
    }

    public function getActiveResolution(Pdv $pdv = null)
    {
        return ResolucionFacturacion::where('tipo_doc_app_id', $this->resolveId($pdv))
            ->where('estado', 'Activo')
            ->get()
            ->last();
    }
}
