<?php

namespace App\Nomina\Services;

use App\Nomina\ParametroLegal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ParametroLegalService
{
    public function getParametrosParaFecha($fecha)
    {
        $fechaPeriodo = Carbon::parse($fecha)->format('Y-m-d');

        $parametro = $this->buscarEnBaseDatos($fechaPeriodo);
        if (!is_null($parametro)) {
            return $this->normalizarParametro($parametro);
        }

        $parametroPorAnio = $this->getParametroPorAnio((int)substr($fechaPeriodo, 0, 4));
        if (!is_null($parametroPorAnio)) {
            return $parametroPorAnio;
        }

        return (object)[
            'id' => null,
            'smmlv' => (float)config('nomina.SMMLV'),
            'auxilio_transporte' => 0,
            'uvt' => (float)config('nomina.valor_uvt_actual'),
            'horas_laborales' => (float)config('nomina.horas_laborales'),
            'horas_dia_laboral' => (float)config('nomina.horas_dia_laboral'),
            'normatividad' => 'Configuracion general de nomina',
        ];
    }

    protected function buscarEnBaseDatos($fecha)
    {
        if (!Schema::hasTable('nom_parametros_legales')) {
            return null;
        }

        return ParametroLegal::where('estado', 'Activo')
            ->where('fecha_inicio', '<=', $fecha)
            ->where(function ($query) use ($fecha) {
                $query->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', $fecha);
            })
            ->orderBy('fecha_inicio', 'DESC')
            ->first();
    }

    protected function normalizarParametro(ParametroLegal $parametro)
    {
        return (object)[
            'id' => $parametro->id,
            'smmlv' => (float)$parametro->smmlv,
            'auxilio_transporte' => (float)$parametro->auxilio_transporte,
            'uvt' => (float)$parametro->uvt,
            'horas_laborales' => (float)$parametro->horas_laborales,
            'horas_dia_laboral' => (float)$parametro->horas_dia_laboral,
            'normatividad' => $parametro->normatividad,
        ];
    }

    protected function getParametroPorAnio($anio)
    {
        $horasLaborales = (float)config('nomina.horas_laborales');
        $horasDiaLaboral = (float)config('nomina.horas_dia_laboral');

        $parametros = [
            2024 => [
                'smmlv' => 1300000,
                'auxilio_transporte' => 162000,
                'uvt' => 47065,
                'normatividad' => 'Decretos 2292 y 2293 de 2023 / Resolucion DIAN 187 de 2023',
            ],
            2025 => [
                'smmlv' => 1423500,
                'auxilio_transporte' => 200000,
                'uvt' => 49799,
                'normatividad' => 'Decretos 1572 y 1573 de 2024 / Resolucion DIAN 193 de 2024',
            ],
        ];

        if (!array_key_exists($anio, $parametros)) {
            return null;
        }

        return (object)[
            'id' => null,
            'smmlv' => (float)$parametros[$anio]['smmlv'],
            'auxilio_transporte' => (float)$parametros[$anio]['auxilio_transporte'],
            'uvt' => (float)$parametros[$anio]['uvt'],
            'horas_laborales' => $horasLaborales,
            'horas_dia_laboral' => $horasDiaLaboral,
            'normatividad' => $parametros[$anio]['normatividad'],
        ];
    }
}
