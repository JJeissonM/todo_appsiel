<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SiesaRetencionesSeeder extends Seeder
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
        $path = base_path('database/seeds/data/siesa_retenciones.xlsx');

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
            'Clase' => 'clase',
            'Descripcion' => 'descripcion',
            'Sigla' => 'sigla',
            'Estado' => 'estado',
            'Regionalidad' => 'regionalidad',
            'BaseImpto' => 'baseimpto',
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
            DB::table('siesa_retenciones')->truncate();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowValues = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
                $rowValues = $rowValues[0];

                if ($this->isRowEmpty($rowValues)) {
                    continue;
                }

                $record = [
                    'clase' => $this->toString($rowValues[$headerIndexes['clase']]),
                    'descripcion' => $this->toString($rowValues[$headerIndexes['descripcion']]),
                    'sigla' => $this->toString($rowValues[$headerIndexes['sigla']]),
                    'estado' => $this->toString($rowValues[$headerIndexes['estado']]),
                    'regionalidad' => $this->toString($rowValues[$headerIndexes['regionalidad']]),
                    'baseimpto' => $this->toString($rowValues[$headerIndexes['baseimpto']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    DB::table('siesa_retenciones')->insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('siesa_retenciones')->insert($batch);
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


