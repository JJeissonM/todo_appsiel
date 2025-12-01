<?php

namespace App\Nomina\Services;

use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\RegistroTurno;

class LiquidacionPorTurnosService
{
    public function almacenar_registro_empleado(NomContrato $empleado, NomDocEncabezado $documento, $usuario)
    {
        $lapso = $documento->lapso();

        $registros_turnos = RegistroTurno::where('contrato_id', $empleado->id)
            ->where('fecha', '>=', $lapso->fecha_inicial)
            ->where('fecha', '<=', $lapso->fecha_final)
            ->where('estado', 'Pendiente')
            ->get();

        $devengo_turnos = 0;
        foreach ($registros_turnos as $registro_turno) {
            $devengo_turnos += $registro_turno->valor;
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

        $registros_turnos = RegistroTurno::where('contrato_id', $empleado->id)
            ->where('fecha', '>=', $lapso->fecha_inicial)
            ->where('fecha', '<=', $lapso->fecha_final)
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
}
