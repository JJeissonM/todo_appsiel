<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contabilidad\Services\AuxiliarTercerosService;

class GenerarAuxiliarTerceros extends Command
{
    protected $signature = 'contabilidad:auxiliar-terceros {empresa} {--ano=2025} {--tercero=} {--cuenta=} {--cuenta_desde=} {--cuenta_hasta=} {--salida=}';
    protected $description = 'Exporta el auxiliar por terceros mensual, anual y detallado, sin modificar contabilidad.';

    public function handle()
    {
        $filters = ['empresa'=>$this->argument('empresa')];
        foreach (['ano','tercero','cuenta','cuenta_desde','cuenta_hasta'] as $key) {
            if ($this->option($key) !== null && $this->option($key) !== '') { $filters[$key] = $this->option($key); }
        }
        $directory = $this->option('salida') ?: storage_path('app/auxiliar_terceros_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)));
        if (file_exists($directory)) { $this->error('La carpeta de salida ya existe. Indique una carpeta nueva para evitar sobrescribir archivos.'); return 1; }
        try {
            $service = new AuxiliarTercerosService($filters);
            $result = $service->export($directory);
            $this->info($result['path']);
            $this->line(json_encode($result['audit'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return 0;
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage()); return 1;
        } catch (\Exception $e) {
            AuxiliarTercerosService::removeDirectory($directory);
            $this->error('No se generó el reporte. Tipo de error: '.get_class($e).' en '.basename($e->getFile()).':'.$e->getLine().'.');
            if (get_class($e) === 'RuntimeException') { $this->error($e->getMessage()); }
            return 1;
        }
    }
}
