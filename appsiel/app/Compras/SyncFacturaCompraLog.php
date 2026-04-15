<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class SyncFacturaCompraLog extends Model
{
    protected $table = 'compras_sync_log';

    protected $fillable = [
        'cufe',
        'core_empresa_id',
        'compras_doc_encabezado_id',
        'estado',
        'mensaje_error',
        'creado_por',
    ];

    public function encabezado()
    {
        return $this->belongsTo(ComprasDocEncabezado::class, 'compras_doc_encabezado_id');
    }

    /**
     * Verifica si un CUFE ya fue procesado exitosamente para esta empresa.
     * Garantiza idempotencia: el BOT puede reintentar sin crear duplicados.
     */
    public static function ya_procesado(string $cufe, int $empresa_id): bool
    {
        return static::where('cufe', $cufe)
            ->where('core_empresa_id', $empresa_id)
            ->where('estado', 'procesado')
            ->exists();
    }
}