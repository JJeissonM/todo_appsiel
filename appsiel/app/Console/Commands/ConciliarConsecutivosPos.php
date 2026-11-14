<?php

namespace App\Console\Commands;

use App\VentasPos\Services\PosDuplicateReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ConciliarConsecutivosPos extends Command
{
    protected $signature = 'pos:conciliar-consecutivos
        {--ejecutar : Aplica el plan completo; por defecto solo diagnostica}
        {--respaldo= : Directorio nuevo para respaldo, plan y resultado; por defecto storage/app/conciliaciones-pos}';

    protected $description = 'Concilia consecutivos POS duplicados y sus referencias en la conexión actual, con respaldo y verificación';

    public function handle()
    {
        $directory = $this->option('respaldo') ?: storage_path('app/conciliaciones-pos/'.date('Ymd-His').'-'.str_replace('.', '', uniqid('', true)));
        $this->line('Base de datos: '.DB::connection()->getDatabaseName());
        $this->line('Respaldo e informe: '.$directory);
        try {
            $service = new PosDuplicateReconciliationService(DB::connection()->getPdo());
            $plan = $service->reconcile((bool)$this->option('ejecutar'), $directory);
            if (!$plan['documents']) {
                $this->info('No hay consecutivos POS duplicados.');
                return 0;
            }
            $rows = array();
            foreach ($plan['documents'] as $document) {
                $rows[] = array($document['id'], $document['kept_id'], $document['company'], $document['transaction'], $document['type'], $document['old'], $document['new']);
            }
            $this->table(array('ID renumerado', 'ID conserva', 'Empresa', 'Transacción', 'Tipo', 'Anterior', 'Nuevo'), $rows);
            if ($this->option('ejecutar')) {
                $this->info('Conciliación confirmada: '.count($plan['changes']).' filas. Cero duplicados POS después del commit.');
            } else {
                $this->info('Diagnóstico: '.count($plan['changes']).' filas por corregir. No se modificaron datos.');
                $this->line('Use --ejecutar para aplicar. Detenga la facturación, los workers y las integraciones durante la ejecución.');
            }
            $this->line('El comando no ejecuta migraciones ni modifica índices o contadores.');
            return 0;
        } catch (\Exception $error) {
            $this->error($error->getMessage());
            $this->line('Revise el informe en '.$directory.'.');
            return 1;
        }
    }
}
