<?php

namespace App\Nomina\Services;

use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\RegistroTurno;
use Carbon\Carbon;

class LiquidacionPorTurnosService
{
    public function almacenar_registro_empleado(NomContrato $empleado, NomDocEncabezado $documento, $usuario)
    {
        $lapso = $documento->lapso();
        $fecha_final_turnos = $this->get_fecha_final_turnos($documento, $lapso->fecha_final);

        $registros_turnos = RegistroTurno::where('contrato_id', $empleado->id)
            ->where('fecha', '>=', $lapso->fecha_inicial)
            ->where('fecha', '<=', $fecha_final_turnos)
            ->where('estado', 'Pendiente')
            ->get();

        $devengo_turnos = 0;
        $cantidad_horas = 0;
        $horas_dia_laboral = (float)config('nomina.horas_dia_laboral');
        foreach ($registros_turnos as $registro_turno)
        {
            $devengo_turnos += $registro_turno->valor;
            $cantidad_horas += $horas_dia_laboral;
            // Actualizamos el estado del registro de turno a 'Liquidado'
            $registro_turno->estado = 'Liquidado';
            $registro_turno->save();
        }

        if ($devengo_turnos != 0) {
            NomDocRegistro::create(
                ['nom_doc_encabezado_id' => $documento->id] +
                    ['fecha' => $documento->fecha] +
                    ['core_empresa_id' => $documento->core_empresa_id] +
                    ['nom_concepto_id' => (int)config('nomina.concepto_pago_turnos')] +
                    ['core_tercero_id' => $empleado->core_tercero_id] +
                    ['nom_contrato_id' => $empleado->id] +
                    ['estado' => 'Activo'] +
                    ['creado_por' => $usuario->email] +
                    ['modificado_por' => ''] +
                    ['cantidad_horas' => $cantidad_horas] +
                    [
                        'valor_devengo' => $devengo_turnos,
                        'valor_deduccion' => 0
                    ]
            );

            return true;
        }

        return false;
    }

    public function retirar_registro_empleado(NomContrato $empleado, NomDocEncabezado $documento)
    {
        $lapso = $documento->lapso();
        $fecha_final_turnos = $this->get_fecha_final_turnos($documento, $lapso->fecha_final);

        $registros_turnos = RegistroTurno::where('contrato_id', $empleado->id)
            ->where('fecha', '>=', $lapso->fecha_inicial)
            ->where('fecha', '<=', $fecha_final_turnos)
            ->where('estado', 'Liquidado')
            ->get();

        foreach ($registros_turnos as $registro_turno) {
            // Actualizamos el estado del registro de turno a 'Pendiente'
            $registro_turno->estado = 'Pendiente';
            $registro_turno->save();
        }

        NomDocRegistro::where([
                                ['nom_doc_encabezado_id' , $documento->id],
                                ['nom_concepto_id' , (int)config('nomina.concepto_pago_turnos')],
                                ['nom_contrato_id' , $empleado->id]
                            ])
                        ->delete();
        
    }

    protected function get_fecha_final_turnos(NomDocEncabezado $documento, string $fecha_final_lapso)
    {
        $fecha_final_turnos = Carbon::createFromFormat('Y-m-d', $fecha_final_lapso);
        $fecha_documento = Carbon::createFromFormat('Y-m-d', $documento->fecha);

        if (
            $fecha_final_turnos->day === 30 &&
            $fecha_documento->year === $fecha_final_turnos->year &&
            $fecha_documento->month === $fecha_final_turnos->month &&
            $fecha_documento->copy()->endOfMonth()->day === 31
        ) {
            return $fecha_final_turnos->copy()->addDay()->toDateString();
        }

        return $fecha_final_turnos->toDateString();
    }
}
