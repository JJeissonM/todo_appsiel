<?php

namespace App\Support;

class ReportTimeFormatter
{
    /**
     * Formatea una hora para reportes sin depender de la configuracion regional
     * del servidor.
     */
    public static function time($value, $emptyValue = '')
    {
        $timestamp = self::timestamp($value);

        if ($timestamp === false) {
            return $emptyValue;
        }

        return date('h:i:s', $timestamp) . ' ' . self::period($timestamp);
    }

    /**
     * Conserva la fecha y presenta su hora con el mismo formato de los filtros.
     */
    public static function dateTime($value, $emptyValue = '')
    {
        $timestamp = self::timestamp($value);

        if ($timestamp === false) {
            return $emptyValue;
        }

        return date('Y-m-d h:i:s', $timestamp) . ' ' . self::period($timestamp);
    }

    private static function timestamp($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        $value = trim((string)$value);

        if ($value === '' || strpos($value, '0000-00-00') === 0) {
            return false;
        }

        return strtotime($value);
    }

    private static function period($timestamp)
    {
        return date('A', $timestamp) === 'AM' ? 'a. m.' : 'p. m.';
    }
}
