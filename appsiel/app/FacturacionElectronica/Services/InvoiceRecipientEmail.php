<?php

namespace App\FacturacionElectronica\Services;

class InvoiceRecipientEmail
{
    public static function resolve($document, $fallbackEmail = null)
    {
        if (isset($document->contacto_cliente_id) && (int)$document->contacto_cliente_id > 0) {
            $contact = $document->contacto_cliente;
            if ($contact && (int)$contact->cliente_id === (int)$document->cliente_id && $contact->tercero) {
                $email = EmailNormalizer::normalize($contact->tercero->email);
                if ($email !== '') {
                    return $email;
                }
            }
        }

        return EmailNormalizer::normalize($fallbackEmail === null
            ? $document->cliente_facturacion->tercero->email : $fallbackEmail);
    }
}
