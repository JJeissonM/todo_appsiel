<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SiesaDatosCompletosProveedoresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $path = base_path('database/seeds/data/siesa_datos_completos_proveedores.xls');

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
            'Codigo' => 'codigo',
            'Sucursal' => 'sucursal',
            'Razon social sucursal' => 'razon_social_sucursal',
            'Clase de proveedor' => 'clase_de_proveedor',
            'Condicion de pago' => 'condicion_de_pago',
            'Tipo proveedor' => 'tipo_proveedor',
            'Forma de pago' => 'forma_de_pago',
            'Notas' => 'notas',
            'Contacto' => 'contacto',
            'Direccion 1' => 'direccion_1',
            'Direccion 2' => 'direccion_2',
            'Direccion 3' => 'direccion_3',
            'Cod. Depto.' => 'cod_depto',
            'Cod. Ciudad' => 'cod_ciudad',
            'Barrio' => 'barrio',
            'Telefono' => 'telefono',
            'Email' => 'email',
            'Fecha ingreso' => 'fecha_ingreso',
            'Monto anual compras' => 'monto_anual_compras',
            'Exige cotizacion en OC y entrada' => 'exige_cotizacion_en_oc_y_entrada',
            'Exige OC en entrada de almacen' => 'exige_oc_en_entrada_de_almacen',
            'Grupo CO' => 'grupo_co',
            'Celular' => 'celular',
            'Suc. defecto PE.' => 'suc_defecto_pe',
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
            DB::table('siesa_datos_completos_proveedores')->truncate();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowValues = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
                $rowValues = $rowValues[0];

                if ($this->isRowEmpty($rowValues)) {
                    continue;
                }

                $record = [
                    'codigo' => $this->toString($rowValues[$headerIndexes['codigo']]),
                    'sucursal' => $this->toString($rowValues[$headerIndexes['sucursal']]),
                    'razon_social_sucursal' => $this->toString($rowValues[$headerIndexes['razon_social_sucursal']]),
                    'clase_de_proveedor' => $this->toString($rowValues[$headerIndexes['clase_de_proveedor']]),
                    'condicion_de_pago' => $this->toString($rowValues[$headerIndexes['condicion_de_pago']]),
                    'tipo_proveedor' => $this->toString($rowValues[$headerIndexes['tipo_proveedor']]),
                    'forma_de_pago' => $this->toString($rowValues[$headerIndexes['forma_de_pago']]),
                    'notas' => $this->toString($rowValues[$headerIndexes['notas']]),
                    'contacto' => $this->toString($rowValues[$headerIndexes['contacto']]),
                    'direccion_1' => $this->toString($rowValues[$headerIndexes['direccion_1']]),
                    'direccion_2' => $this->toString($rowValues[$headerIndexes['direccion_2']]),
                    'direccion_3' => $this->toString($rowValues[$headerIndexes['direccion_3']]),
                    'cod_depto' => $this->toString($rowValues[$headerIndexes['cod_depto']]),
                    'cod_ciudad' => $this->toString($rowValues[$headerIndexes['cod_ciudad']]),
                    'barrio' => $this->toString($rowValues[$headerIndexes['barrio']]),
                    'telefono' => $this->toString($rowValues[$headerIndexes['telefono']]),
                    'email' => $this->toString($rowValues[$headerIndexes['email']]),
                    'fecha_ingreso' => $this->toString($rowValues[$headerIndexes['fecha_ingreso']]),
                    'monto_anual_compras' => $this->toString($rowValues[$headerIndexes['monto_anual_compras']]),
                    'exige_cotizacion_en_oc_y_entrada' => $this->toString($rowValues[$headerIndexes['exige_cotizacion_en_oc_y_entrada']]),
                    'exige_oc_en_entrada_de_almacen' => $this->toString($rowValues[$headerIndexes['exige_oc_en_entrada_de_almacen']]),
                    'grupo_co' => $this->toString($rowValues[$headerIndexes['grupo_co']]),
                    'celular' => $this->toString($rowValues[$headerIndexes['celular']]),
                    'suc_defecto_pe' => $this->toString($rowValues[$headerIndexes['suc_defecto_pe']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    DB::table('siesa_datos_completos_proveedores')->insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('siesa_datos_completos_proveedores')->insert($batch);
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
