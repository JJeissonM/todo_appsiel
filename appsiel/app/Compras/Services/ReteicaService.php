<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasRetencionLiquidacion;
use App\Contabilidad\Retencion;
use App\Contabilidad\RegistroRetencion;
use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;
use App\Core\Tercero;
use App\CxP\CxpMovimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReteicaService
{
    public function habilitado()
    {
        $valor = config('compras.maneja_retenciones_fuente', '0');
        if (is_bool($valor)) {
            return $valor;
        }
        return in_array(strtolower(trim((string)$valor)), ['1', 'si', 'sí', 'true', 'activo', 'habilitado'], true);
    }

    public function retenciones_activas()
    {
        if (!$this->habilitado()) {
            return collect();
        }
        $categoria = (int)config('contabilidad.categoria_reteica_id', 0);
        if (!$categoria || !\App\Contabilidad\CategoriaRetencion::where('id', $categoria)->where('estado', 'Activo')->exists()) {
            return collect();
        }
        return Retencion::where('categoria_retenciones_id', $categoria)
            ->where('estado', 'Activo')->orderBy('tasa_retencion')->get();
    }

    // Las tasas contables siempre se almacenan en porcentaje: 4.14 por mil = 0.414%.
    public function calcular($base, $tasa)
    {
        if (!is_numeric($base) || !is_numeric($tasa) || !is_finite((float)$base)
            || !is_finite((float)$tasa) || $base < 0 || $tasa <= 0 || $tasa > 100) {
            throw new \InvalidArgumentException('Base o tarifa de ReteICA inválida.');
        }
        return round(round((float)$base, 2) * (float)$tasa / 100, 2);
    }

    public function validar_seleccion($id, $tipo)
    {
        if (!$id) {
            return null;
        }
        if (!$this->habilitado()) {
            throw new \InvalidArgumentException('Para aplicar ReteICA debe activar el manejo de retenciones en la configuración de Compras.');
        }
        $retencion = $this->retenciones_activas()->where('id', (int)$id)->first();
        if (!in_array((int)$tipo, [25, 48]) || !$retencion) {
            throw new \InvalidArgumentException('Seleccione una retención activa de la categoría ReteICA configurada.');
        }
        if (!ContabCuenta::where('id', $retencion->cta_compras_id)->where('estado', 'Activo')->exists()) {
            throw new \InvalidArgumentException('Configure una cuenta de compras activa para la retención ReteICA.');
        }
        if (!Tercero::where('id', (int)config('contabilidad.tercero_reteica_id'))->where('estado', 'Activo')->exists()) {
            throw new \InvalidArgumentException('Configure en Contabilidad el tercero recaudador municipal de ReteICA.');
        }
        $this->calcular(0, $retencion->tasa_retencion);
        return $retencion;
    }

    public function liquidar_documento(ComprasDocEncabezado $documento)
    {
        if ($documento->estado == 'Anulado') {
            throw new \InvalidArgumentException('No se pueden liquidar retenciones de una compra anulada.');
        }
        if (!(int)$documento->reteica_retencion_id) {
            return 0;
        }
        $retencion = $this->validar_seleccion($documento->reteica_retencion_id, $documento->core_tipo_transaccion_id);
        // Conservar la base editada; en modo automático usar el subtotal sin IVA.
        $base = $documento->reteica_base_manual ? $documento->reteica_base
            : (float)$documento->lineas_registros()->where('estado', 'Activo')->sum('base_impuesto');
        $documento->reteica_base = round($base, 2);
        $documento->reteica_tasa = (float)$retencion->tasa_retencion;
        $documento->reteica_valor = $this->calcular($base, $retencion->tasa_retencion);
        $documento->save();
        return (float)$documento->reteica_valor;
    }

    public function contabilizar(ComprasDocEncabezado $documento)
    {
        return DB::transaction(function () use ($documento) {
            $documento = ComprasDocEncabezado::where('id', $documento->id)->lockForUpdate()->firstOrFail();
            if ($documento->estado == 'Anulado') {
                throw new \InvalidArgumentException('No se pueden contabilizar retenciones de una compra anulada.');
            }
            if (!(int)$documento->reteica_retencion_id || (float)$documento->reteica_valor <= 0) {
                return;
            }
            if (ComprasRetencionLiquidacion::where('compras_doc_encabezado_id', $documento->id)
                ->where('codigo_concepto', 'reteica')->where('estado', 'Activo')->exists()) {
                return;
            }
            $retencion = $this->validar_seleccion($documento->reteica_retencion_id, $documento->core_tipo_transaccion_id);
            $valor = (float)$documento->reteica_valor;
            $datos = $documento->toArray();
            $datos = array_merge($datos, [
                'tipo' => 'practicada', 'contab_retencion_id' => $retencion->id,
                'compras_doc_registro_id' => 0, 'valor_base_retencion' => $documento->reteica_base,
                'tasa_retencion' => $documento->reteica_tasa, 'valor' => $valor,
                'numero_certificado' => '', 'fecha_certificado' => '', 'fecha_recepcion_certificado' => '',
                'numero_doc_identidad_agente_retencion' => '', 'razon_social_agente_retencion' => '',
                'detalle' => 'ReteICA compra #' . $documento->id, 'estado' => 'Activo',
                'creado_por' => Auth::user()->email, 'modificado_por' => ''
            ]);
            $registro = RegistroRetencion::create($datos);
            ComprasRetencionLiquidacion::create([
                'compras_doc_encabezado_id' => $documento->id, 'compras_doc_registro_id' => 0,
                'contab_registro_retencion_id' => $registro->id, 'contab_retencion_id' => $retencion->id,
                'anio' => date('Y', strtotime($documento->fecha)), 'codigo_concepto' => 'reteica',
                'concepto' => $retencion->descripcion, 'tipo_operacion' => 'compras',
                'base_retencion' => $documento->reteica_base, 'tasa_retencion' => $documento->reteica_tasa,
                'valor_retencion' => $valor, 'aplicada' => 1, 'origen' => 'manual',
                'detalle' => $datos['detalle'], 'creado_por' => $datos['creado_por'], 'estado' => 'Activo'
            ]);
            $datos['core_tercero_id'] = (int)config('contabilidad.tercero_reteica_id');
            $datos['tipo_transaccion'] = '';
            (new ContabMovimiento())->contabilizar_linea_registro($datos, $retencion->cta_compras_id, $datos['detalle'], 0, $valor);
            $datos['valor_documento'] = $valor;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $valor;
            $datos['estado'] = 'Pendiente';
            CxpMovimiento::create($datos);
        });
    }
}
