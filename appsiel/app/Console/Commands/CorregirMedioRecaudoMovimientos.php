<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CorregirMedioRecaudoMovimientos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appsiel:corregir-medio-recaudo-movimientos {--dry-run : Solo muestra conteos, no actualiza}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asigna teso_medio_recaudo_id en teso_movimientos cuando esta en 0, segun caja o cuenta bancaria';

    public function handle()
    {
        $dryRun = (bool)$this->option('dry-run');

        $queryBase = DB::table('teso_movimientos')
            ->where('teso_medio_recaudo_id', 0)
            ->where(function ($q) {
                $q->whereRaw('COALESCE(teso_caja_id,0) <> 0')
                    ->orWhereRaw('COALESCE(teso_cuenta_bancaria_id,0) <> 0');
            });

        $totalObjetivo = (clone $queryBase)->count();
        $soloCaja = (clone $queryBase)->whereRaw('COALESCE(teso_caja_id,0) <> 0')->whereRaw('COALESCE(teso_cuenta_bancaria_id,0) = 0')->count();
        $soloCuenta = (clone $queryBase)->whereRaw('COALESCE(teso_cuenta_bancaria_id,0) <> 0')->whereRaw('COALESCE(teso_caja_id,0) = 0')->count();
        $ambos = (clone $queryBase)->whereRaw('COALESCE(teso_caja_id,0) <> 0')->whereRaw('COALESCE(teso_cuenta_bancaria_id,0) <> 0')->count();

        $this->info('Registros objetivo (teso_medio_recaudo_id = 0 y caja/cuenta != 0): ' . $totalObjetivo);
        $this->line('  Solo caja (se asigna 1): ' . $soloCaja);
        $this->line('  Solo cuenta bancaria (se asigna 2): ' . $soloCuenta);
        $this->line('  Caja y cuenta bancaria (se asigna 2): ' . $ambos);

        if ($dryRun) {
            $this->warn('DRY-RUN activo: no se realizaron cambios.');
            return 0;
        }

        $actualizados = 0;
        DB::beginTransaction();
        try {
            $actualizados = DB::table('teso_movimientos')
                ->where('teso_medio_recaudo_id', 0)
                ->where(function ($q) {
                    $q->whereRaw('COALESCE(teso_caja_id,0) <> 0')
                        ->orWhereRaw('COALESCE(teso_cuenta_bancaria_id,0) <> 0');
                })
                ->update([
                    'teso_medio_recaudo_id' => DB::raw('CASE WHEN COALESCE(teso_cuenta_bancaria_id,0) <> 0 THEN 2 WHEN COALESCE(teso_caja_id,0) <> 0 THEN 1 ELSE 0 END')
                ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error al actualizar: ' . $e->getMessage());
            return 1;
        }

        $this->info('Registros actualizados: ' . $actualizados);

        return 0;
    }
}

