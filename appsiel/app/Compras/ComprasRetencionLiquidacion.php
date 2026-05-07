<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class ComprasRetencionLiquidacion extends Model
{
    protected $table = 'compras_retenciones_liquidaciones';

    protected $fillable = [
        'compras_doc_encabezado_id',
        'compras_doc_registro_id',
        'contab_registro_retencion_id',
        'retencion_fuente_concepto_anual_id',
        'contab_retencion_id',
        'anio',
        'codigo_concepto',
        'concepto',
        'tipo_operacion',
        'tipo_declarante',
        'base_retencion',
        'tasa_retencion',
        'cuantia_minima_uvt',
        'cuantia_minima_pesos',
        'valor_retencion',
        'aplicada',
        'origen',
        'detalle',
        'creado_por',
        'modificado_por',
        'estado',
    ];

    public function encabezado()
    {
        return $this->belongsTo(ComprasDocEncabezado::class, 'compras_doc_encabezado_id');
    }

    public function linea()
    {
        return $this->belongsTo(ComprasDocRegistro::class, 'compras_doc_registro_id');
    }
}
