<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SiesaListasDescuentosSeeder extends Seeder
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
        $path = base_path('database/seeds/data/siesa_listas_de_descuentos.xlsx');

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
            'LD' => 'ld',
            'NOMBRE LD' => 'nombre_ld',
            'Referencia_Item' => 'referencia_item',
            'NOMBRE ITEM' => 'nombre_item',
            'FECHA' => 'fecha',
            'UM' => 'um',
            'VALOR MAX' => 'valor_max',
            'DESCUENTO1' => 'descuento1',
            'DESCUENTO2' => 'descuento2',
            'ITEMS ENTERPRISE' => 'items_enterprise',
            'EXTENCION ITEM ENTE' => 'extencion_item_ente',
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
            DB::table('siesa_listas_descuentos')->truncate();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowValues = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
                $rowValues = $rowValues[0];

                if ($this->isRowEmpty($rowValues)) {
                    continue;
                }

                $record = [
                    'ld' => $this->toString($rowValues[$headerIndexes['ld']]),
                    'nombre_ld' => $this->toString($rowValues[$headerIndexes['nombre_ld']]),
                    'referencia_item' => $this->toString($rowValues[$headerIndexes['referencia_item']]),
                    'nombre_item' => $this->toString($rowValues[$headerIndexes['nombre_item']]),
                    'fecha' => $this->parseDate($sheet, $row, $headerIndexes['fecha'], $rowValues[$headerIndexes['fecha']]),
                    'um' => $this->toString($rowValues[$headerIndexes['um']]),
                    'valor_max' => $this->toString($rowValues[$headerIndexes['valor_max']]),
                    'descuento1' => $this->parseDouble($rowValues[$headerIndexes['descuento1']]),
                    'descuento2' => $this->parseDouble($rowValues[$headerIndexes['descuento2']]),
                    'items_enterprise' => $this->toString($rowValues[$headerIndexes['items_enterprise']]),
                    'extencion_item_ente' => $this->toString($rowValues[$headerIndexes['extencion_item_ente']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    DB::table('siesa_listas_descuentos')->insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('siesa_listas_descuentos')->insert($batch);
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

    private function parseDate($sheet, $row, $colIndex, $value)
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        $cell = $sheet->getCellByColumnAndRow($colIndex + 1, $row);
        if (ExcelDate::isDateTime($cell)) {
            $date = ExcelDate::excelToDateTimeObject($cell->getValue());
            return $date->format('Y-m-d');
        }

        $value = trim((string)$value);
        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'm-d-Y'];
        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Try next format
            }
        }

        return null;
    }

    private function parseDouble($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float)$value;
        }

        $value = trim((string)$value);
        if ($value === '' || $value === '-') {
            return null;
        }

        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, ',') !== false) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float)$value : null;
    }
}


