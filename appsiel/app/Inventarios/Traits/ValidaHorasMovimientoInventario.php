<?php

namespace App\Inventarios\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidaHorasMovimientoInventario
{
    /**
     * Valida solo cuando la implementacion envia horas. Si no las envia, no
     * agrega valores por defecto y se conserva el comportamiento por fecha.
     */
    protected function validarYNormalizarHorasMovimiento(Request $request)
    {
        $campos = ['hora_inicio', 'hora_finalizacion'];
        $datos = [];

        foreach ($campos as $campo) {
            if (!$request->has($campo)) {
                continue;
            }

            $valor = trim((string)$request->input($campo));
            $datos[$campo] = $valor === '' ? null : $valor;
        }

        if (empty($datos)) {
            return;
        }

        // Laravel 5.2 no dispone de la regla nullable. Los nulos se excluyen
        // expresamente de la validacion y luego se conservan en el request.
        $datosValidar = array_filter($datos, function ($valor) {
            return !is_null($valor);
        });

        $validator = Validator::make($datosValidar, [
            'hora_inicio' => ['sometimes', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/'],
            'hora_finalizacion' => ['sometimes', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/']
        ], [
            'hora_inicio.regex' => 'La hora de inicio debe tener el formato HH:MM o HH:MM:SS.',
            'hora_finalizacion.regex' => 'La hora de finalizacion debe tener el formato HH:MM o HH:MM:SS.'
        ]);

        $validator->after(function ($validator) use ($datos) {
            if (!empty($datos['hora_inicio']) && !empty($datos['hora_finalizacion'])) {
                $inicio = $this->normalizarHoraMovimiento($datos['hora_inicio']);
                $fin = $this->normalizarHoraMovimiento($datos['hora_finalizacion']);

                if ($fin < $inicio) {
                    $validator->errors()->add('hora_finalizacion', 'La hora de finalizacion debe ser igual o posterior a la hora de inicio.');
                }
            }
        });

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        foreach ($datos as $campo => $valor) {
            $request->merge([$campo => is_null($valor) ? null : $this->normalizarHoraMovimiento($valor)]);
        }
    }

    private function normalizarHoraMovimiento($hora)
    {
        return strlen($hora) === 5 ? $hora . ':00' : $hora;
    }
}
