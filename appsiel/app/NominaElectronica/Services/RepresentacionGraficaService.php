<?php

namespace App\NominaElectronica\Services;

use App\Core\Empresa;
use App\NominaElectronica\DATAICO\DocumentoSoporte;
use App\NominaElectronica\DATAICO\Services\DocumentoSoporteService;
use App\NominaElectronica\ResultadoEnvioDocumento;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class RepresentacionGraficaService
{
    /**
     * Obtiene la representación gráfica sin depender innecesariamente del proveedor.
     *
     * Prioridad:
     * 1. PDF almacenado en una respuesta de envío anterior (DATAICO u OSEI).
     * 2. Consulta a DATAICO, cuando es el proveedor configurado.
     * 3. Representación local construida con los datos persistidos del documento.
     */
    public function obtenerPdf(DocumentoSoporte $documento)
    {
        $pdfCache = $this->obtenerPdfCache($documento);
        if (!is_null($pdfCache)) {
            return $this->resultadoExitoso($pdfCache, 'cache-local');
        }

        $pdfAlmacenado = $this->obtenerPdfAlmacenado($documento);
        if (!is_null($pdfAlmacenado)) {
            return $this->guardarYResponder($documento, $pdfAlmacenado, 'proveedor-almacenado');
        }

        if (strtoupper(config('nomina.proveedor_tecnologico_default', 'DATAICO')) === 'DATAICO') {
            $respuesta = (new DocumentoSoporteService())->consultar_documento_emitido($documento);
            $pdfRemoto = $this->extraerPdf($respuesta);

            if (!is_null($pdfRemoto)) {
                return $this->guardarYResponder($documento, $pdfRemoto, 'dataico-remoto');
            }
        }

        try {
            return $this->guardarYResponder($documento, $this->generarPdfLocal($documento), 'local');
        } catch (\Throwable $e) {
            Log::error('Nómina electrónica: no fue posible generar la representación gráfica.', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message' => 'No fue posible obtener ni generar la representación gráfica del documento.',
            ];
        }
    }

    protected function obtenerPdfAlmacenado(DocumentoSoporte $documento)
    {
        $resultados = ResultadoEnvioDocumento::select('response_xml')
            ->where('core_empresa_id', $documento->core_empresa_id)
            ->where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        foreach ($resultados as $resultado) {
            $pdf = $this->extraerPdf($resultado->response_xml);
            if (!is_null($pdf)) {
                return $pdf;
            }
        }

        return null;
    }

    protected function obtenerPdfCache(DocumentoSoporte $documento)
    {
        try {
            $ruta = $this->rutaCache($documento);
            if (!Storage::disk('local')->exists($ruta)) {
                return null;
            }

            $contenido = Storage::disk('local')->get($ruta);
            return strpos($contenido, '%PDF-') === 0 ? $contenido : null;
        } catch (\Throwable $e) {
            Log::warning('Nómina electrónica: no fue posible leer el PDF de caché.', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function guardarYResponder(DocumentoSoporte $documento, $pdf, $origen)
    {
        try {
            Storage::disk('local')->put($this->rutaCache($documento), $pdf);
        } catch (\Throwable $e) {
            // El caché es una optimización: su fallo no debe impedir mostrar el PDF.
            Log::warning('Nómina electrónica: no fue posible guardar el PDF en caché.', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $this->resultadoExitoso($pdf, $origen);
    }

    protected function rutaCache(DocumentoSoporte $documento)
    {
        $version = $documento->updated_at ? $documento->updated_at->format('YmdHis') : 'sin-fecha';
        $identificador = implode('-', [
            $documento->core_empresa_id,
            $documento->core_tipo_doc_app_id,
            $documento->id,
            $version,
        ]);

        return 'nomina_electronica/representaciones/' . sha1($identificador) . '.pdf';
    }

    /**
     * Extrae y valida un PDF desde respuestas DATAICO (incluido el sobre de
     * evidencia HTTP) y respuestas JSON de OSEI.
     */
    public function extraerPdf($respuesta)
    {
        $datos = $this->normalizarRespuesta($respuesta);
        if (is_null($datos)) {
            return null;
        }

        return $this->buscarPdf($datos);
    }

    protected function normalizarRespuesta($respuesta)
    {
        if (is_object($respuesta)) {
            $respuesta = json_decode(json_encode($respuesta), true);
        }

        if (is_string($respuesta)) {
            $respuesta = trim($respuesta);
            if ($respuesta === '') {
                return null;
            }

            $decodificada = json_decode($respuesta, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $respuesta = $decodificada;
            } else {
                return $respuesta;
            }
        }

        if (is_array($respuesta) && isset($respuesta['body_json'])) {
            $bodyJson = $this->normalizarRespuesta($respuesta['body_json']);
            if (!is_null($bodyJson)) {
                return $bodyJson;
            }
        }

        if (is_array($respuesta) && isset($respuesta['body_raw'])) {
            $bodyRaw = $this->normalizarRespuesta($respuesta['body_raw']);
            if (!is_null($bodyRaw)) {
                return $bodyRaw;
            }
        }

        return $respuesta;
    }

    protected function buscarPdf($datos)
    {
        if (is_string($datos)) {
            return $this->decodificarPdf($datos);
        }

        if (!is_array($datos)) {
            return null;
        }

        $clavesPdf = [
            'pdf', 'pdfBase64', 'pdf_base64', 'documentPdf', 'document_pdf',
            'documentBase64', 'fileContent', 'base64', 'content',
            'representation', 'graphicRepresentation', 'graphic_representation',
        ];

        foreach ($clavesPdf as $clave) {
            if (array_key_exists($clave, $datos)) {
                $pdf = $this->buscarPdf($datos[$clave]);
                if (!is_null($pdf)) {
                    return $pdf;
                }
            }
        }

        foreach ($datos as $valor) {
            if (is_array($valor) || is_object($valor)) {
                $pdf = $this->buscarPdf($valor);
                if (!is_null($pdf)) {
                    return $pdf;
                }
            }
        }

        return null;
    }

    protected function decodificarPdf($contenido)
    {
        $contenido = trim($contenido);

        if (strpos($contenido, '%PDF-') === 0) {
            return $contenido;
        }

        if (preg_match('/^data:application\/pdf;base64,/i', $contenido)) {
            $contenido = preg_replace('/^data:application\/pdf;base64,/i', '', $contenido);
        }

        $contenido = preg_replace('/\s+/', '', $contenido);
        if ($contenido === '' || strlen($contenido) < 8) {
            return null;
        }

        $binario = base64_decode($contenido, true);
        if ($binario === false || strpos($binario, '%PDF-') !== 0) {
            return null;
        }

        return $binario;
    }

    protected function generarPdfLocal(DocumentoSoporte $documento)
    {
        $empresa = Empresa::find($documento->core_empresa_id);
        $contrato = $documento->empleado;
        $tercero = is_null($contrato) ? null : $contrato->tercero;
        $resultado = $this->ultimoResultado($documento);
        $accruals = $this->decodificarLineas($documento->accruals_json);
        $deductions = $this->decodificarLineas($documento->deductions_json);

        $totalDevengos = $this->sumarValores($accruals, true);
        $totalDeducciones = $this->sumarValores($deductions);

        $html = View::make('nomina.nomina_electronica.pdf_documento_soporte', compact(
            'documento', 'empresa', 'contrato', 'tercero', 'resultado',
            'accruals', 'deductions', 'totalDevengos', 'totalDeducciones'
        ))->render();

        return app('dompdf.wrapper')
            ->loadHTML($html)
            ->setPaper('letter', 'portrait')
            ->output();
    }

    protected function ultimoResultado(DocumentoSoporte $documento)
    {
        return ResultadoEnvioDocumento::select('dian_status', 'cune')
            ->where('core_empresa_id', $documento->core_empresa_id)
            ->where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo)
            ->orderBy('id', 'desc')
            ->first();
    }

    protected function decodificarLineas($json)
    {
        $lineas = json_decode($json, true);
        return is_array($lineas) ? $lineas : [];
    }

    protected function sumarValores(array $lineas, $incluirInteresesCesantias = false)
    {
        return array_reduce($lineas, function ($total, $linea) use ($incluirInteresesCesantias) {
            $valor = isset($linea['amount-ns'])
                ? (float) $linea['amount-ns']
                : (float) (isset($linea['amount']) ? $linea['amount'] : 0);

            if ($incluirInteresesCesantias) {
                $valor += (float) (isset($linea['cesantias-interest']) ? $linea['cesantias-interest'] : 0);
            }

            return $total + $valor;
        }, 0);
    }

    protected function resultadoExitoso($pdf, $origen)
    {
        return [
            'ok' => true,
            'content' => $pdf,
            'source' => $origen,
        ];
    }
}
