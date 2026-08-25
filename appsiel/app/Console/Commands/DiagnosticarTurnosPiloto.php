<?php

namespace App\Console\Commands;

use App\Core\Services\TurnoPilotDiagnosticService;
use Illuminate\Console\Command;

class DiagnosticarTurnosPiloto extends Command
{
    protected $signature = 'turnos:diagnosticar-piloto
                            {empresa_id : ID de la empresa}
                            {contexto_tipo : Tipo de contexto, por ejemplo pdv}
                            {contexto_id : ID del contexto}
                            {--dias=7 : Ventana de movimientos recientes}
                            {--json : Imprime el resultado completo como JSON}';

    protected $description = 'Diagnóstico de solo lectura antes de activar TURNOS en un contexto piloto.';

    public function handle(TurnoPilotDiagnosticService $service)
    {
        $result = $service->diagnose(
            $this->argument('empresa_id'),
            $this->argument('contexto_tipo'),
            $this->argument('contexto_id'),
            $this->option('dias')
        );

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return 0;
        }

        $this->info('Empresa: ' . ($result['empresa'] ?: 'NO ENCONTRADA') . ' [' . $result['empresa_id'] . ']');
        $this->line('Contexto: ' . $result['contexto_tipo'] . ':' . $result['contexto_id']);
        $this->line('Turno abierto: ' . (is_null($result['open_turn']) ? 'ninguno' : ($result['open_turn']['codigo'] . ' / ' . $result['open_turn']['estado'])));

        $moduleRows = array();
        foreach ($result['modules'] as $module => $settings) {
            $moduleRows[] = array($module, $settings['integrated'] ? 'sí' : 'no', $settings['mode']);
        }
        $this->table(array('Módulo', 'Integrado', 'Modo efectivo'), $moduleRows);

        $sourceRows = array();
        foreach ($result['recent_null_fk'] as $source) {
            $sourceRows[] = array($source['label'], $source['scope'], $source['count'], implode(',', $source['sample_ids']));
        }
        $this->table(array('Fuente', 'Alcance', 'Sin FK', 'IDs muestra'), $sourceRows);

        foreach ($result['warnings'] as $warning) {
            $this->warn($warning);
        }
        $this->info($result['ready'] ? 'Diagnóstico sin advertencias.' : 'Diagnóstico completado con advertencias; revise antes de activar.');
        return 0;
    }
}
