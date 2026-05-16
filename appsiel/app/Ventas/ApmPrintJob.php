<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ApmPrintJob extends Model
{
    protected $table = 'apm_print_jobs';

    public $encabezado_tabla = [
        '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>',
        'Estado',
        'Documento',
        'Tipo',
        'Copia',
        'Impresora',
        'Estacion',
        'Intentos',
        'En cola',
        'Ultimo intento',
        'Impreso',
        'Usuario cola',
        'Ultimo error'
    ];

    public $urls_acciones = '{
        "create":"no",
        "edit":"no",
        "show":"no",
        "imprimir":"no",
        "cambiar_estado":"no",
        "eliminar":"apm_print_jobs/id_fila/delete",
        "otros_enlaces":"[{\"url\":\"apm_print_jobs/id_fila/mark_pending\",\"title\":\"Imprimir ahora\",\"color_bootstrap\":\"primary\",\"faicon\":\"refresh\",\"size\":\"sm\",\"tag_html\":\"a\"},{\"url\":\"apm_print_jobs/id_fila/mark_printed\",\"title\":\"Marcar como impreso\",\"color_bootstrap\":\"success\",\"faicon\":\"check\",\"size\":\"sm\",\"tag_html\":\"a\"},{\"url\":\"apm_print_jobs/id_fila/mark_retired\",\"title\":\"Retirar de cola\",\"color_bootstrap\":\"warning\",\"faicon\":\"archive\",\"size\":\"sm\",\"tag_html\":\"a\"},{\"url\":\"apm_print_jobs/id_fila/mark_cancelled\",\"title\":\"Cancelar\",\"color_bootstrap\":\"danger\",\"faicon\":\"ban\",\"size\":\"sm\",\"tag_html\":\"a\"}]"
    }';

    public $archivo_js = 'assets/js/apm/print_jobs_catalog.js';

    public $vistas = '';

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
        'retired_by',
        'queued_at',
        'last_attempt_at',
        'printed_at',
        'retired_at'
    ];

    public function status()
    {
        return $this->belongsTo('App\\Ventas\\ApmPrintStatus', 'apm_print_status_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $search = trim($search);

        return self::leftJoin('apm_print_statuses', 'apm_print_statuses.id', '=', 'apm_print_jobs.apm_print_status_id')
            ->select(
                'apm_print_statuses.description AS campo1',
                \DB::raw("CONCAT(apm_print_jobs.document_label, ' #', apm_print_jobs.consecutivo) AS campo2"),
                'apm_print_jobs.document_type AS campo3',
                'apm_print_jobs.copy_label AS campo4',
                'apm_print_jobs.printer_id AS campo5',
                'apm_print_jobs.station_id AS campo6',
                'apm_print_jobs.attempts_count AS campo7',
                'apm_print_jobs.queued_at AS campo8',
                'apm_print_jobs.last_attempt_at AS campo9',
                'apm_print_jobs.printed_at AS campo10',
                'apm_print_jobs.queued_by AS campo11',
                'apm_print_jobs.last_error AS campo12',
                'apm_print_jobs.id AS campo13'
            )
            ->where(function ($query) use ($search) {
                $query->where('apm_print_statuses.description', 'LIKE', "%$search%")
                    ->orWhere('apm_print_statuses.code', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.document_label', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.document_type', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.consecutivo', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.copy_label', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.printer_id', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.station_id', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.queued_by', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.printed_by', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.retired_by', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.last_error', 'LIKE', "%$search%");
            })
            ->orderByRaw("CASE apm_print_statuses.code WHEN 'pending' THEN 0 WHEN 'retired' THEN 1 WHEN 'cancelled' THEN 2 ELSE 3 END")
            ->orderBy('apm_print_jobs.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $search = trim($search);

        $string = self::leftJoin('apm_print_statuses', 'apm_print_statuses.id', '=', 'apm_print_jobs.apm_print_status_id')
            ->select(
                'apm_print_statuses.description AS ESTADO',
                \DB::raw("CONCAT(apm_print_jobs.document_label, ' #', apm_print_jobs.consecutivo) AS DOCUMENTO"),
                'apm_print_jobs.document_type AS TIPO',
                'apm_print_jobs.copy_label AS COPIA',
                'apm_print_jobs.printer_id AS IMPRESORA',
                'apm_print_jobs.station_id AS ESTACION',
                'apm_print_jobs.attempts_count AS INTENTOS',
                'apm_print_jobs.queued_at AS EN_COLA',
                'apm_print_jobs.last_attempt_at AS ULTIMO_INTENTO',
                'apm_print_jobs.printed_at AS IMPRESO',
                'apm_print_jobs.queued_by AS USUARIO_COLA',
                'apm_print_jobs.printed_by AS USUARIO_IMPRIME',
                'apm_print_jobs.retired_by AS USUARIO_RETIRA',
                'apm_print_jobs.last_error AS ULTIMO_ERROR'
            )
            ->where(function ($query) use ($search) {
                $query->where('apm_print_statuses.description', 'LIKE', "%$search%")
                    ->orWhere('apm_print_statuses.code', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.document_label', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.document_type', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.consecutivo', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.copy_label', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.printer_id', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.station_id', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.queued_by', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.printed_by', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.retired_by', 'LIKE', "%$search%")
                    ->orWhere('apm_print_jobs.last_error', 'LIKE', "%$search%");
            })
            ->orderBy('apm_print_jobs.created_at', 'DESC')
            ->toSql();

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    public static function tituloExport()
    {
        return 'GESTION DE TRABAJOS DE IMPRESION APM';
    }
}
