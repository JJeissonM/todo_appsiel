<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class RetencionFuenteConceptoAnual extends Model
{
    protected $table = 'compras_retencion_fuente_conceptos_anuales';

    protected $fillable = [
        'anio',
        'uvt',
        'codigo',
        'concepto',
        'tipo_operacion',
        'tipo_item',
        'tipo_declarante',
        'tasa_retencion',
        'cuantia_minima_uvt',
        'cuantia_minima_pesos',
        'base_calculo',
        'contab_retencion_id',
        'estado',
    ];

    public function retencion()
    {
        return $this->belongsTo('App\Contabilidad\Retencion', 'contab_retencion_id');
    }

    public static function opciones_campo_select()
    {
        $opciones = self::where('anio', date('Y'))
            ->where('estado', 'Activo')
            ->orderBy('concepto')
            ->get();

        if ($opciones->isEmpty()) {
            $opciones = self::where('estado', 'Activo')
                ->orderBy('anio', 'DESC')
                ->orderBy('concepto')
                ->get();
        }

        $vec = [0 => 'Automático según producto/servicio'];
        foreach ($opciones as $opcion) {
            $declarante = $opcion->tipo_declarante == 'cualquiera' ? '' : ' - ' . str_replace('_', ' ', $opcion->tipo_declarante);
            $vec[$opcion->id] = $opcion->concepto . $declarante . ' (' . $opcion->tasa_retencion . '%)';
        }

        return $vec;
    }
}
