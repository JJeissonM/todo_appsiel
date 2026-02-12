<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SiesaEncabezadosListasDescuentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        if (env('APPSIEL_CLIENTE') !== 'SIESA') {
            return;
        }
        $path = base_path('database/seeds/data/siesa_encabezados_listas_descuentos.xlsx');

        if (!file_exists($path)) {
            throw new \Exception('No se encuentra el archivo: ' . $path);
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $headerRow = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false);
        $headers = $headerRow[0];

        $map = [
            'ID_entreprise' => 'id_entreprise',
            'LP' => 'lp',
            'DescripciÃƒÂ³n dscto/promociÃƒÂ³n' => 'descripcion_dscto_promocion',
            'Fecha inicial' => 'fecha_inicial',
            'Fecha final' => 'fecha_final',
            'Estado' => 'estado',
            'Exclusivo' => 'exclusivo',
            'Exclusivo para control dsctos manuales' => 'exclusivo_para_control_dsctos_manuales',
            'Exclusivo valor acumulado' => 'exclusivo_valor_acumulado',
            'Notas' => 'notas',
        ];

        $headerIndexes = [];
        foreach ($headers as $index => $header) {
            $header = trim($header);
            if (isset($map[$header])) {
                $headerIndexes[$map[$header]] = $index;
            }
        }

        $missing = array_diff(array_values($map), array_keys($headerIndexes));
        if (!empty($missing)) {
            throw new \Exception('Faltan columnas en el Excel: ' . implode(', ', $missing));
        }

        $now = Carbon::now();
        $batch = [];
        $batchSize = 500;

        DB::beginTransaction();
        try {
            DB::table('siesa_encabezados_listas_descuentos')->truncate();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowValues = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
                $rowValues = $rowValues[0];

                if ($this->isRowEmpty($rowValues)) {
                    continue;
                }

                $record = [
                    'id_entreprise' => $this->toString($rowValues[$headerIndexes['id_entreprise']]),
                    'lp' => $this->toString($rowValues[$headerIndexes['lp']]),
                    'descripcion_dscto_promocion' => $this->toString($rowValues[$headerIndexes['descripcion_dscto_promocion']]),
                    'fecha_inicial' => $this->toString($rowValues[$headerIndexes['fecha_inicial']]),
                    'fecha_final' => $this->toString($rowValues[$headerIndexes['fecha_final']]),
                    'estado' => $this->toString($rowValues[$headerIndexes['estado']]),
                    'exclusivo' => $this->toString($rowValues[$headerIndexes['exclusivo']]),
                    'exclusivo_para_control_dsctos_manuales' => $this->toString($rowValues[$headerIndexes['exclusivo_para_control_dsctos_manuales']]),
                    'exclusivo_valor_acumulado' => $this->toString($rowValues[$headerIndexes['exclusivo_valor_acumulado']]),
                    'notas' => $this->toString($rowValues[$headerIndexes['notas']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    DB::table('siesa_encabezados_listas_descuentos')->insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('siesa_encabezados_listas_descuentos')->insert($batch);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function isRowEmpty($rowValues)
    {
        foreach ($rowValues as $value) {
            if ($value !== null && trim((string)$value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function toString($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string)$value);

        return $value === '' ? null : $value;
    }
}


