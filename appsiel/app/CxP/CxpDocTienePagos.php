<?php

namespace App\CxP;

use Illuminate\Database\Eloquent\Model;

use DB;

class CxpDocTienePagos extends Model
{
    protected $table = 'cxp_documento_tiene_pagos';

    protected $fillable = ['cxp_doc_cruce_id','fecha_registro', 'doc_cxp_id','doc_pago_id','transaccion_origen_doc_pago_id','doc_proveedor_prefijo','doc_proveedor_consecutivo','valor_pagado','creado_por','modificado_por'];
}
