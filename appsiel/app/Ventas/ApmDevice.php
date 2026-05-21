<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ApmDevice extends Model
{
    protected $table = 'apm_devices';

    protected $fillable = [
        'device_type',
        'device_id',
        'device_name',
        'ip_address',
        'paper_width_mm',
        'code_page',
        'beep_after_print',
        'open_drawer_after_print',
        'cut_after_print',
        'serial_port',
        'baud_rate',
        'data_bits',
        'parity',
        'stop_bits',
        'estado'
    ];

    public $encabezado_tabla = [
        '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>',
        'Tipo',
        'ID APM',
        'Nombre',
        'IP',
        'Puerto COM',
        'Ancho papel',
        'Code page',
        'Pitar',
        'Abrir cajon',
        'Cortar',
        'Estado'
    ];

    public $urls_acciones = '{ "create":"web/create", "edit":"web/id_fila/edit", "eliminar":"web_eliminar/id_fila"}';

    public $archivo_js = 'assets/js/apm/devices.js';

    public static function consultar_registros($nro_registros, $search)
    {
        return self::select(
                'apm_devices.device_type AS campo1',
                'apm_devices.device_id AS campo2',
                'apm_devices.device_name AS campo3',
                'apm_devices.ip_address AS campo4',
                'apm_devices.serial_port AS campo5',
                'apm_devices.paper_width_mm AS campo6',
                'apm_devices.code_page AS campo7',
                'apm_devices.beep_after_print AS campo8',
                'apm_devices.open_drawer_after_print AS campo9',
                'apm_devices.cut_after_print AS campo10',
                'apm_devices.estado AS campo11',
                'apm_devices.id AS campo12'
            )
            ->where('apm_devices.device_type', 'LIKE', "%$search%")
            ->orWhere('apm_devices.device_id', 'LIKE', "%$search%")
            ->orWhere('apm_devices.device_name', 'LIKE', "%$search%")
            ->orWhere('apm_devices.ip_address', 'LIKE', "%$search%")
            ->orWhere('apm_devices.serial_port', 'LIKE', "%$search%")
            ->orWhere('apm_devices.estado', 'LIKE', "%$search%")
            ->orderBy('apm_devices.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = self::select(
                'apm_devices.device_type AS TIPO',
                'apm_devices.device_id AS ID_APM',
                'apm_devices.device_name AS NOMBRE',
                'apm_devices.ip_address AS IP',
                'apm_devices.serial_port AS PUERTO_COM',
                'apm_devices.paper_width_mm AS ANCHO_PAPEL',
                'apm_devices.code_page AS CODE_PAGE',
                'apm_devices.beep_after_print AS PITAR',
                'apm_devices.open_drawer_after_print AS ABRIR_CAJON',
                'apm_devices.cut_after_print AS CORTAR',
                'apm_devices.estado AS ESTADO'
            )
            ->where('apm_devices.device_type', 'LIKE', "%$search%")
            ->orWhere('apm_devices.device_id', 'LIKE', "%$search%")
            ->orWhere('apm_devices.device_name', 'LIKE', "%$search%")
            ->orWhere('apm_devices.ip_address', 'LIKE', "%$search%")
            ->orWhere('apm_devices.serial_port', 'LIKE', "%$search%")
            ->orWhere('apm_devices.estado', 'LIKE', "%$search%")
            ->orderBy('apm_devices.created_at', 'DESC')
            ->toSql();

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    public static function tituloExport()
    {
        return 'LISTADO DE DISPOSITIVOS APM';
    }

    public static function opciones_campo_select()
    {
        return self::activeOptions();
    }

    public static function printerOptions()
    {
        return self::activeOptions('printer');
    }

    public static function scaleOptions()
    {
        return self::activeOptions('scale');
    }

    public static function activeOptions($deviceType = null)
    {
        $query = self::where('estado', 'Activo')->orderBy('device_name')->orderBy('device_id');

        if (!is_null($deviceType)) {
            $query->where('device_type', $deviceType);
        }

        $vec = ['' => ''];
        foreach ($query->get() as $device) {
            $vec[$device->device_id] = $device->device_name . ' (' . $device->device_id . ')';
        }

        return $vec;
    }

    public static function frontConfigByDeviceIds(array $deviceIds)
    {
        $deviceIds = array_filter(array_unique($deviceIds));

        if (empty($deviceIds)) {
            return [];
        }

        $devices = self::whereIn('device_id', $deviceIds)->get();
        $config = [];

        foreach ($devices as $device) {
            $config[$device->device_id] = [
                'device_type' => $device->device_type,
                'device_id' => $device->device_id,
                'name' => $device->device_name,
                'serial_port' => $device->serial_port,
                'baud_rate' => (int) $device->baud_rate,
                'data_bits' => (int) $device->data_bits,
                'parity' => $device->parity,
                'stop_bits' => $device->stop_bits
            ];
        }

        return $config;
    }
}
