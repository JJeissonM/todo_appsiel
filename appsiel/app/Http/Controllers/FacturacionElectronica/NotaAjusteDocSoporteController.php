<?php

namespace App\Http\Controllers\FacturacionElectronica;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\Services\NotaAjusteDocSoporteService;
use App\CxP\CxpMovimiento;
use App\FacturacionElectronica\NotaAjusteDocSoporte;
use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Inventarios\InvProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

// Nota de ajuste al documento soporte (transacción 62). La lógica de negocio vive en NotaAjusteDocSoporteService.
class NotaAjusteDocSoporteController extends TransaccionController
{
    public function create()
    {
        $this->set_variables_globales();

        $ds = ComprasDocEncabezado::where('core_empresa_id', Auth::user()->empresa_id)->find(Input::get('doc_soporte_id'));
        if ($ds === null) {
            return redirect()->back()->with('mensaje_error', 'Documento soporte no encontrado.');
        }

        $error = $this->error_doc_soporte($ds);
        if ($error != '') {
            return redirect($this->url_doc_soporte($ds->id))->with('mensaje_error', $error);
        }

        $lineas = $this->lineas_disponibles($ds);
        $hay_notas = NotaAjusteDocSoporte::notas_activas_de($ds->id)->count() > 0;
        $concepto = (int)Input::get('concepto');
        if ($concepto < 1 || $concepto > 5 || ($concepto == 2 && $hay_notas)) {
            $concepto = 0;
        }

        $saldo_cxp = null;
        if ($ds->forma_pago == 'credito') {
            $cxp = CxpMovimiento::where('core_empresa_id', $ds->core_empresa_id)
                ->where('core_tipo_transaccion_id', $ds->core_tipo_transaccion_id)
                ->where('core_tipo_doc_app_id', $ds->core_tipo_doc_app_id)
                ->where('consecutivo', $ds->consecutivo)
                ->where('core_tercero_id', $ds->core_tercero_id)
                ->first();
            $saldo_cxp = $cxp ? $cxp->saldo_pendiente : 0;
        }

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');
        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, count($lista_campos), 'create');

        $form_create = ['url' => $this->modelo->url_form_create, 'campos' => $lista_campos];
        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Crear: ' . $this->transaccion->descripcion);
        $conceptos = NotaAjusteDocSoporte::CONCEPTOS;
        $id_transaccion = $this->transaccion->id;
        $url_volver = $this->url_doc_soporte($ds->id);
        $proveedor = ComprasDocEncabezado::get_registro_impresion($ds->id);

        return view('compras.notas_ajuste_doc_soporte.create', compact('form_create', 'miga_pan', 'ds', 'proveedor', 'lineas', 'conceptos', 'concepto', 'hay_notas', 'saldo_cxp', 'id_transaccion', 'url_volver'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'concepto' => 'required|integer|between:1,5',
            'fecha' => 'required|date_format:Y-m-d',
            'core_tipo_doc_app_id' => 'required|integer',
            'compras_doc_relacionado_id' => 'required|integer',
        ]);

        try {
            $ds = ComprasDocEncabezado::where('core_empresa_id', Auth::user()->empresa_id)->findOrFail($request->compras_doc_relacionado_id);
            $solicitud = self::armar_solicitud($request->concepto, $request->input('lineas', []));
            $encabezado = [
                'core_tipo_doc_app_id' => $request->core_tipo_doc_app_id,
                'fecha' => $request->fecha,
                'descripcion' => (string)$request->descripcion,
                'url_id_modelo' => $request->url_id_modelo,
            ];

            $nota = DB::transaction(function () use ($ds, $request, $encabezado, $solicitud) {
                return (new NotaAjusteDocSoporteService)->crear($ds, (int)$request->concepto, $encabezado, $solicitud);
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('mensaje_error', e($e->getMessage()));
        }

        return redirect('compras_notas_ajuste_doc_soporte/' . $nota->id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . NotaAjusteDocSoporte::TRANSACCION_ID);
    }

    public function show($id)
    {
        $this->set_variables_globales();

        return redirect('compras/' . $id . '?id=' . $this->app->id . '&id_modelo=' . $this->modelo->id . '&id_transaccion=' . $this->transaccion->id . '&vista=compras.notas_ajuste_doc_soporte.show');
    }

    public function anular($id)
    {
        $this->set_variables_globales();

        $nota = NotaAjusteDocSoporte::where('core_empresa_id', Auth::user()->empresa_id)
            ->where('core_tipo_transaccion_id', NotaAjusteDocSoporte::TRANSACCION_ID)->findOrFail($id);

        $url = 'compras/' . $id . '?id=' . $this->app->id . '&id_modelo=' . $this->modelo->id . '&id_transaccion=' . $this->transaccion->id . '&vista=compras.notas_ajuste_doc_soporte.show';

        try {
            DB::transaction(function () use ($nota) {
                (new NotaAjusteDocSoporteService)->anular($nota);
            });
        } catch (\InvalidArgumentException $e) {
            return redirect($url)->with('mensaje_error', e($e->getMessage()));
        }

        return redirect($url)->with('flash_message', 'Nota anulada correctamente.');
    }

    /**
     * Cantidades o valores del formulario => [id línea DS => número > 0]. El concepto 2 no usa líneas.
     * Los vacíos o en cero se ignoran; un valor no numérico o negativo es error del usuario.
     */
    public static function armar_solicitud($concepto, $lineas)
    {
        $solicitud = [];
        if ((int)$concepto == 2 || !is_array($lineas)) {
            return $solicitud;
        }
        foreach ($lineas as $id => $valor) {
            $valor = trim((string)$valor);
            if ($valor === '') {
                continue;
            }
            if (!is_numeric($valor) || (float)$valor < 0) {
                throw new \InvalidArgumentException('Valor inválido en una línea: use solo números positivos.');
            }
            if ((float)$valor > 0) {
                $solicitud[(int)$id] = (float)$valor;
            }
        }
        return $solicitud;
    }

    protected function error_doc_soporte(ComprasDocEncabezado $ds)
    {
        if ((int)$ds->core_tipo_transaccion_id != NotaAjusteDocSoporte::TRANSACCION_DOC_SOPORTE_ID) {
            return 'La nota de ajuste solo aplica a documentos soporte.';
        }
        if ($ds->estado == 'Anulado') {
            return 'El documento soporte está anulado.';
        }
        if (!$ds->enviado_electronicamente()) {
            return 'El documento soporte no ha sido aceptado por la DIAN: anúlelo en lugar de hacer una nota de ajuste.';
        }
        return '';
    }

    protected function url_doc_soporte($id)
    {
        return 'compras/' . $id . '?id=' . Input::get('id') . '&id_modelo=233&id_transaccion=' . NotaAjusteDocSoporte::TRANSACCION_DOC_SOPORTE_ID;
    }

    // Por cada línea activa del DS: cantidad, base, IVA y lo disponible tras restar las notas activas.
    protected function lineas_disponibles(ComprasDocEncabezado $ds)
    {
        $lineas = $ds->lineas_registros()->where('estado', 'Activo')->get();
        $ajustes = ComprasDocRegistro::where('estado', 'Activo')
            ->whereIn('compras_doc_registro_origen_id', $lineas->pluck('id')->all())->get();

        $resultado = [];
        foreach ($lineas as $linea) {
            $propias = $ajustes->filter(function ($a) use ($linea) { return $a->compras_doc_registro_origen_id == $linea->id; });
            $cantidad = abs((float)$linea->cantidad);
            $base = abs((float)$linea->base_impuesto);
            $producto = InvProducto::find($linea->inv_producto_id);
            $resultado[] = [
                'id' => $linea->id,
                'producto' => $producto ? $producto->descripcion : '',
                'cantidad' => $cantidad,
                'base' => $base,
                'iva' => abs((float)$linea->valor_impuesto),
                'tasa_impuesto' => (float)$linea->tasa_impuesto,
                'cantidad_disponible' => round($cantidad - $propias->sum(function ($a) { return abs((float)$a->cantidad); }), 4),
                'base_disponible' => round($base - $propias->sum(function ($a) { return abs((float)$a->base_impuesto); }), 2),
            ];
        }
        return $resultado;
    }
}
