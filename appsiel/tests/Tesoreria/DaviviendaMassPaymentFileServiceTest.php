<?php

use App\Core\Empresa;
use App\Core\Tercero;
use App\Core\TipoDocumentoId;
use App\Tesoreria\Services\DaviviendaMassPaymentFileService;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoDocEncabezado;

class DaviviendaMassPaymentFileServiceTest extends PHPUnit_Framework_TestCase
{
    public function testBuildsA170CharacterControlRecordUsingCents()
    {
        $service = new TestableDaviviendaMassPaymentFileService();
        $payment = new TesoDocEncabezado();
        $sourceAccount = new TesoCuentaBancaria();
        $sourceAccount->descripcion = 'DAV.NOMINA 256000785376';
        $sourceAccount->tipo_cuenta = 'Ahorros';
        $payment->setRelation('cuenta_bancaria', $sourceAccount);

        $company = new Empresa();
        $company->numero_identificacion = '892300733';
        $company->digito_verificacion = '4';
        $company->setRelation('tipo_doc_identidad', $this->documentType('NIT'));

        $record = $service->buildControlRecord(
            $payment,
            $company,
            'NOMI',
            24774408200,
            106,
            new DateTime('2026-01-23 12:27:02')
        );

        $this->assertSame(170, strlen($record));
        $this->assertSame('RC', substr($record, 0, 2));
        $this->assertSame('0000008923007334', substr($record, 2, 16));
        $this->assertSame('NOMINOMI', substr($record, 18, 8));
        $this->assertSame('0000256000785376', substr($record, 26, 16));
        $this->assertSame('CA000051', substr($record, 42, 8));
        $this->assertSame('000000024774408200', substr($record, 50, 18));
        $this->assertSame('00010620260123122702', substr($record, 68, 20));
        $this->assertSame('03', substr($record, 112, 2));
    }

    public function testBuildsA170CharacterTransferRecord()
    {
        $service = new TestableDaviviendaMassPaymentFileService();
        $beneficiary = new Tercero();
        $beneficiary->numero_identificacion = '49789996';
        $beneficiary->setRelation('tipo_doc_identidad', $this->documentType('CC'));

        $record = $service->buildTransferRecord([
            'third_party' => $beneficiary,
            'identification' => '49789996',
            'identification_type' => '01',
            'account_number' => '69300000424',
            'account_type' => 'CA',
            'bank_code' => '000051',
            'amount_cents' => 286016400
        ]);

        $this->assertSame(170, strlen($record));
        $this->assertSame('TR', substr($record, 0, 2));
        $this->assertSame('0000000049789996', substr($record, 2, 16));
        $this->assertSame(str_repeat('0', 16), substr($record, 18, 16));
        $this->assertSame('0000069300000424', substr($record, 34, 16));
        $this->assertSame('CA000051', substr($record, 50, 8));
        $this->assertSame('000000000286016400', substr($record, 58, 18));
        $this->assertSame('0119999', substr($record, 82, 7));
    }

    protected function documentType($abbreviation)
    {
        $type = new TipoDocumentoId();
        $type->abreviatura = $abbreviation;

        return $type;
    }
}

class TestableDaviviendaMassPaymentFileService extends DaviviendaMassPaymentFileService
{
    public function buildControlRecord($payment, $company, $serviceCode, $totalCents, $count, DateTime $now)
    {
        return $this->controlRecord($payment, $company, $serviceCode, $totalCents, $count, $now);
    }

    public function buildTransferRecord(array $beneficiary)
    {
        return $this->transferRecord($beneficiary);
    }
}
