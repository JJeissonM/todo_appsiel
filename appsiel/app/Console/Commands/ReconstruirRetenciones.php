<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Contabilidad\Services\Retenciones\TrazabilidadRetenciones;

class ReconstruirRetenciones extends Command
{
    protected $signature = 'contab:reconstruir-retenciones';
    protected $description = 'Recupera empresa, fecha y tercero de retenciones históricas con origen inequívoco.';

    public function handle()
    {
        $resultado = (new TrazabilidadRetenciones)->reconstruir();
        $this->info('Recuperados: '.$resultado['recuperados'].'. Pendientes de conciliación manual: '.$resultado['pendientes'].'.');
    }
}
