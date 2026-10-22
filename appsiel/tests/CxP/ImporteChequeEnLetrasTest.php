<?php

use App\Tesoreria\Services\ImporteChequeEnLetras;

class ImporteChequeEnLetrasTest extends PHPUnit_Framework_TestCase
{
    /** @dataProvider importes */
    public function testConvierteElImporteCompleto($importe, $esperado)
    {
        $this->assertNotContains('CENTAV', ImporteChequeEnLetras::convertir($importe));
        $this->assertSame(ImporteChequeEnLetras::convertir(ImporteChequeEnLetras::redondear($importe)), ImporteChequeEnLetras::convertir($importe));
        $this->assertSame($esperado . ' MCTE.', ImporteChequeEnLetras::convertir($importe));
    }

    public function importes()
    {
        return [
            ['4026760.78', 'CUATRO MILLONES VEINTISEIS MIL SETECIENTOS SESENTA Y UN PESOS'],
            ['1720233.60', 'UN MILLON SETECIENTOS VEINTE MIL DOSCIENTOS TREINTA Y CUATRO PESOS'],
            ['11045808.29', 'ONCE MILLONES CUARENTA Y CINCO MIL OCHOCIENTOS OCHO PESOS'],
            ['760.499', 'SETECIENTOS SESENTA PESOS'],
            ['760.50', 'SETECIENTOS SESENTA Y UN PESOS'],
            ['0', 'CERO PESOS'],
            ['1', 'UN PESO'],
            ['21', 'VEINTIUN PESOS'],
            ['100', 'CIEN PESOS'],
            ['101', 'CIENTO UN PESOS'],
            ['1000', 'MIL PESOS'],
            ['1000000', 'UN MILLON DE PESOS'],
            ['21000000', 'VEINTIUN MILLONES DE PESOS'],
            ['999999999', 'NOVECIENTOS NOVENTA Y NUEVE MILLONES NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE PESOS'],
            ['999999999.99', 'MIL MILLONES DE PESOS'],
            ['1000000000', 'MIL MILLONES DE PESOS'],
            ['1000000001', 'MIL MILLONES UN PESOS'],
            ['1001000000', 'MIL UN MILLONES DE PESOS'],
            ['1234567890.50', 'MIL DOSCIENTOS TREINTA Y CUATRO MILLONES QUINIENTOS SESENTA Y SIETE MIL OCHOCIENTOS NOVENTA Y UN PESOS'],
            ['2000000000.01', 'DOS MIL MILLONES DE PESOS'],
            ['999999999999.49', 'NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE MILLONES NOVECIENTOS NOVENTA Y NUEVE MIL NOVECIENTOS NOVENTA Y NUEVE PESOS'],
            ['999999999.999', 'MIL MILLONES DE PESOS'],
        ];
    }

    /** @dataProvider importesInvalidos */
    public function testRechazaImportesInvalidosEnLugarDeImprimirUnMensajeDeError($importe)
    {
        $this->setExpectedException('InvalidArgumentException');
        ImporteChequeEnLetras::convertir($importe);
    }

    public function importesInvalidos()
    {
        return [['999999999999.99'], [-1], ['texto'], [null], [INF], [NAN], ['1000000000000']];
    }
}
