<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasRetencionLiquidacion;
use App\Compras\Proveedor;
use App\Compras\RetencionFuenteConceptoAnual;
use App\Contabilidad\Retencion;
use App\Inventarios\InvProducto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RetencionFuenteService
{
    public function get_retenciones_activas()
    {
        if (!$this->maneja_retenciones_compras()) {
            return collect();
        }

        return Retencion::where('estado', 'Activo')
            ->select('id', 'descripcion', 'nombre_corto', 'tasa_retencion')
            ->orderBy('descripcion')
            ->get();
    }

    public function calcular_valor_retencion_linea($precio_unitario, $cantidad, $tasa_impuesto, $tasa_retencion)
    {
        $valor_bruto_linea = (float)$precio_unitario * (float)$cantidad;

        $divisor_iva = 1 + ((float)$tasa_impuesto / 100);
        if ($divisor_iva <= 0) {
            $divisor_iva = 1;
        }

        $base_sin_iva = $valor_bruto_linea / $divisor_iva;
        $valor_retencion = $base_sin_iva * ((float)$tasa_retencion / 100);

        return [
            'base_sin_iva' => round($base_sin_iva, 2),
            'valor_retencion' => round($valor_retencion, 2),
        ];
    }

    public function conceptos_anuales($anio = null)
    {
        $anio = $anio ? (int)$anio : (int)date('Y');

        if (!$this->maneja_retenciones_compras($anio)) {
            return collect();
        }

        return RetencionFuenteConceptoAnual::where('anio', $anio)
            ->where('estado', 'Activo')
            ->orderBy('tipo_operacion')
            ->orderBy('concepto')
            ->get();
    }

    public function liquidar_lineas_request(array $lineas, $fecha = null, $proveedorId = 0, $forzar = false)
    {
        if (!$this->maneja_retenciones_compras($fecha)) {
            return $this->limpiar_retenciones_lineas($lineas);
        }

        $proveedor = $proveedorId ? Proveedor::find((int)$proveedorId) : null;

        foreach ($lineas as $linea) {
            if (!is_object($linea)) {
                continue;
            }

            $retencionManual = isset($linea->contab_retencion_id) && (int)$linea->contab_retencion_id > 0;
            if ($retencionManual && !$forzar) {
                continue;
            }

            $liquidacion = $this->liquidar_linea($linea, $fecha, null, $proveedor);
            if (!$liquidacion['aplica']) {
                if ($forzar) {
                    $linea->contab_retencion_id = 0;
                    $linea->tasa_retencion = 0;
                    $linea->valor_retencion = 0;
                }
                continue;
            }

            $linea->contab_retencion_id = (int)$liquidacion['contab_retencion_id'];
            $linea->tasa_retencion = (float)$liquidacion['tasa_retencion'];
            $linea->valor_retencion = (float)$liquidacion['valor_retencion'];
            $linea->retencion_fuente_concepto_anual_id = (int)$liquidacion['concepto_id'];
            $linea->retencion_fuente_codigo = $liquidacion['codigo_concepto'];
        }

        return $lineas;
    }

    public function liquidar_linea($linea, $fecha = null, $codigoConcepto = null, Proveedor $proveedor = null)
    {
        $anio = $this->get_anio($fecha);

        if (!$this->maneja_retenciones_compras($anio)) {
            return $this->respuesta_no_aplica($anio);
        }

        $producto = $this->get_producto_linea($linea);
        $tipoItem = $producto && $producto->tipo == 'servicio' ? 'servicio' : 'producto';
        $codigoConcepto = $this->get_codigo_concepto_linea($linea, $codigoConcepto);
        $conceptoId = $this->get_concepto_id_linea($linea, $proveedor);
        $tipoDeclarante = $this->get_tipo_declarante_proveedor($proveedor);
        $concepto = $this->get_concepto_para_linea($anio, $tipoItem, $tipoDeclarante, $codigoConcepto, $conceptoId);

        $precioUnitario = isset($linea->precio_unitario) ? (float)$linea->precio_unitario : (isset($linea->costo_unitario) ? (float)$linea->costo_unitario : 0);
        $cantidad = isset($linea->cantidad) ? (float)$linea->cantidad : 0;
        $tasaImpuesto = isset($linea->tasa_impuesto) ? (float)$linea->tasa_impuesto : $this->get_tasa_impuesto_producto($producto);

        if (is_null($concepto) || $precioUnitario <= 0 || $cantidad <= 0) {
            return $this->respuesta_no_aplica($anio, $concepto);
        }

        $calculo = $this->calcular_valor_retencion_linea($precioUnitario, $cantidad, $tasaImpuesto, $concepto->tasa_retencion);
        $base = (float)$calculo['base_sin_iva'];
        $cuantiaMinima = (float)$concepto->cuantia_minima_pesos;
        $valor = $base >= $cuantiaMinima ? (float)$calculo['valor_retencion'] : 0;

        return [
            'aplica' => $valor > 0 && (int)$concepto->contab_retencion_id > 0,
            'anio' => $anio,
            'concepto_id' => (int)$concepto->id,
            'codigo_concepto' => $concepto->codigo,
            'concepto' => $concepto->concepto,
            'tipo_operacion' => $concepto->tipo_operacion,
            'tipo_declarante' => $concepto->tipo_declarante,
            'contab_retencion_id' => (int)$concepto->contab_retencion_id,
            'base_retencion' => round($base, 2),
            'tasa_retencion' => (float)$concepto->tasa_retencion,
            'cuantia_minima_uvt' => (float)$concepto->cuantia_minima_uvt,
            'cuantia_minima_pesos' => round($cuantiaMinima, 2),
            'valor_retencion' => round($valor, 2),
            'detalle' => $valor > 0 ? 'Retención automática aplicada.' : 'No supera cuantía mínima.',
        ];
    }

    public function liquidar_documento(ComprasDocEncabezado $docEncabezado, $almacenar = false)
    {
        if (!$this->maneja_retenciones_compras($docEncabezado->fecha)) {
            return [
                'documento_id' => (int)$docEncabezado->id,
                'total_retenciones' => 0,
                'lineas' => [],
            ];
        }

        $lineas = ComprasDocRegistro::where('compras_doc_encabezado_id', $docEncabezado->id)->get();
        $resultado = [];
        $total = 0;

        foreach ($lineas as $linea) {
            $liquidacion = $this->liquidar_linea($linea, $docEncabezado->fecha, null, $docEncabezado->proveedor);
            $liquidacion['compras_doc_registro_id'] = (int)$linea->id;
            $resultado[] = $liquidacion;
            $total += (float)$liquidacion['valor_retencion'];

            if ($almacenar && $liquidacion['aplica'] && (int)$linea->contab_retencion_id <= 0) {
                $linea->contab_retencion_id = (int)$liquidacion['contab_retencion_id'];
                $linea->tasa_retencion = (float)$liquidacion['tasa_retencion'];
                $linea->valor_retencion = (float)$liquidacion['valor_retencion'];
                $linea->save();
            }
        }

        return [
            'documento_id' => (int)$docEncabezado->id,
            'total_retenciones' => round($total, 2),
            'lineas' => $resultado,
        ];
    }

    public function almacenar_liquidacion_linea(ComprasDocEncabezado $docEncabezado, ComprasDocRegistro $linea, Retencion $retencion, array $datosRetencion, $contabRegistroRetencionId = 0, $origen = 'automatico')
    {
        if (!$this->maneja_retenciones_compras($docEncabezado->fecha) || !Schema::hasTable('compras_retenciones_liquidaciones')) {
            return null;
        }

        $liquidacion = $this->liquidar_linea($linea, $docEncabezado->fecha, null, $docEncabezado->proveedor);

        if (isset($datosRetencion['base_sin_iva'])) {
            $liquidacion['base_retencion'] = (float)$datosRetencion['base_sin_iva'];
        }

        if (isset($datosRetencion['valor_retencion'])) {
            $liquidacion['valor_retencion'] = (float)$datosRetencion['valor_retencion'];
        }

        $datos = [
            'compras_doc_encabezado_id' => (int)$docEncabezado->id,
            'compras_doc_registro_id' => (int)$linea->id,
            'contab_registro_retencion_id' => (int)$contabRegistroRetencionId,
            'retencion_fuente_concepto_anual_id' => (int)$liquidacion['concepto_id'],
            'contab_retencion_id' => (int)$retencion->id,
            'anio' => (int)$liquidacion['anio'],
            'codigo_concepto' => $liquidacion['codigo_concepto'],
            'concepto' => $liquidacion['concepto'],
            'tipo_operacion' => $liquidacion['tipo_operacion'],
            'tipo_declarante' => $liquidacion['tipo_declarante'],
            'base_retencion' => (float)$liquidacion['base_retencion'],
            'tasa_retencion' => (float)$retencion->tasa_retencion,
            'cuantia_minima_uvt' => (float)$liquidacion['cuantia_minima_uvt'],
            'cuantia_minima_pesos' => (float)$liquidacion['cuantia_minima_pesos'],
            'valor_retencion' => (float)$liquidacion['valor_retencion'],
            'aplicada' => 1,
            'origen' => $origen,
            'detalle' => 'Factura de compras, línea #' . $linea->id,
            'creado_por' => Auth::check() ? Auth::user()->email : '',
            'modificado_por' => '',
            'estado' => 'Activo',
        ];

        $existente = ComprasRetencionLiquidacion::where('compras_doc_registro_id', $linea->id)
            ->where('contab_retencion_id', $retencion->id)
            ->where('estado', 'Activo')
            ->first();

        if ($existente) {
            $existente->update($datos);
            return $existente;
        }

        return ComprasRetencionLiquidacion::create($datos);
    }

    protected function get_concepto_para_linea($anio, $tipoItem, $tipoDeclarante, $codigoConcepto = null, $conceptoId = 0)
    {
        if (!$this->maneja_retenciones_compras($anio)) {
            return null;
        }

        if ($conceptoId > 0) {
            $concepto = RetencionFuenteConceptoAnual::where('anio', $anio)
                ->where('estado', 'Activo')
                ->where('id', $conceptoId)
                ->first();

            if ($concepto) {
                return $concepto;
            }
        }

        $query = RetencionFuenteConceptoAnual::where('anio', $anio)->where('estado', 'Activo');

        if ($codigoConcepto) {
            $concepto = $query->where('codigo', $codigoConcepto)->first();
            if ($concepto) {
                return $concepto;
            }
        }

        $codigo = $tipoItem == 'servicio' ? 'servicios_general_' . $tipoDeclarante : 'compras_general_' . $tipoDeclarante;

        return RetencionFuenteConceptoAnual::where('anio', $anio)
            ->where('estado', 'Activo')
            ->where('codigo', $codigo)
            ->first();
    }

    protected function get_codigo_concepto_linea($linea, $codigoConcepto = null)
    {
        if ($codigoConcepto) {
            return $codigoConcepto;
        }

        return isset($linea->retencion_fuente_codigo) ? $linea->retencion_fuente_codigo : null;
    }

    protected function get_concepto_id_linea($linea, Proveedor $proveedor = null)
    {
        if (isset($linea->retencion_fuente_concepto_anual_id) && (int)$linea->retencion_fuente_concepto_anual_id > 0) {
            return (int)$linea->retencion_fuente_concepto_anual_id;
        }

        if ($proveedor && (int)$proveedor->retencion_fuente_concepto_default_id > 0) {
            return (int)$proveedor->retencion_fuente_concepto_default_id;
        }

        return 0;
    }

    protected function get_tipo_declarante_proveedor(Proveedor $proveedor = null)
    {
        if (!$proveedor || empty($proveedor->declarante_renta)) {
            return 'declarante';
        }

        return $proveedor->declarante_renta == 'no_declarante' ? 'no_declarante' : 'declarante';
    }

    protected function get_producto_linea($linea)
    {
        $productoId = isset($linea->inv_producto_id) ? (int)$linea->inv_producto_id : 0;
        return $productoId > 0 ? InvProducto::find($productoId) : null;
    }

    protected function get_tasa_impuesto_producto($producto)
    {
        return $producto && $producto->impuesto ? (float)$producto->impuesto->tasa_impuesto : 0;
    }

    protected function get_anio($fecha)
    {
        if ($fecha) {
            return (int)date('Y', strtotime($fecha));
        }

        return (int)date('Y');
    }

    public function maneja_retenciones_compras($fecha = null)
    {
        $config = config('compras.maneja_retenciones_fuente', '0');

        if (is_bool($config)) {
            $habilitado = $config;
        } else {
            $valor = strtolower(trim((string)$config));
            if (in_array($valor, ['0', 'no', 'false', 'inactivo', 'deshabilitado'])) {
                return false;
            }

            $habilitado = in_array($valor, ['1', 'si', 'sí', 'true', 'activo', 'habilitado']);
        }

        if (!$this->esquema_retenciones_disponible()) {
            return false;
        }

        if ($habilitado) {
            return true;
        }

        $anio = is_numeric($fecha) ? (int)$fecha : $this->get_anio($fecha);

        return RetencionFuenteConceptoAnual::where('anio', $anio)
            ->where('estado', 'Activo')
            ->exists();
    }

    public function campos_retencion_linea_disponibles()
    {
        return Schema::hasColumn('compras_doc_registros', 'contab_retencion_id')
            && Schema::hasColumn('compras_doc_registros', 'tasa_retencion')
            && Schema::hasColumn('compras_doc_registros', 'valor_retencion')
            && Schema::hasColumn('compras_doc_registros', 'retencion_fuente_concepto_anual_id')
            && Schema::hasColumn('compras_doc_registros', 'retencion_fuente_codigo');
    }

    protected function esquema_retenciones_disponible()
    {
        return $this->existe_tabla_conceptos_anuales()
            && Schema::hasTable('contab_retenciones')
            && $this->campos_retencion_linea_disponibles();
    }

    protected function existe_tabla_conceptos_anuales()
    {
        return Schema::hasTable('compras_retencion_fuente_conceptos_anuales');
    }

    protected function limpiar_retenciones_lineas(array $lineas)
    {
        foreach ($lineas as $linea) {
            if (!is_object($linea)) {
                continue;
            }

            $linea->contab_retencion_id = 0;
            $linea->tasa_retencion = 0;
            $linea->valor_retencion = 0;
            $linea->retencion_fuente_concepto_anual_id = 0;
            $linea->retencion_fuente_codigo = '';
        }

        return $lineas;
    }

    protected function respuesta_no_aplica($anio, $concepto = null)
    {
        return [
            'aplica' => false,
            'anio' => $anio,
            'concepto_id' => $concepto ? (int)$concepto->id : 0,
            'codigo_concepto' => $concepto ? $concepto->codigo : '',
            'concepto' => $concepto ? $concepto->concepto : '',
            'tipo_operacion' => $concepto ? $concepto->tipo_operacion : '',
            'tipo_declarante' => $concepto ? $concepto->tipo_declarante : '',
            'contab_retencion_id' => $concepto ? (int)$concepto->contab_retencion_id : 0,
            'base_retencion' => 0,
            'tasa_retencion' => $concepto ? (float)$concepto->tasa_retencion : 0,
            'cuantia_minima_uvt' => $concepto ? (float)$concepto->cuantia_minima_uvt : 0,
            'cuantia_minima_pesos' => $concepto ? (float)$concepto->cuantia_minima_pesos : 0,
            'valor_retencion' => 0,
            'detalle' => $concepto ? 'No aplica retención.' : 'No hay concepto activo para el año.',
        ];
    }
}
