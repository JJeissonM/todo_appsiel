<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SiesaProveedoresEnterpriseSeeder extends Seeder
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
        $path = base_path('database/seeds/data/siesa_proveedores_enterprise.xlsx');

        if (!file_exists($path)) {
            throw new \Exception('No se encuentra el archivo: ' . $path);
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $headerRow = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, false, false);
        $headers = $headerRow[0];

        $map = [
            'Codigo' => 'codigo',
            'Razon social' => 'razon_social',
            'Sucursal' => 'sucursal',
            'Razon social sucursal' => 'razon_social_sucursal',
            'Moneda' => 'moneda',
            'Desc. moneda' => 'desc_moneda',
            'Fecha ingreso' => 'fecha_ingreso',
            'Antiguedad' => 'antiguedad',
            'Clase de proveedor' => 'clase_de_proveedor',
            'Desc. clase de proveedor' => 'desc_clase_de_proveedor',
            'Condicion de pago' => 'condicion_de_pago',
            'Desc. condicion de pago' => 'desc_condicion_de_pago',
            'Dias gracia' => 'dias_gracia',
            'Cupo de credito' => 'cupo_de_credito',
            'Tipo proveedor' => 'tipo_proveedor',
            'Desc. tipo proveedor' => 'desc_tipo_proveedor',
            'SUJETO O NO INTERP' => 'sujeto_o_no_interp',
            'RTSERVIC' => 'rtservic',
            'LLAVE-RTSERVIC' => 'llave_rtservic',
            'RTSALARI' => 'rtsalari',
            'LLAVE-RTSALARI' => 'llave_rtsalari',
            'RTIVA1' => 'rtiva1',
            'LLAVE-RTIVA1' => 'llave_rtiva1',
            'RTHONORA' => 'rthonora',
            'LLAVE-RTHONORA' => 'llave_rthonora',
            'RTCOMISI' => 'rtcomisi',
            'LLAVE-RTCOMISI' => 'llave_rtcomisi',
            'RTBIENES' => 'rtbienes',
            'LLAVE-RTBIENES' => 'llave_rtbienes',
            'RTARREND' => 'rtarrend',
            'LLAVE-RTARREND' => 'llave_rtarrend',
            'RIVAGRAN' => 'rivagran',
            'IVA INTERP' => 'iva_interp',
            'INCBolsa' => 'incbolsa',
            'ICUI' => 'icui',
            'ICINDUST' => 'icindust',
            'ICD' => 'icd',
            'FEDEGAN' => 'fedegan',
            'ICASER' => 'icaser',
            'LLAVE-ICASER' => 'llave_icaser',
            'ICACOMER' => 'icacomer',
            'LLAVE-ICACOMER' => 'llave_icacomer',
            'IBUA' => 'ibua',
            'Numero cuenta' => 'numero_cuenta',
            'Tipo_cuenta' => 'tipo_cuenta',
            'Tipo de pago' => 'tipo_de_pago',
            'Tipo de tercero' => 'tipo_de_tercero',
            'Tipo de identificacion' => 'tipo_de_identificacion',
            'NOTA' => 'nota',
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
            DB::table('siesa_proveedores_enterprise')->truncate();

            for ($row = 2; $row <= $highestRow; $row++) {
                $rowValues = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, false, false);
                $rowValues = $rowValues[0];

                if ($this->isRowEmpty($rowValues)) {
                    continue;
                }

                $record = [
                    'codigo' => $this->toString($rowValues[$headerIndexes['codigo']]),
                    'razon_social' => $this->toString($rowValues[$headerIndexes['razon_social']]),
                    'sucursal' => $this->toString($rowValues[$headerIndexes['sucursal']]),
                    'razon_social_sucursal' => $this->toString($rowValues[$headerIndexes['razon_social_sucursal']]),
                    'moneda' => $this->toString($rowValues[$headerIndexes['moneda']]),
                    'desc_moneda' => $this->toString($rowValues[$headerIndexes['desc_moneda']]),
                    'fecha_ingreso' => $this->toString($rowValues[$headerIndexes['fecha_ingreso']]),
                    'antiguedad' => $this->toString($rowValues[$headerIndexes['antiguedad']]),
                    'clase_de_proveedor' => $this->toString($rowValues[$headerIndexes['clase_de_proveedor']]),
                    'desc_clase_de_proveedor' => $this->toString($rowValues[$headerIndexes['desc_clase_de_proveedor']]),
                    'condicion_de_pago' => $this->toString($rowValues[$headerIndexes['condicion_de_pago']]),
                    'desc_condicion_de_pago' => $this->toString($rowValues[$headerIndexes['desc_condicion_de_pago']]),
                    'dias_gracia' => $this->toString($rowValues[$headerIndexes['dias_gracia']]),
                    'cupo_de_credito' => $this->toString($rowValues[$headerIndexes['cupo_de_credito']]),
                    'tipo_proveedor' => $this->toString($rowValues[$headerIndexes['tipo_proveedor']]),
                    'desc_tipo_proveedor' => $this->toString($rowValues[$headerIndexes['desc_tipo_proveedor']]),
                    'sujeto_o_no_interp' => $this->toString($rowValues[$headerIndexes['sujeto_o_no_interp']]),
                    'rtservic' => $this->toString($rowValues[$headerIndexes['rtservic']]),
                    'llave_rtservic' => $this->toString($rowValues[$headerIndexes['llave_rtservic']]),
                    'rtsalari' => $this->toString($rowValues[$headerIndexes['rtsalari']]),
                    'llave_rtsalari' => $this->toString($rowValues[$headerIndexes['llave_rtsalari']]),
                    'rtiva1' => $this->toString($rowValues[$headerIndexes['rtiva1']]),
                    'llave_rtiva1' => $this->toString($rowValues[$headerIndexes['llave_rtiva1']]),
                    'rthonora' => $this->toString($rowValues[$headerIndexes['rthonora']]),
                    'llave_rthonora' => $this->toString($rowValues[$headerIndexes['llave_rthonora']]),
                    'rtcomisi' => $this->toString($rowValues[$headerIndexes['rtcomisi']]),
                    'llave_rtcomisi' => $this->toString($rowValues[$headerIndexes['llave_rtcomisi']]),
                    'rtbienes' => $this->toString($rowValues[$headerIndexes['rtbienes']]),
                    'llave_rtbienes' => $this->toString($rowValues[$headerIndexes['llave_rtbienes']]),
                    'rtarrend' => $this->toString($rowValues[$headerIndexes['rtarrend']]),
                    'llave_rtarrend' => $this->toString($rowValues[$headerIndexes['llave_rtarrend']]),
                    'rivagran' => $this->toString($rowValues[$headerIndexes['rivagran']]),
                    'iva_interp' => $this->toString($rowValues[$headerIndexes['iva_interp']]),
                    'incbolsa' => $this->toString($rowValues[$headerIndexes['incbolsa']]),
                    'icui' => $this->toString($rowValues[$headerIndexes['icui']]),
                    'icindust' => $this->toString($rowValues[$headerIndexes['icindust']]),
                    'icd' => $this->toString($rowValues[$headerIndexes['icd']]),
                    'fedegan' => $this->toString($rowValues[$headerIndexes['fedegan']]),
                    'icaser' => $this->toString($rowValues[$headerIndexes['icaser']]),
                    'llave_icaser' => $this->toString($rowValues[$headerIndexes['llave_icaser']]),
                    'icacomer' => $this->toString($rowValues[$headerIndexes['icacomer']]),
                    'llave_icacomer' => $this->toString($rowValues[$headerIndexes['llave_icacomer']]),
                    'ibua' => $this->toString($rowValues[$headerIndexes['ibua']]),
                    'numero_cuenta' => $this->toString($rowValues[$headerIndexes['numero_cuenta']]),
                    'tipo_cuenta' => $this->toString($rowValues[$headerIndexes['tipo_cuenta']]),
                    'tipo_de_pago' => $this->toString($rowValues[$headerIndexes['tipo_de_pago']]),
                    'tipo_de_tercero' => $this->toString($rowValues[$headerIndexes['tipo_de_tercero']]),
                    'tipo_de_identificacion' => $this->toString($rowValues[$headerIndexes['tipo_de_identificacion']]),
                    'nota' => $this->toString($rowValues[$headerIndexes['nota']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $batch[] = $record;

                if (count($batch) >= $batchSize) {
                    DB::table('siesa_proveedores_enterprise')->insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('siesa_proveedores_enterprise')->insert($batch);
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



