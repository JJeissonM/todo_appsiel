<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SiesaClientesSeeder extends Seeder
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
        $path = base_path('database/seeds/data/siesa_clientes.xlsx');

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
            'Id_Cliente' => 'id_cliente',
            'Nombre Cliente' => 'nombre_cliente',
            'Centro de Operacion Cliente' => 'centro_operacion_cliente',
            'Estado Cliente' => 'estado_cliente',
            'Lista de Precio Cliente' => 'lista_precio_cliente',
            'LD' => 'ld',
            'Sucursal_Cliente' => 'sucursal_cliente',
            'CLIENTE EN ENTERPRISE' => 'cliente_en_enterprise',
            'Columna1' => 'columna1',
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
            DB::table('siesa_clientes')->truncate();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowValues = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
                $rowValues = $rowValues[0];

                if ($this->isRowEmpty($rowValues)) {
                    continue;
                }

                $record = [
                    'id_cliente' => $this->toString($rowValues[$headerIndexes['id_cliente']]),
                    'nombre_cliente' => $this->toString($rowValues[$headerIndexes['nombre_cliente']]),
                    'centro_operacion_cliente' => $this->toString($rowValues[$headerIndexes['centro_operacion_cliente']]),
                    'estado_cliente' => $this->toString($rowValues[$headerIndexes['estado_cliente']]),
                    'lista_precio_cliente' => $this->toString($rowValues[$headerIndexes['lista_precio_cliente']]),
                    'ld' => $this->toString($rowValues[$headerIndexes['ld']]),
                    'sucursal_cliente' => $this->toString($rowValues[$headerIndexes['sucursal_cliente']]),
                    'cliente_en_enterprise' => $this->toString($rowValues[$headerIndexes['cliente_en_enterprise']]),
                    'columna1' => $this->toString($rowValues[$headerIndexes['columna1']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    DB::table('siesa_clientes')->insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('siesa_clientes')->insert($batch);
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


