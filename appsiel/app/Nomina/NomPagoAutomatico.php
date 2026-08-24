<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomPagoAutomatico extends Model
{
    protected $table = 'nom_pagos_automaticos';

    protected $fillable = [
        'core_empresa_id', 'nom_doc_encabezado_id', 'teso_doc_encabezado_id',
        'teso_medio_recaudo_id', 'teso_caja_id', 'teso_cuenta_bancaria_id',
        'fecha_pago', 'valor_total', 'cantidad_empleados', 'estado',
        'token_solicitud', 'creado_por', 'modificado_por'
    ];

    public function detalles()
    {
        return $this->hasMany(NomPagoAutomaticoDetalle::class, 'nom_pago_automatico_id');
    }

    public function documento_nomina()
    {
        return $this->belongsTo(NomDocEncabezado::class, 'nom_doc_encabezado_id');
    }

    public function documento_pago()
    {
        return $this->belongsTo('App\Tesoreria\TesoDocEncabezado', 'teso_doc_encabezado_id');
    }
}
