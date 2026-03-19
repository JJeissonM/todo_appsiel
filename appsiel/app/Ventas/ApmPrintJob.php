<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ApmPrintJob extends Model
{
    protected $table = 'apm_print_jobs';

    protected $fillable = [
        'core_empresa_id',
        'core_tipo_transaccion_id',
        'core_tipo_doc_app_id',
        'consecutivo',
        'apm_print_status_id',
        'document_type',
        'document_label',
        'copy_number',
        'copy_label',
        'printer_id',
        'station_id',
        'payload_json',
        'attempts_count',
        'last_error',
        'queued_by',
        'printed_by',
        'queued_at',
        'last_attempt_at',
        'printed_at'
    ];

    public function status()
    {
        return $this->belongsTo('App\\Ventas\\ApmPrintStatus', 'apm_print_status_id');
    }
}