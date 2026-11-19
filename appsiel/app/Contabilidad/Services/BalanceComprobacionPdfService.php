<?php

namespace App\Contabilidad\Services;

use Illuminate\Support\Facades\Auth;

class BalanceComprobacionPdfService
{
    public static function cacheKey($reporteId)
    {
        return 'pdf_balance_comprobacion_'.Auth::user()->empresa_id.'_'.Auth::id().'_'.$reporteId;
    }

    public function generar($contenido, $papel, $orientacion)
    {
        $documento = new \DOMDocument();
        $errores = libxml_use_internal_errors(true);
        try {
            $documento->loadHTML('<?xml encoding="UTF-8">'.$contenido);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($errores);
        }
        $xpath = new \DOMXPath($documento);
        $tabla = $xpath->query('//table[@id="myTable"]')->item(0);
        $cuerpo = $xpath->query('//table[@id="myTable"]/tbody')->item(0);
        if (!$tabla || !$cuerpo) {
            throw new \RuntimeException('El balance no contiene una tabla para exportar. Genere nuevamente el reporte.');
        }
        $encabezados = $xpath->query('./thead/tr/th', $tabla);
        $columnasTexto = $encabezados->length - 4;
        $anchoTexto = $columnasTexto === 2 ? 24 : 40;
        $anchoNumero = $columnasTexto === 2 ? 13 : 15;
        foreach ($encabezados as $indice => $celda) {
            $celda->setAttribute('style', 'width:'.($indice < $columnasTexto ? $anchoTexto : $anchoNumero).'%;');
        }
        $filas = [];
        foreach ($xpath->query('./tr', $cuerpo) as $fila) {
            $celda = $xpath->query('./td[1]', $fila)->item(0);
            if ($celda && $fila->hasAttribute('data-nivel')) {
                $celda->setAttribute('style', 'padding-left:'.min(64, (int)$fila->getAttribute('data-nivel') * 8).'px;');
            }
            $filas[] = $documento->saveHTML($fila);
        }
        while ($cuerpo->firstChild) {
            $cuerpo->removeChild($cuerpo->firstChild);
        }
        $cuerpo->appendChild($documento->createTextNode('__FILAS_BALANCE__'));
        $encabezado = '';
        foreach ($xpath->query('//h3 | //p') as $nodo) {
            $encabezado .= $documento->saveHTML($nodo);
        }
        $base = $encabezado.$documento->saveHTML($tabla);
        $estilos = '<style>table{font-size:7.5pt;table-layout:fixed;}td,th{padding:2px;}td.text-right{white-space:nowrap;}thead{display:table-header-group;}tr{page-break-inside:avoid;}</style>';
        $union = new \setasign\Fpdi\Fpdi();
        $temporales = [];
        try {
            // Cada instancia de Dompdf solo procesa un bloque y libera sus objetos.
            foreach (array_chunk($filas, 180) as $bloque) {
                $html = $estilos.str_replace('__FILAS_BALANCE__', implode('', $bloque), $base);
                $pdf = app('dompdf.wrapper')->loadHTML(view('core.pdf_documento', ['contenido'=>$html]))->setPaper($papel, $orientacion);
                $archivo = tempnam(storage_path('app'), 'balance_pdf_');
                if ($archivo === false) {
                    throw new \RuntimeException('No se pudo crear el archivo temporal del PDF.');
                }
                $temporales[] = $archivo;
                $bytes = $pdf->output();
                if (file_put_contents($archivo, $bytes) !== strlen($bytes)) {
                    throw new \RuntimeException('No se pudo guardar el bloque del PDF.');
                }
                unset($pdf, $bytes);
                gc_collect_cycles();
                $paginas = $union->setSourceFile($archivo);
                for ($pagina = 1; $pagina <= $paginas; $pagina++) {
                    $plantilla = $union->importPage($pagina);
                    $tamano = $union->getTemplateSize($plantilla);
                    $union->AddPage($tamano['orientation'], [$tamano['width'], $tamano['height']]);
                    $union->useTemplate($plantilla);
                }
            }
            return $union->Output('S');
        } finally {
            foreach ($temporales as $archivo) {
                if (is_file($archivo)) {
                    unlink($archivo);
                }
            }
        }
    }
}
