<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Nomina\CambioSalario;
use App\Nomina\GrupoEmpleado;
use App\Nomina\NomActualizacionSueldo;
use App\Nomina\NomActualizacionSueldoDetalle;
use App\Nomina\NomContrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ActualizacionSueldosController extends Controller
{
    public function cargar_empleados(Request $request)
    {
        $porcentaje = (float)$request->porcentaje_aumento;
        $grupo_empleado_id = $request->grupo_empleado_id;

        if ($porcentaje <= 0) {
            return '<div class="alert alert-warning">Debe ingresar un porcentaje de aumento mayor a 0.</div>';
        }

        $query = NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
            ->leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_contratos.grupo_empleado_id')
            ->where('nom_contratos.estado', 'Activo')
            ->select(
                'nom_contratos.id AS contrato_id',
                'nom_contratos.sueldo',
                'nom_contratos.fecha_ingreso',
                'nom_contratos.grupo_empleado_id',
                'core_terceros.descripcion AS empleado',
                'nom_cargos.descripcion AS cargo',
                'nom_grupos_empleados.descripcion AS grupo_empleado'
            )
            ->orderBy('core_terceros.descripcion');

        if (!empty($grupo_empleado_id) && (int)$grupo_empleado_id > 0) {
            $query->where('nom_contratos.grupo_empleado_id', (int)$grupo_empleado_id);
        }

        $contratos = $query->get();

        $lineas = [];
        $factor = 1 + ($porcentaje / 100);
        foreach ($contratos as $contrato) {
            $lineas[] = (object)[
                'contrato_id' => $contrato->contrato_id,
                'grupo_empleado' => $contrato->grupo_empleado,
                'empleado' => $contrato->empleado,
                'cargo' => $contrato->cargo,
                'fecha_ingreso' => $contrato->fecha_ingreso,
                'sueldo' => (float)$contrato->sueldo,
                'nuevo_sueldo' => round((float)$contrato->sueldo * $factor, 2)
            ];
        }

        $grupo = null;
        if (!empty($grupo_empleado_id) && (int)$grupo_empleado_id > 0) {
            $grupo = GrupoEmpleado::find((int)$grupo_empleado_id);
        }

        return View::make('nomina.procesos.incluir.tabla_actualizacion_sueldos', [
            'lineas' => $lineas,
            'grupo' => $grupo,
            'grupo_empleado_id' => (int)$grupo_empleado_id,
            'porcentaje' => $porcentaje
        ])->render();
    }

    public function guardar(Request $request)
    {
        $contratos = $request->nom_contrato_id;
        $salarios_anteriores = $request->salario_anterior;
        $nuevos_salarios = $request->nuevo_sueldo;
        $porcentaje = (float)$request->porcentaje;
        $grupo_empleado_id = (int)$request->grupo_empleado_id;

        if (empty($contratos)) {
            return '<div class="alert alert-warning">No hay empleados para actualizar.</div>';
        }

        $proceso = null;
        $cantidad = 0;

        DB::transaction(function () use (
            $contratos,
            $salarios_anteriores,
            $nuevos_salarios,
            $porcentaje,
            $grupo_empleado_id,
            &$proceso,
            &$cantidad
        ) {
            $proceso = NomActualizacionSueldo::create([
                'core_empresa_id' => Auth::user()->empresa_id,
                'grupo_empleado_id' => $grupo_empleado_id > 0 ? $grupo_empleado_id : null,
                'porcentaje' => $porcentaje,
                'fecha' => date('Y-m-d'),
                'estado' => 'Aplicado',
                'observacion' => '',
                'creado_por' => Auth::user()->email,
                'modificado_por' => ''
            ]);

            foreach ($contratos as $index => $contrato_id) {
                $contrato = NomContrato::find((int)$contrato_id);
                if (is_null($contrato)) {
                    continue;
                }

                $nuevo_salario = isset($nuevos_salarios[$index]) ? (float)$nuevos_salarios[$index] : 0;
                if ($nuevo_salario <= 0) {
                    continue;
                }

                $salario_anterior_real = (float)$contrato->sueldo;

                NomActualizacionSueldoDetalle::create([
                    'nom_actualizacion_sueldo_id' => $proceso->id,
                    'nom_contrato_id' => $contrato->id,
                    'salario_anterior' => $salario_anterior_real,
                    'salario_nuevo' => $nuevo_salario,
                    'aplicado' => 1,
                    'revertido' => 0
                ]);

                $contrato->sueldo = $nuevo_salario;
                $contrato->save();

                CambioSalario::create([
                    'nom_contrato_id' => $contrato->id,
                    'salario_anterior' => $salario_anterior_real,
                    'nuevo_salario' => $nuevo_salario,
                    'fecha_modificacion' => date('Y-m-d'),
                    'tipo_modificacion' => 'proceso_actualizacion_sueldos',
                    'observacion' => 'Proceso #' . $proceso->id,
                    'creado_por' => Auth::user()->email,
                    'modificado_por' => ''
                ]);

                $cantidad++;
            }
        });

        return View::make('nomina.procesos.incluir.resultado_actualizacion_sueldos', [
            'proceso' => $proceso,
            'cantidad' => $cantidad
        ])->render();
    }

    public function revertir(Request $request, $proceso_id)
    {
        $proceso = NomActualizacionSueldo::with('detalles')->find($proceso_id);
        if (is_null($proceso)) {
            return '<div class="alert alert-danger">No se encontró el proceso solicitado.</div>';
        }

        if ($proceso->estado == 'Revertido') {
            return '<div class="alert alert-warning">El proceso ya fue revertido.</div>';
        }

        $cantidad = 0;

        DB::transaction(function () use ($proceso, &$cantidad) {
            foreach ($proceso->detalles as $detalle) {
                $contrato = NomContrato::find((int)$detalle->nom_contrato_id);
                if (is_null($contrato)) {
                    continue;
                }

                $salario_anterior_real = (float)$contrato->sueldo;
                $contrato->sueldo = $detalle->salario_anterior;
                $contrato->save();

                $detalle->revertido = 1;
                $detalle->save();

                CambioSalario::create([
                    'nom_contrato_id' => $contrato->id,
                    'salario_anterior' => $salario_anterior_real,
                    'nuevo_salario' => (float)$detalle->salario_anterior,
                    'fecha_modificacion' => date('Y-m-d'),
                    'tipo_modificacion' => 'reversion_proceso_actualizacion_sueldos',
                    'observacion' => 'Reversión proceso #' . $proceso->id,
                    'creado_por' => Auth::user()->email,
                    'modificado_por' => ''
                ]);

                $cantidad++;
            }

            $proceso->estado = 'Revertido';
            $proceso->modificado_por = Auth::user()->email;
            $proceso->save();
        });

        return View::make('nomina.procesos.incluir.resultado_actualizacion_sueldos', [
            'proceso' => $proceso,
            'cantidad' => $cantidad,
            'revertido' => true
        ])->render();
    }
}
