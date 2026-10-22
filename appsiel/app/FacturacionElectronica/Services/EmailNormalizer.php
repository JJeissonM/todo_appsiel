<?php

namespace App\FacturacionElectronica\Services;

class EmailNormalizer
{
    public static function normalize($email)
    {
        $email = (string)$email;
        // Incluye espacios no separables e invisibles introducidos al copiar/pegar.
        $normalized = preg_replace('/[\s\p{Z}\x{200B}\x{FEFF}]+/u', '', $email);
        return $normalized === null ? trim($email) : $normalized;
    }
}
