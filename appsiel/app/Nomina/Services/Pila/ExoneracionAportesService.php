<?php

namespace App\Nomina\Services\Pila;

use App\Nomina\NomContrato;
use App\Nomina\PilaDatosEmpresa;

class ExoneracionAportesService
{
    const MODO_AUTO = 'auto';
    const MODO_SI = 'si';
    const MODO_NO = 'no';

    public function getReglas(PilaDatosEmpresa $datosEmpresa, NomContrato $empleado, $ibc, $smmlv)
    {
        $ibc = (float)$ibc;
        $smmlv = (float)$smmlv;
        $modo = $this->normalizarModo($datosEmpresa->modo_exoneracion_aportes);
        $superaTope = $smmlv > 0 && $ibc >= 10 * $smmlv;
        $excluidoPorTipoEmpleado = (bool)$empleado->es_pasante_sena || (bool)$empleado->salario_integral;
        $empresaExonerada = $this->empresaPuedeExonerar($datosEmpresa, $modo);
        $exonerado = $empresaExonerada && !$superaTope && !$excluidoPorTipoEmpleado;

        return (object)[
            'modo' => $modo,
            'empresa_exonerada' => $empresaExonerada,
            'cotizante_exonerado' => $exonerado,
            'supera_tope_10_smmlv' => $superaTope,
            'excluido_por_tipo_empleado' => $excluidoPorTipoEmpleado,
            'tarifa_salud_total' => $this->getTarifaSaludTotal($datosEmpresa, $exonerado),
            'tarifa_salud_empresa' => $exonerado ? 0 : (float)$datosEmpresa->porcentaje_eps_empresa / 100,
            'aporta_sena' => !$exonerado && !$empleado->es_pasante_sena && (float)$datosEmpresa->porcentaje_sena > 0,
            'aporta_icbf' => !$exonerado && !$empleado->es_pasante_sena && (float)$datosEmpresa->porcentaje_icbf > 0,
        ];
    }

    protected function normalizarModo($modo)
    {
        $modo = strtolower(trim((string)$modo));

        if (in_array($modo, [self::MODO_SI, 's', '1', 'true'])) {
            return self::MODO_SI;
        }

        if (in_array($modo, [self::MODO_NO, 'n', '0', 'false'])) {
            return self::MODO_NO;
        }

        return self::MODO_AUTO;
    }

    protected function empresaPuedeExonerar(PilaDatosEmpresa $datosEmpresa, $modo)
    {
        if ($modo == self::MODO_SI) {
            return true;
        }

        if ($modo == self::MODO_NO) {
            return false;
        }

        return (float)$datosEmpresa->porcentaje_eps_empresa == 0;
    }

    protected function getTarifaSaludTotal(PilaDatosEmpresa $datosEmpresa, $exonerado)
    {
        if ($exonerado) {
            return 4 / 100;
        }

        return ((float)$datosEmpresa->porcentaje_eps_empresa + 4) / 100;
    }
}
