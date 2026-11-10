<?php

namespace App\FacturacionElectronica\Services;

class DocumentNotesText
{
    public static function normalize($html)
    {
        $text = html_entity_decode((string)$html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('~<(script|style)\b[^>]*>.*?</\1\s*>~is', '', $text);
        $text = preg_replace('~<br\s*/?>|</(?:p|div|li|tr|h[1-6])\s*>~i', "\n", $text);
        $text = strip_tags($text);
        $text = str_replace(["\r\n", "\r", "\xc2\xa0"], ["\n", "\n", ' '], $text);
        $text = preg_replace('/[^\S\n]+/u', ' ', $text);
        $text = preg_replace('/ *\n */', "\n", $text);
        return trim(preg_replace('/\n{3,}/', "\n\n", $text));
    }
}
