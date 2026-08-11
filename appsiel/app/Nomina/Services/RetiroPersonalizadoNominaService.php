<?php

namespace App\Nomina\Services;

use App\Nomina\Exceptions\RetiroPersonalizadoException;
use App\Nomina\ModosLiquidacion\ModoLiquidacion;
use App\Nomina\ModosLiquidacion\ModoLiquidacionPrestacion;
use App\Nomina\NomCuota;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomDocRegistro;
use App\Nomina\NomPrestamo;
use App\Nomina\NovedadTnl;
use Illuminate\Support\Facades\DB;

class RetiroPersonalizadoNominaService
{
    /**
     * Retorna únicamente combinaciones que realmente existen en el documento.
     */
    public function opciones(NomDocEncabezado $documento)
    {
        $filas = NomDocRegistro::join('nom_contratos', 'nom_contratos.id', '=', 'nom_doc_registros.nom_contrato_id')
            ->join('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->join('nom_conceptos', 'nom_conceptos.id', '=', 'nom_doc_registros.nom_concepto_id')
            ->leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_contratos.grupo_empleado_id')
            ->where('nom_doc_registros.nom_doc_encabezado_id', $documento->id)
            ->where('nom_doc_registros.core_empresa_id', $documento->core_empresa_id)
            ->select(
                DB::raw('COALESCE(nom_contratos.grupo_empleado_id, 0) AS grupo_id'),
                DB::raw("COALESCE(nom_grupos_empleados.descripcion, 'Sin grupo') AS grupo"),
                'nom_contratos.id AS contrato_id',
                'core_terceros.numero_identificacion AS identificacion',
                'core_terceros.descripcion AS empleado',
                'nom_conceptos.id AS concepto_id',
                'nom_conceptos.descripcion AS concepto',
                DB::raw('COUNT(nom_doc_registros.id) AS cantidad')
            )
            ->groupBy(
                'nom_contratos.grupo_empleado_id',
                'nom_grupos_empleados.descripcion',
                'nom_contratos.id',
                'core_terceros.numero_identificacion',
                'core_terceros.descripcion',
                'nom_conceptos.id',
                'nom_conceptos.descripcion'
            )
            ->orderBy('grupo')
            ->orderBy('empleado')
            ->orderBy('concepto')
            ->get();

        return $filas->map(function ($fila) {
            return [
                'grupo_id' => (int) $fila->grupo_id,
                'grupo' => $fila->grupo,
                'contrato_id' => (int) $fila->contrato_id,
                'identificacion' => $fila->identificacion,
                'empleado' => $fila->empleado,
                'concepto_id' => (int) $fila->concepto_id,
                'concepto' => $fila->concepto,
                'cantidad' => (int) $fila->cantidad,
            ];
        })->values()->all();
    }

    public function retirar(NomDocEncabezado $documento, $grupoEmpleadoId, $contratoId, $conceptoId, $cantidadEsperada = null)
    {
        $documentoId = (int) $documento->id;
        $grupoEmpleadoId = is_null($grupoEmpleadoId) || $grupoEmpleadoId === '' ? null : (int) $grupoEmpleadoId;
        $contratoId = is_null($contratoId) || $contratoId === '' ? null : (int) $contratoId;
        $conceptoId = is_null($conceptoId) || $conceptoId === '' ? null : (int) $conceptoId;
        $cantidadEsperada = is_null($cantidadEsperada) ? null : (int) $cantidadEsperada;
        $resultado = [];

        if (is_null($grupoEmpleadoId) && is_null($contratoId) && is_null($conceptoId)) {
            throw new RetiroPersonalizadoException('Debe seleccionar al menos un grupo, empleado o concepto.');
        }

        DB::transaction(function () use ($documentoId, $grupoEmpleadoId, $contratoId, $conceptoId, $cantidadEsperada, &$resultado) {
            $documentoBloqueado = NomDocEncabezado::where('id', $documentoId)->lockForUpdate()->first();
            if (is_null($documentoBloqueado)) {
                throw new RetiroPersonalizadoException('El documento de nómina no existe.');
            }

            if (!$documentoBloqueado->esta_activo_para_transacciones()) {
                throw new RetiroPersonalizadoException('El documento de nómina no puede modificarse porque no está en estado Activo.');
            }

            $query = NomDocRegistro::with([
                    'concepto', 'contrato', 'encabezado_documento'
                ])
                ->join('nom_contratos', 'nom_contratos.id', '=', 'nom_doc_registros.nom_contrato_id')
                ->select('nom_doc_registros.*')
                ->where('nom_doc_registros.nom_doc_encabezado_id', $documentoBloqueado->id)
                ->where('nom_doc_registros.core_empresa_id', $documentoBloqueado->core_empresa_id);

            if (!is_null($grupoEmpleadoId)) {
                if ($grupoEmpleadoId === 0) {
                    $query->whereNull('nom_contratos.grupo_empleado_id');
                } else {
                    $query->where('nom_contratos.grupo_empleado_id', $grupoEmpleadoId);
                }
            }

            if (!is_null($contratoId)) {
                $query->where('nom_doc_registros.nom_contrato_id', $contratoId);
            }

            if (!is_null($conceptoId)) {
                $query->where('nom_doc_registros.nom_concepto_id', $conceptoId);
            }

            $registros = $query->orderBy('nom_doc_registros.id')->lockForUpdate()->get();
            if ($registros->isEmpty()) {
                throw new RetiroPersonalizadoException('No hay registros que coincidan con los filtros seleccionados.');
            }

            if (!is_null($cantidadEsperada) && $registros->count() !== $cantidadEsperada) {
                throw new RetiroPersonalizadoException(
                    'Los registros del documento cambiaron después de abrir la ventana. Actualice la página y confirme nuevamente.'
                );
            }

            $totalDevengos = (float) $registros->sum('valor_devengo');
            $totalDeducciones = (float) $registros->sum('valor_deduccion');

            foreach ($registros as $registro) {
                try {
                    $this->bloquearAsociaciones($registro);
                    $this->revertirRegistro($registro, $documentoBloqueado);
                } catch (RetiroPersonalizadoException $e) {
                    throw $e;
                } catch (\RuntimeException $e) {
                    if ($e instanceof \PDOException) {
                        throw $e;
                    }

                    throw new RetiroPersonalizadoException(
                        'No se pudo revertir el registro #' . $registro->id .
                        ' (' . $registro->concepto->descripcion . '): ' . $e->getMessage(),
                        0,
                        $e
                    );
                }

                if (NomDocRegistro::where('id', $registro->id)->exists()) {
                    throw new RetiroPersonalizadoException(
                        'No se pudo revertir completamente el registro #' . $registro->id . '. No se realizó ningún retiro.'
                    );
                }
            }

            $documentoBloqueado->actualizar_totales();

            $resultado = [
                'cantidad_retirada' => $registros->count(),
                'total_devengos_retirado' => $totalDevengos,
                'total_deducciones_retirado' => $totalDeducciones,
                'total_devengos_documento' => (float) $documentoBloqueado->total_devengos,
                'total_deducciones_documento' => (float) $documentoBloqueado->total_deducciones,
            ];
        });

        return $resultado;
    }

    protected function bloquearAsociaciones(NomDocRegistro $registro)
    {
        if ($this->tieneIdAsociado($registro->nom_cuota_id)) {
            $registro->setRelation('cuota', NomCuota::where('id', $registro->nom_cuota_id)->lockForUpdate()->first());
        }

        if ($this->tieneIdAsociado($registro->nom_prestamo_id)) {
            $registro->setRelation('prestamo', NomPrestamo::where('id', $registro->nom_prestamo_id)->lockForUpdate()->first());
        }

        if ($this->tieneIdAsociado($registro->novedad_tnl_id)) {
            $registro->setRelation('novedad_tnl', NovedadTnl::where('id', $registro->novedad_tnl_id)->lockForUpdate()->first());
        }
    }

    protected function tieneIdAsociado($id)
    {
        return !is_null($id) && (int) $id > 0;
    }

    protected function revertirRegistro(NomDocRegistro $registro, NomDocEncabezado $documento)
    {
        if (is_null($registro->concepto) || is_null($registro->contrato)) {
            throw new RetiroPersonalizadoException(
                'El registro #' . $registro->id . ' tiene un concepto o contrato inconsistente.'
            );
        }

        $modoLiquidacionId = (int) $registro->concepto->modo_liquidacion_id;

        if ((int) $registro->nom_concepto_id === (int) config('nomina.concepto_pago_turnos')) {
            (new LiquidacionPorTurnosService())->retirar_registro_empleado($registro->contrato, $documento, $registro);
            return;
        }

        // Las asociaciones tienen prioridad sobre una parametrización de modo incorrecta.
        if ($this->tieneIdAsociado($registro->nom_cuota_id)) {
            $this->retirarModoAutomatico(3, $registro);
            return;
        }

        if ($this->tieneIdAsociado($registro->nom_prestamo_id)) {
            $this->retirarModoAutomatico(4, $registro);
            return;
        }

        if ($this->tieneIdAsociado($registro->novedad_tnl_id)) {
            if (!is_null($registro->novedad_tnl) && $registro->novedad_tnl->tipo_novedad_tnl === 'vacaciones') {
                $this->retirarPrestacion('vacaciones', $registro);
            } else {
                $this->retirarModoAutomatico(7, $registro);
            }
            return;
        }

        $prestacion = $this->prestacionSegunModo($modoLiquidacionId);
        if (!is_null($prestacion)) {
            $this->retirarPrestacion($prestacion, $registro);
            return;
        }

        if ($modoLiquidacionId === 7 && $documento->tipo_liquidacion === 'terminacion_contrato') {
            // En liquidaciones de retiro pueden existir líneas TNL calculadas sin novedad.
            $registro->delete();
            return;
        }

        $fachada = new ModoLiquidacion();
        if ($fachada->soporta($modoLiquidacionId)) {
            $fachada->retirar($modoLiquidacionId, $registro);
            return;
        }

        // Manuales, cruces, provisiones y otros modos sin acumulados asociados.
        $registro->delete();
    }

    protected function retirarModoAutomatico($modoLiquidacionId, NomDocRegistro $registro)
    {
        $fachada = new ModoLiquidacion();
        if (!$fachada->soporta((int) $modoLiquidacionId)) {
            throw new RetiroPersonalizadoException('El modo de liquidación no tiene una estrategia de reversión configurada.');
        }

        $fachada->retirar((int) $modoLiquidacionId, $registro);
    }

    protected function retirarPrestacion($prestacion, NomDocRegistro $registro)
    {
        (new ModoLiquidacionPrestacion())->retirar($prestacion, $registro);
    }

    protected function prestacionSegunModo($modoLiquidacionId)
    {
        $prestaciones = [
            14 => 'prima_legal',
            15 => 'cesantias',
            16 => 'intereses_cesantias',
            17 => 'cesantias',
        ];

        return isset($prestaciones[$modoLiquidacionId]) ? $prestaciones[$modoLiquidacionId] : null;
    }
}
