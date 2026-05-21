<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Ventas\ApmDevice;
use Illuminate\Support\Facades\Auth;

class ApmDeviceCommandController extends Controller
{
    const TEST_PERMISSION = 'vtas_apm_device_testing';
    const DEVICES_PERMISSION = 'vtas_apm_devices';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function beep($deviceId)
    {
        return $this->commandResponse($deviceId, 'Beep');
    }

    public function openDrawer($deviceId)
    {
        return $this->commandResponse($deviceId, 'OpenDrawer');
    }

    public function cut($deviceId)
    {
        return $this->commandResponse($deviceId, 'Cut');
    }

    protected function commandResponse($deviceId, $command)
    {
        if (!$this->userCanTestDevices()) {
            return response()->json(['message' => 'No tiene permiso para probar dispositivos APM.'], 403);
        }

        $device = ApmDevice::findOrFail($deviceId);

        if ($device->device_type !== 'printer') {
            return response()->json(['message' => 'El dispositivo seleccionado no es una impresora APM.'], 422);
        }

        if ($device->estado !== 'Activo') {
            return response()->json(['message' => 'La impresora APM seleccionada no esta activa.'], 422);
        }

        return response()->json([
            'action' => 'ExecuteCommand',
            'printer_id' => $device->device_id,
            'command' => $command
        ]);
    }

    protected function userCanTestDevices()
    {
        $user = Auth::user();

        return !is_null($user) && (
            $user->hasRole('SuperAdmin') ||
            $user->hasRole('Administrador') ||
            $user->can(self::TEST_PERMISSION) ||
            $user->can(self::DEVICES_PERMISSION)
        );
    }
}
