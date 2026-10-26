<?php

namespace App\FacturacionElectronica\OSEI;

/** Normaliza una copia del payload, sin modificar los modelos del documento. */
class PayloadNormalizer
{
    public static function text($value)
    {
        $value = self::ascii($value);
        $value = preg_replace('/[^a-zA-Z0-9 ]/', ' ', $value);

        return trim(preg_replace('/ +/', ' ', $value));
    }

    protected static function ascii($value)
    {
        // Mapa explicito: no depende del locale ni de extensiones opcionales.
        $value = strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ç' => 'c', 'Ç' => 'C', 'ö' => 'o', 'ä' => 'a',
            "\xc2\xa0" => ' ',
        ]);
        // Elimina marcas combinantes para admitir tambien Unicode descompuesto.
        $value = preg_replace('/[\p{Mn}\p{Cf}]/u', '', $value);
        $value = preg_replace('/[^\x20-\x7E]/', ' ', $value);

        return trim(preg_replace('/ +/', ' ', $value));
    }

    public static function normalize($value, $field = '')
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::normalize($item, $key);
            }
            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        if (in_array($field, ['legal_name', 'trade_name', 'description', 'notes', 'address_line'], true)) {
            return self::text($value);
        }

        // Correos, URLs, fechas, identificadores y codigos conservan su puntuacion.
        return self::ascii($value);
    }
}
