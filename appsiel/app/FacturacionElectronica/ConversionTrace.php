<?php

namespace App\FacturacionElectronica;

use Illuminate\Database\Eloquent\Model;

class ConversionTrace extends Model
{
    protected $table = 'fe_conversion_traces';

    protected $fillable = [
        'core_empresa_id',
        'vtas_doc_encabezado_id',
        'origen_core_tipo_transaccion_id',
        'origen_core_tipo_doc_app_id',
        'origen_consecutivo',
        'destino_core_tipo_transaccion_id',
        'destino_core_tipo_doc_app_id',
        'destino_consecutivo',
        'estado',
        'referencia',
        'motivo',
        'metadata',
        'creado_por',
        'modificado_por',
    ];
}
