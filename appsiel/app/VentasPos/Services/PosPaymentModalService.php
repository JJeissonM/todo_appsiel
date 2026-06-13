<?php

namespace App\VentasPos\Services;

use App\Http\Controllers\Tesoreria\RecaudoController;
use App\Tesoreria\TesoMedioRecaudoDestino;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use Illuminate\Support\Facades\Schema;

class PosPaymentModalService
{
    public function buildData()
    {
        $mediosRecaudo = RecaudoController::get_medios_recaudo();
        $cajas = TesoCaja::opciones_campo_select();
        $cuentasBancarias = TesoCuentaBancaria::opciones_campo_select();
        $usarModalBotones = $this->usarModalBotones();
        $filtrarDestinosPorMedioRecaudo = $this->filtrarDestinosPorMedioRecaudo();

        return [
            'medios_recaudo' => $mediosRecaudo,
            'cajas' => $cajas,
            'cuentas_bancarias' => $cuentasBancarias,
            'usar_modal_botones' => $usarModalBotones,
            'filtrar_destinos_por_medio_recaudo' => $filtrarDestinosPorMedioRecaudo,
            'destinos_medios_recaudo_data' => $this->buildDestinationsData($mediosRecaudo, $cajas, $cuentasBancarias, $filtrarDestinosPorMedioRecaudo),
            'modal_botones_data' => $usarModalBotones
                ? $this->getModalButtonsData($mediosRecaudo, $cajas, $cuentasBancarias, $filtrarDestinosPorMedioRecaudo)
                : ['medios' => [], 'destinos' => []]
        ];
    }

    public function usarModalBotones()
    {
        return (int)config('ventas_pos.usar_modal_botones_medios_pago') === 1;
    }

    public function filtrarDestinosPorMedioRecaudo()
    {
        if (!Schema::hasTable('teso_medios_recaudo_destinos')) {
            return false;
        }

        return TesoMedioRecaudoDestino::where('estado', 'Activo')->count() > 0;
    }

    public function buildDestinationsData(array $mediosRecaudo, array $cajas, array $cuentasBancarias, $filtrarDestinosPorMedioRecaudo = null)
    {
        $filtrar = is_null($filtrarDestinosPorMedioRecaudo) ?
            $this->filtrarDestinosPorMedioRecaudo() :
            (bool)$filtrarDestinosPorMedioRecaudo;

        $mapaCajas = $this->buildOptionsMap($cajas);
        $mapaCuentas = $this->buildOptionsMap($cuentasBancarias);
        $mapaDestinos = $filtrar ? $this->getDestinationsMap($mapaCajas, $mapaCuentas) : [];
        $data = [
            'activo' => $filtrar,
            'destinos' => []
        ];

        foreach ($mediosRecaudo as $value => $label) {
            if ($value === '') {
                continue;
            }

            $partes = explode('-', $value, 2);
            $medioId = (int)$partes[0];

            $data['destinos'][$medioId] = isset($mapaDestinos[$medioId]) ?
                $mapaDestinos[$medioId] :
                ['cajas' => [], 'cuentas' => []];
        }

        return $data;
    }

    public function getModalButtonsData(array $mediosRecaudo, array $cajas, array $cuentasBancarias, $filtrarDestinosPorMedioRecaudo = null)
    {
        $data = [
            'medios' => [],
            'destinos' => []
        ];

        $mapaCajas = $this->buildOptionsMap($cajas);
        $mapaCuentas = $this->buildOptionsMap($cuentasBancarias);
        $filtrar = is_null($filtrarDestinosPorMedioRecaudo) ?
            $this->filtrarDestinosPorMedioRecaudo() :
            (bool)$filtrarDestinosPorMedioRecaudo;
        $mapaDestinos = $filtrar ? $this->getDestinationsMap($mapaCajas, $mapaCuentas) : [];

        foreach ($mediosRecaudo as $value => $label) {
            if ($value === '') {
                continue;
            }

            $partes = explode('-', $value, 2);
            $medioId = (int)$partes[0];
            $comportamiento = isset($partes[1]) ? $partes[1] : '';

            $data['medios'][] = [
                'id' => $medioId,
                'value' => $value,
                'label' => $label
            ];

            if ($filtrar) {
                $data['destinos'][$medioId] = isset($mapaDestinos[$medioId]) ?
                    $mapaDestinos[$medioId] :
                    ['cajas' => [], 'cuentas' => []];
            } else {
                $data['destinos'][$medioId] = $this->getFallbackDestinations($comportamiento, $mapaCajas, $mapaCuentas);
            }
        }

        return $data;
    }

    protected function buildOptionsMap(array $options)
    {
        $map = [];

        foreach ($options as $id => $label) {
            if ($id === '') {
                continue;
            }

            $map[(int)$id] = [
                'id' => (int)$id,
                'value' => $id . '-' . $label,
                'label' => $label
            ];
        }

        return $map;
    }

    protected function getDestinationsMap(array $mapaCajas, array $mapaCuentas)
    {
        if (!Schema::hasTable('teso_medios_recaudo_destinos')) {
            return [];
        }

        $destinos = [];

        $registros = TesoMedioRecaudoDestino::where('estado', 'Activo')
            ->orderBy('teso_medio_recaudo_id')
            ->orderBy('teso_caja_id')
            ->orderBy('teso_cuenta_bancaria_id')
            ->get();

        foreach ($registros as $registro) {
            $medioId = (int)$registro->teso_medio_recaudo_id;

            if (!isset($destinos[$medioId])) {
                $destinos[$medioId] = [
                    'cajas' => [],
                    'cuentas' => []
                ];
            }

            if ((int)$registro->teso_caja_id !== 0 && isset($mapaCajas[(int)$registro->teso_caja_id])) {
                $destinos[$medioId]['cajas'][(int)$registro->teso_caja_id] = $mapaCajas[(int)$registro->teso_caja_id];
            }

            if ((int)$registro->teso_cuenta_bancaria_id !== 0 && isset($mapaCuentas[(int)$registro->teso_cuenta_bancaria_id])) {
                $destinos[$medioId]['cuentas'][(int)$registro->teso_cuenta_bancaria_id] = $mapaCuentas[(int)$registro->teso_cuenta_bancaria_id];
            }
        }

        foreach ($destinos as $medioId => $items) {
            $destinos[$medioId]['cajas'] = array_values($items['cajas']);
            $destinos[$medioId]['cuentas'] = array_values($items['cuentas']);
        }

        foreach ($destinos as $medioId => $items) {
            if (empty($items['cajas']) && empty($items['cuentas'])) {
                unset($destinos[$medioId]);
            }
        }

        return $destinos;
    }

    protected function getFallbackDestinations($comportamiento, array $mapaCajas, array $mapaCuentas)
    {
        if ($comportamiento === 'Tarjeta bancaria') {
            return [
                'cajas' => [],
                'cuentas' => array_values($mapaCuentas)
            ];
        }

        return [
            'cajas' => array_values($mapaCajas),
            'cuentas' => []
        ];
    }
}
