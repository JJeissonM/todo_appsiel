<?php

namespace App\Contabilidad\Exports;

/** XLSX con XML en disco: no conserva las celdas del detalle en memoria. */
class AuxiliarXlsxWriter
{
    private $directory;
    private $sheets = [];
    private $links = [];
    const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function sheet($name, array $headers)
    {
        $index = count($this->sheets) + 1;
        $xml = new \XMLWriter();
        if (!$xml->openUri($this->directory.'/sheet'.$index.'.xml')) {
            throw new \RuntimeException('No se pudo crear el archivo temporal del reporte.');
        }
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElementNS(null, 'worksheet', self::NS);
        $xml->startElement('sheetViews'); $xml->startElement('sheetView');
        $xml->writeAttribute('workbookViewId', '0');
        $xml->startElement('pane');
        foreach (['ySplit'=>'1', 'topLeftCell'=>'A2', 'activePane'=>'bottomLeft', 'state'=>'frozen'] as $k=>$v) {
            $xml->writeAttribute($k, $v);
        }
        $xml->endElement(); $xml->endElement(); $xml->endElement();
        $xml->startElement('sheetData');
        $this->sheets[$index] = ['xml'=>$xml, 'name'=>$name, 'rows'=>0, 'columns'=>count($headers)];
        $this->row($index, $headers);
        return $index;
    }

    public static function column($number)
    {
        $result = '';
        while ($number > 0) {
            $number--;
            $result = chr(65 + $number % 26).$result;
            $number = (int) floor($number / 26);
        }
        return $result;
    }

    public function row($sheet, array $values, array $links = [])
    {
        $s =& $this->sheets[$sheet];
        $row = ++$s['rows'];
        if ($row > 1048576) {
            throw new \RuntimeException('El detalle supera el límite de filas de Excel. Filtre por cuenta o tercero.');
        }
        $xml = $s['xml'];
        $xml->startElement('row'); $xml->writeAttribute('r', $row);
        foreach ($values as $i=>$value) {
            $ref = self::column($i + 1).$row;
            $xml->startElement('c'); $xml->writeAttribute('r', $ref);
            if (is_int($value) || is_float($value)) {
                $xml->writeAttribute('s', '1');
                $xml->writeElement('v', sprintf('%.17g', $value));
            } else {
                // Texto explícito: conserva identificaciones y evita fórmulas inyectadas.
                $xml->writeAttribute('t', 'inlineStr');
                $xml->startElement('is'); $xml->startElement('t');
                $xml->writeAttribute('xml:space', 'preserve');
                $xml->text(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', mb_substr((string)$value, 0, 32767)));
                $xml->endElement(); $xml->endElement();
            }
            $xml->endElement();
            if (isset($links[$i])) {
                $this->links[$sheet][] = [$ref, $links[$i]];
            }
        }
        $xml->endElement();
        if ($row % 1000 === 0) { $xml->flush(); }
        return $row;
    }

    public function save($path)
    {
        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('No se pudo crear el Excel.');
        }
        $types = '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $book = '<workbook xmlns="'.self::NS.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        $rels = '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($this->sheets as $id=>$sheet) {
            $xml = $sheet['xml']; $xml->endElement();
            $xml->startElement('autoFilter');
            $xml->writeAttribute('ref', 'A1:'.self::column($sheet['columns']).$sheet['rows']);
            $xml->endElement();
            if (!empty($this->links[$id])) {
                $xml->startElement('hyperlinks');
                foreach ($this->links[$id] as $link) {
                    $xml->startElement('hyperlink'); $xml->writeAttribute('ref', $link[0]);
                    $xml->writeAttribute('location', $link[1]); $xml->endElement();
                }
                $xml->endElement();
            }
            $xml->endElement(); $xml->endDocument(); $xml->flush();
            $zip->addFile($this->directory.'/sheet'.$id.'.xml', 'xl/worksheets/sheet'.$id.'.xml');
            $types .= '<Override PartName="/xl/worksheets/sheet'.$id.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $book .= '<sheet name="'.htmlspecialchars($sheet['name'], ENT_QUOTES, 'UTF-8').'" sheetId="'.$id.'" r:id="rId'.$id.'"/>';
            $rels .= '<Relationship Id="rId'.$id.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$id.'.xml"/>';
        }
        $rels .= '<Relationship Id="styles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
        $zip->addFromString('[Content_Types].xml', $types.'</Types>');
        $zip->addFromString('_rels/.rels', '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', $book.'</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', $rels);
        $zip->addFromString('xl/styles.xml', '<styleSheet xmlns="'.self::NS.'"><fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts><fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="4" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs></styleSheet>');
        if (!$zip->close()) { throw new \RuntimeException('No se pudo finalizar el Excel.'); }
        foreach ($this->sheets as $id=>$sheet) { unlink($this->directory.'/sheet'.$id.'.xml'); }
        $this->sheets = []; $this->links = [];
    }
}
