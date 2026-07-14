<?php

namespace App\Console\Commands;

use App\FacturacionElectronica\Services\StudentInvoiceElectronicConversionService;
use Illuminate\Console\Command;

class ConvertirFacturasEstudiantesElectronicas extends Command
{
    protected $signature = 'facturacion:convertir-facturas-estudiantes
                            {--ids= : IDs de vtas_doc_encabezados separados por coma}
                            {--lote= : Lote de facturacion masiva}
                            {--fecha= : Fecha del documento YYYY-MM-DD}
                            {--created-from= : Fecha de creacion desde YYYY-MM-DD}
                            {--created-to= : Fecha de creacion hasta YYYY-MM-DD}
                            {--empresa-id= : Empresa a procesar}
                            {--limite= : Cantidad maxima de facturas}
                            {--confirmar : Aplica cambios; sin esta opcion solo simula}';

    protected $description = 'Convierte facturas estandar de estudiantes a facturas electronicas pendientes de envio.';

    public function handle(StudentInvoiceElectronicConversionService $service)
    {
        $filters = $this->filters();

        if (!$this->tieneFiltroSeguro($filters)) {
            $this->error('Debe indicar al menos --ids, --lote, --fecha, --created-from o --created-to.');
            return 1;
        }

        $dryRun = !$this->option('confirmar');
        $query = $service->candidatos($filters);

        if ($this->option('limite') !== null && $this->option('limite') !== '') {
            $query->limit((int)$this->option('limite'));
        }

        $documentos = $query->get();

        $this->info($dryRun ? 'Modo simulacion: no se aplicaran cambios.' : 'Modo confirmacion: se aplicaran cambios.');
        $this->info('Facturas candidatas: ' . count($documentos));

        $resumen = [
            'simulado' => 0,
            'convertido' => 0,
            'omitido' => 0,
            'error' => 0,
        ];

        foreach ($documentos as $documento) {
            try {
                $resultado = $service->convertir($documento->id, $filters, $dryRun);
            } catch (\Throwable $e) {
                $resultado = (object)[
                    'estado' => 'error',
                    'id' => $documento->id,
                    'origen' => is_null($documento->tipo_documento_app) ? (string)$documento->consecutivo : ($documento->tipo_documento_app->prefijo . ' ' . $documento->consecutivo),
                    'destino' => '',
                    'mensaje' => $e->getMessage(),
                ];
            }

            if (!isset($resumen[$resultado->estado])) {
                $resumen[$resultado->estado] = 0;
            }
            $resumen[$resultado->estado]++;

            $this->line(
                '[' . strtoupper($resultado->estado) . '] ID ' . $resultado->id .
                ' | ' . $resultado->origen .
                ' -> ' . $resultado->destino .
                ' | ' . $resultado->mensaje
            );
        }

        $this->info('Resumen: simuladas=' . $resumen['simulado'] . ', convertidas=' . $resumen['convertido'] . ', omitidas=' . $resumen['omitido'] . ', errores=' . $resumen['error']);

        if ($dryRun) {
            $this->warn('Para aplicar la conversion ejecute el mismo comando agregando --confirmar.');
        }

        return $resumen['error'] > 0 ? 1 : 0;
    }

    protected function filters()
    {
        return [
            'ids' => $this->parseIds($this->option('ids')),
            'lote' => trim((string)$this->option('lote')),
            'fecha' => trim((string)$this->option('fecha')),
            'created_from' => trim((string)$this->option('created-from')),
            'created_to' => trim((string)$this->option('created-to')),
            'empresa_id' => trim((string)$this->option('empresa-id')),
        ];
    }

    protected function parseIds($ids)
    {
        $ids = trim((string)$ids);
        if ($ids === '') {
            return [];
        }

        $parts = explode(',', $ids);
        $parsed = [];
        foreach ($parts as $part) {
            $id = (int)trim($part);
            if ($id > 0) {
                $parsed[] = $id;
            }
        }

        return array_values(array_unique($parsed));
    }

    protected function tieneFiltroSeguro(array $filters)
    {
        return !empty($filters['ids']) ||
            $filters['lote'] !== '' ||
            $filters['fecha'] !== '' ||
            $filters['created_from'] !== '' ||
            $filters['created_to'] !== '';
    }
}
