<?php

namespace App\Tesoreria\Services;

class ImporteChequeEnLetras
{
    public static function convertir($importe)
    {
        $pesos = self::redondear($importe);
        $texto = self::entero($pesos);
        $texto .= $pesos > 0 && $pesos % 1000000 === 0 ? ' DE PESOS' : ($pesos === 1 ? ' PESO' : ' PESOS');
        return $texto . ' MCTE.';
    }

    public static function redondear($importe)
    {
        if (!is_numeric($importe) || !is_finite((float) $importe) || $importe < 0) {
            throw new \InvalidArgumentException('El importe del cheque debe ser un numero positivo.');
        }
        $pesos = round((float) $importe, 0, PHP_ROUND_HALF_UP);
        if ($pesos > 999999999999) {
            throw new \InvalidArgumentException('El importe del cheque supera el limite de conversion a letras.');
        }
        return (int) $pesos;
    }

    protected static function entero($numero)
    {
        if ($numero >= 1000000) {
            $millones = (int) floor($numero / 1000000);
            $resto = $numero % 1000000;
            return ($millones === 1 ? 'UN MILLON' : self::entero($millones) . ' MILLONES')
                . ($resto ? ' ' . self::entero($resto) : '');
        }
        if ($numero >= 1000) {
            $miles = (int) floor($numero / 1000);
            $resto = $numero % 1000;
            return ($miles === 1 ? 'MIL' : self::entero($miles) . ' MIL')
                . ($resto ? ' ' . self::entero($resto) : '');
        }
        if ($numero === 100) {
            return 'CIEN';
        }
        if ($numero >= 100) {
            $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS',
                'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
            return $centenas[(int) floor($numero / 100)] . ($numero % 100 ? ' ' . self::entero($numero % 100) : '');
        }
        $unidades = ['CERO', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
            'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO',
            'DIECINUEVE', 'VEINTE', 'VEINTIUN', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO',
            'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'];
        if ($numero < 30) {
            return $unidades[$numero];
        }
        $decenas = [3 => 'TREINTA', 4 => 'CUARENTA', 5 => 'CINCUENTA', 6 => 'SESENTA',
            7 => 'SETENTA', 8 => 'OCHENTA', 9 => 'NOVENTA'];
        return $decenas[(int) floor($numero / 10)] . ($numero % 10 ? ' Y ' . $unidades[$numero % 10] : '');
    }
}
