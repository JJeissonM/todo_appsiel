<?php

namespace App\Console\Commands;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasMovimiento;
use App\Compras\SyncFacturaCompraLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Antes de que el BOT distinguiera las notas crédito, todas se creaban como
 * factura de compra (p. ej. la nota ENC19137 de DATAICO quedó como FC 127).
 *
 * Este comando anula esa factura, siempre que siga pendiente (sin confirmar),
 * y borra su registro de sincronización para que el CUFE no quede como
 * "duplicado". Después se vuelve a sincronizar el documento desde el BOT y
 * entra como nota crédito.
 */
class LiberarNotasCreditoBotComoFactura extends Command
{
    protected $signature = 'compras:liberar-nota-credito-bot
        {cufe* : CUDE de la nota crédito tal como lo trae el BOT}
        {--ejecutar : Aplica los cambios; sin esta opción solo muestra qué haría}';

    protected $description = 'Anula una factura de compra pendiente que el BOT creó a partir de una nota crédito y libera su CUFE para re-sincronizarla';

    public function handle()
    {
        $ejecutar = (bool)$this->option('ejecutar');

        foreach ($this->argument('cufe') as $cufe) {
            $logs = SyncFacturaCompraLog::where('cufe', $cufe)->get();

            if ($logs->isEmpty()) {
                $this->warn("{$cufe}: no tiene registro de sincronización.");
                continue;
            }

            foreach ($logs as $log) {
                $documento = $log->compras_doc_encabezado_id
                    ? ComprasDocEncabezado::find($log->compras_doc_encabezado_id)
                    : null;

                if ($documento && (int)$documento->core_tipo_transaccion_id !== (int)config('compras.fc_tipo_transaccion_id', 25)) {
                    $this->warn("{$cufe}: el documento {$documento->id} no es una factura de compra; no se toca.");
                    continue;
                }

                $confirmada = $documento && ComprasMovimiento::where('core_empresa_id', $documento->core_empresa_id)
                    ->where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
                    ->where('consecutivo', $documento->consecutivo)
                    ->exists();

                if ($documento && $documento->estado !== 'Anulado' && $confirmada) {
                    $this->error("{$cufe}: la factura {$documento->id} ya fue confirmada. Anúlela desde el ERP y vuelva a ejecutar el comando.");
                    continue;
                }

                $etiqueta = $documento ? $documento->id . ' (consecutivo ' . $documento->consecutivo . ')' : 'sin documento';
                $this->line("{$cufe}: se anula la factura {$etiqueta} y se borra el registro de sincronización {$log->id}.");

                if (!$ejecutar) {
                    continue;
                }

                DB::transaction(function () use ($documento, $log) {
                    if ($documento && $documento->estado !== 'Anulado') {
                        ComprasDocRegistro::where('compras_doc_encabezado_id', $documento->id)
                            ->update(['estado' => 'Anulado', 'modificado_por' => 'compras:liberar-nota-credito-bot']);
                        $documento->update(['estado' => 'Anulado', 'modificado_por' => 'compras:liberar-nota-credito-bot']);
                    }

                    $log->delete();
                });
            }
        }

        if (!$ejecutar) {
            $this->warn('No se hicieron cambios. Use --ejecutar para aplicarlos.');
        }

        return 0;
    }
}
