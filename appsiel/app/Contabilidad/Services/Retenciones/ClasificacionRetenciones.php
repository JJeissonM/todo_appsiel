<?php
namespace App\Contabilidad\Services\Retenciones;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** La configuración anual prevalece. Los auxiliares antiguos usan su categoría y cuenta identificables. */
class ClasificacionRetenciones
{
    private $retenciones = [];
    private $categorias = [];
    private $cuentas = [];
    private $config = [];
    private $tienePlanDeCuentas = false;

    public function __construct($empresaId)
    {
        foreach (DB::table('contab_retenciones')->get() as $r) { $this->retenciones[$r->id] = $r; }
        foreach (DB::table('contab_retenciones_reporte_config')->where('core_empresa_id', $empresaId)->get() as $c) {
            $this->config[$c->anio][$c->contab_retencion_id] = $c;
        }
        if (Schema::hasTable('contab_categorias_retenciones')) {
            foreach (DB::table('contab_categorias_retenciones')->get() as $c) { $this->categorias[$c->id] = $c; }
        }
        if (Schema::hasTable('contab_cuentas')) {
            $this->tienePlanDeCuentas = true;
            foreach (DB::table('contab_cuentas')->where('core_empresa_id', $empresaId)->get() as $c) { $this->cuentas[$c->id] = $c; }
        }
    }

    public function cuenta($id) { return isset($this->cuentas[$id]) ? $this->cuentas[$id] : null; }

    public function esCuentaRetencion($id, $naturaleza)
    {
        $cuenta = $this->cuenta($id);
        if (!$cuenta) { return !$this->tienePlanDeCuentas; }
        if ($this->tipoCuenta($cuenta, $naturaleza)) { return true; }
        $otraNaturaleza = $naturaleza === 'practicada' ? 'sufrida' : 'practicada';
        if ($this->tipoCuenta($cuenta, $otraNaturaleza)) { return false; }
        // Una cuenta de proveedores asociada por error al catálogo no es un pasivo tributario.
        return $this->tipoTexto($cuenta->descripcion) !== null;
    }

    public function cuentasIdentificables($naturaleza)
    {
        $resultado = [];
        foreach ($this->cuentas as $id => $cuenta) {
            if ($this->tipoCuenta($cuenta, $naturaleza)) { $resultado[$id] = $cuenta; }
        }
        return $resultado;
    }

    public function porRetencion($id, $naturaleza, $anio)
    {
        if (isset($this->config[$anio][$id])) {
            return array_merge($this->vacia(), array_intersect_key((array)$this->config[$anio][$id], array_flip([
                'tipo_impuesto', 'concepto', 'codigo_municipio', 'actividad_ica'
            ])), ['clasificacion_origen' => 'configuracion']);
        }
        if (!isset($this->retenciones[$id])) { return $this->vacia(); }
        $retencion = $this->retenciones[$id];
        $campo = $naturaleza === 'practicada' ? 'cta_compras_id' : 'cta_ventas_id';
        $cuenta = isset($retencion->$campo) ? $this->cuenta($retencion->$campo) : null;
        $categoria = isset($retencion->categoria_retenciones_id, $this->categorias[$retencion->categoria_retenciones_id])
            ? $this->categorias[$retencion->categoria_retenciones_id] : null;
        $tipoCategoria = $categoria ? $this->tipoTexto($categoria->descripcion.' '.$categoria->nombre_corto) : null;
        $tipoCuenta = $cuenta ? $this->tipoCuenta($cuenta, $naturaleza) : null;
        // No ocultar un conflicto entre el catálogo y la cuenta asignándolo arbitrariamente.
        if ($tipoCategoria && $tipoCuenta && $tipoCategoria !== $tipoCuenta) { return $this->vacia(); }
        $tipo = $tipoCategoria ?: $tipoCuenta;
        $concepto = $tipo === 'FUENTE'
            ? ($this->conceptoFuente($cuenta ? $cuenta->descripcion : '') ?: $this->conceptoFuente($retencion->descripcion))
            : ($tipo === 'ICA' ? 'ica' : null);
        return array_merge($this->vacia(), ['tipo_impuesto' => $tipo, 'concepto' => $concepto,
            'clasificacion_origen' => $tipo ? 'categoria_cuenta' : null]);
    }

    public function porCuenta($id, $naturaleza, $anio)
    {
        $coincidencias = [];
        $campos = $naturaleza === 'practicada' ? ['cta_compras_id', 'cta_compras_devol_id'] : ['cta_ventas_id', 'cta_ventas_devol_id'];
        foreach ($this->retenciones as $r) {
            foreach ($campos as $campo) {
                if (isset($r->$campo) && (int)$r->$campo === (int)$id) {
                    $c = $this->porRetencion($r->id, $naturaleza, $anio);
                    $clave = json_encode([$c['tipo_impuesto'], $c['concepto'], $c['codigo_municipio'], $c['actividad_ica']]);
                    $coincidencias[$clave] = $c;
                    break;
                }
            }
        }
        if (count($coincidencias) === 1) { return reset($coincidencias); }
        if (count($coincidencias) > 1) {
            $tipos = array_unique(array_map(function ($c) { return $c['tipo_impuesto']; }, $coincidencias));
            $c = $this->vacia();
            if (count($tipos) === 1) { $c['tipo_impuesto'] = reset($tipos); }
            return $c;
        }
        $cuenta = $this->cuenta($id);
        if (!$cuenta) { return $this->vacia(); }
        $c = $this->vacia();
        $c['tipo_impuesto'] = $this->tipoCuenta($cuenta, $naturaleza);
        $c['concepto'] = $c['tipo_impuesto'] === 'FUENTE' ? $this->conceptoFuente($cuenta->descripcion) : ($c['tipo_impuesto'] === 'ICA' ? 'ica' : null);
        $c['clasificacion_origen'] = $c['tipo_impuesto'] ? 'cuenta' : null;
        return $c;
    }

    private function vacia()
    {
        return ['tipo_impuesto' => null, 'concepto' => null, 'codigo_municipio' => null, 'actividad_ica' => null, 'clasificacion_origen' => null];
    }

    private function tipoCuenta($cuenta, $naturaleza)
    {
        $codigo = trim((string)$cuenta->codigo);
        $prefijos = $naturaleza === 'practicada'
            ? ['2365' => 'FUENTE', '2367' => 'IVA', '2368' => 'ICA']
            : ['135515' => 'FUENTE', '135517' => 'IVA', '135518' => 'ICA'];
        foreach ($prefijos as $prefijo => $tipo) {
            if (strpos($codigo, (string)$prefijo) === 0) { return $tipo; }
        }
        return null;
    }

    private function tipoTexto($texto)
    {
        $texto = str_slug($texto);
        if (preg_match('/(^|-)(reteica|ica)(-|$)|industria-y-comercio/', $texto)) { return 'ICA'; }
        if (preg_match('/(^|-)(reteiva|iva)(-|$)|impuesto-sobre-las-ventas/', $texto)) { return 'IVA'; }
        if (preg_match('/rtefte|retefuente|retencion-en-la-fuente|retencion.*renta/', $texto)) { return 'FUENTE'; }
        return null;
    }

    private function conceptoFuente($texto)
    {
        $texto = str_slug($texto);
        if (strpos($texto, 'autorret') !== false || strpos($texto, 'exterior') !== false) { return null; }
        $reglas = [
            'trabajo' => 'salarios|pagos-laborales|rentas-de-trabajo', 'pensiones' => 'pensiones',
            'honorarios' => 'honorarios', 'comisiones' => 'comisiones', 'servicios' => 'servicios?',
            'rendimientos' => 'rendimientos-financieros|intereses', 'arrendamientos' => 'arrendamientos?',
            'regalias' => 'regalias', 'dividendos' => 'dividendos', 'compras' => 'compras?',
            'tarjetas' => 'tarjetas', 'construccion' => 'construccion', 'loterias' => 'loterias|rifas|apuestas'
        ];
        $encontrados = [];
        foreach ($reglas as $concepto => $patron) {
            if (preg_match('/(^|-)('.$patron.')(-|$)/', $texto)) { $encontrados[] = $concepto; }
        }
        return count($encontrados) === 1 ? $encontrados[0] : null;
    }
}
