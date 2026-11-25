<?php

use App\Nomina\Services\PagoAutomaticoNominaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PagoAutomaticoNominaTipoDocumentoTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.pago_nomina_tipo_documento_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'pago_nomina_tipo_documento_testing');
        DB::purge('pago_nomina_tipo_documento_testing');

        Schema::create('sys_tipos_transacciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_modelo_id');
            $table->string('estado');
        });
        Schema::create('core_tipos_docs_apps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prefijo');
            $table->string('descripcion');
            $table->string('estado');
        });
        Schema::create('core_transaccion_tiene_documento', function (Blueprint $table) {
            $table->integer('core_tipo_transaccion_id');
            $table->integer('core_tipo_doc_id');
        });

        DB::table('sys_tipos_transacciones')->insert([
            'id' => PagoAutomaticoNominaService::TRANSACCION_PAGO_CXP,
            'core_modelo_id' => 150,
            'estado' => 'Activo',
        ]);
        DB::table('core_tipos_docs_apps')->insert([
            ['id' => 18, 'prefijo' => 'NB', 'descripcion' => 'NOTA BANCARIA', 'estado' => 'Activo'],
            ['id' => 23, 'prefijo' => 'CE', 'descripcion' => 'COMPROBANTE DE EGRESO', 'estado' => 'Activo'],
            ['id' => 99, 'prefijo' => 'XX', 'descripcion' => 'NO ASIGNADO', 'estado' => 'Activo'],
            ['id' => 100, 'prefijo' => 'IN', 'descripcion' => 'INACTIVO', 'estado' => 'Inactivo'],
        ]);
        DB::table('core_transaccion_tiene_documento')->insert([
            ['core_tipo_transaccion_id' => PagoAutomaticoNominaService::TRANSACCION_PAGO_CXP, 'core_tipo_doc_id' => 18],
            ['core_tipo_transaccion_id' => PagoAutomaticoNominaService::TRANSACCION_PAGO_CXP, 'core_tipo_doc_id' => 23],
            ['core_tipo_transaccion_id' => PagoAutomaticoNominaService::TRANSACCION_PAGO_CXP, 'core_tipo_doc_id' => 100],
        ]);
    }

    public function tearDown()
    {
        DB::purge('pago_nomina_tipo_documento_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function ofrece_solo_tipos_activos_asignados_a_pagos_de_cxp()
    {
        $opciones = PagoAutomaticoNominaService::opcionesTiposDocumentoPago();

        $this->assertSame([
            '' => 'Seleccione...',
            23 => 'CE - COMPROBANTE DE EGRESO',
            18 => 'NB - NOTA BANCARIA',
        ], $opciones);
    }

    /** @test */
    public function utiliza_el_tipo_de_documento_seleccionado()
    {
        $tipo = (new PagoAutomaticoNominaService())->resolverTipoDocumentoPago(23);

        $this->assertSame(23, (int) $tipo->id);
        $this->assertSame('CE', $tipo->prefijo);
    }

    /** @test */
    public function rechaza_un_tipo_no_asignado_a_la_transaccion()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        (new PagoAutomaticoNominaService())->resolverTipoDocumentoPago(99);
    }

    /** @test */
    public function exige_que_se_seleccione_el_tipo_de_documento()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        (new PagoAutomaticoNominaService())->resolverTipoDocumentoPago(0);
    }
}
