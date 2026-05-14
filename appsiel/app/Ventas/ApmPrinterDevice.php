<?php

namespace App\Ventas;

class ApmPrinterDevice
{
    public static function opciones_campo_select()
    {
        return ApmDevice::printerOptions();
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $query = ApmDevice::query()
            ->select('apm_devices.*', 'apm_devices.device_name AS descripcion')
            ->where('device_type', 'printer');

        if (func_num_args() == 2) {
            $value = $operator;
            $operator = '=';
        }

        if ($column == 'id') {
            return $query->where('device_id', $operator, $value, $boolean);
        }

        return $query->where($column, $operator, $value, $boolean);
    }
}
