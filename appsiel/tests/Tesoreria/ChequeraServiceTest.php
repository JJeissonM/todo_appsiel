<?php

use App\Tesoreria\Services\ChequePaymentService;
use App\Tesoreria\Services\ChequeraService;
use App\Tesoreria\TesoChequera;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\ControlCheque;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMedioRecaudoDestino;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class ChequeraServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown()
    {
        config(['tesoreria.modalidad_cheques_pago' => 'cheques_creados']);
        parent::tearDown();
    }

    public function test_reserva_el_consecutivo_esperado_y_agota_la_chequera()
    {
        $cuenta = TesoCuentaBancaria::where('estado', 'Activo')->first();
        $this->assertNotNull($cuenta);

        $inicio = $this->siguienteRango();
        $chequera = TesoChequera::create([
            'teso_cuenta_bancaria_id' => $cuenta->id,
            'descripcion' => 'Chequera prueba reserva',
            'numero_inicial' => $inicio,
            'numero_final' => $inicio,
            'consecutivo_actual' => $inicio,
            'estado' => 'Activo'
        ]);

        $numero = DB::transaction(function () use ($chequera, $cuenta, $inicio) {
            return (new ChequeraService())->reservar_consecutivo($chequera->id, $cuenta->id, $inicio);
        });

        $chequera = $chequera->fresh();
        $this->assertSame($inicio, $numero);
        $this->assertSame($inicio + 1, (int)$chequera->consecutivo_actual);
        $this->assertSame('Agotada', $chequera->estado);

        try {
            DB::transaction(function () use ($chequera, $cuenta) {
                (new ChequeraService())->reservar_consecutivo($chequera->id, $cuenta->id);
            });
            $this->fail('No debe emitir un cheque por encima del número final.');
        } catch (\Exception $e) {
            $this->assertContains('supera el número final', $e->getMessage());
        }

        $this->assertSame($inicio + 1, (int)$chequera->fresh()->consecutivo_actual);
    }

    public function test_no_avanza_si_el_numero_mostrado_ya_no_es_el_actual()
    {
        $cuenta = TesoCuentaBancaria::where('estado', 'Activo')->first();
        $inicio = $this->siguienteRango();
        $chequera = TesoChequera::create([
            'teso_cuenta_bancaria_id' => $cuenta->id,
            'descripcion' => 'Chequera prueba concurrencia',
            'numero_inicial' => $inicio,
            'numero_final' => $inicio + 10,
            'consecutivo_actual' => $inicio,
            'estado' => 'Activo'
        ]);

        try {
            DB::transaction(function () use ($chequera, $cuenta, $inicio) {
                (new ChequeraService())->reservar_consecutivo($chequera->id, $cuenta->id, $inicio + 1);
            });
            $this->fail('Debió rechazar el consecutivo desactualizado.');
        } catch (\Exception $e) {
            $this->assertContains('consecutivo de la chequera cambió', $e->getMessage());
        }

        $this->assertSame($inicio, (int)$chequera->fresh()->consecutivo_actual);
    }

    public function test_anular_un_cheque_de_chequera_no_reutiliza_el_numero()
    {
        $documento = TesoDocEncabezado::first();
        $cuenta = TesoCuentaBancaria::where('core_empresa_id', $documento->core_empresa_id)
            ->where('estado', 'Activo')
            ->first();
        $this->assertNotNull($documento);
        $this->assertNotNull($cuenta);

        $inicio = $this->siguienteRango();
        $chequera = TesoChequera::create([
            'teso_cuenta_bancaria_id' => $cuenta->id,
            'descripcion' => 'Chequera prueba anulación',
            'numero_inicial' => $inicio,
            'numero_final' => $inicio + 10,
            'consecutivo_actual' => $inicio + 1,
            'estado' => 'Activo'
        ]);

        $cheque = ControlCheque::create([
            'fuente' => 'propio',
            'modalidad' => ChequePaymentService::MODALIDAD_CHEQUERA,
            'tercero_id' => $documento->core_tercero_id,
            'fecha_emision' => $documento->fecha,
            'fecha_cobro' => $documento->fecha,
            // Este valor se manipula a propósito: el backend debe ignorarlo.
            'numero_cheque' => $inicio + 7,
            'referencia_cheque' => '',
            'entidad_financiera_id' => $cuenta->entidad_financiera_id,
            'valor' => 1000,
            'detalle' => 'Prueba',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => '',
            'core_tipo_transaccion_id_origen' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id_origen' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo,
            'core_tipo_transaccion_id_consumo' => 0,
            'core_tipo_doc_app_id_consumo' => 0,
            'consecutivo_doc_consumo' => 0,
            'teso_caja_id' => 0,
            'teso_chequera_id' => $chequera->id,
            'teso_cuenta_bancaria_id' => $cuenta->id,
            'teso_doc_encabezado_id' => $documento->id,
            'tipo' => 'cheque_propio',
            'estado' => 'Emitido'
        ]);

        (new ChequePaymentService())->anularDocumento($documento);

        $this->assertSame('Anulado', $cheque->fresh()->estado);
        $this->assertSame($inicio + 1, (int)$chequera->fresh()->consecutivo_actual);
    }

    public function test_emite_cheque_con_relacion_directa_al_documento_cuenta_y_chequera()
    {
        config(['tesoreria.modalidad_cheques_pago' => 'usar_chequera']);

        $documento = TesoDocEncabezado::first();
        $cuenta = TesoCuentaBancaria::where('core_empresa_id', $documento->core_empresa_id)
            ->where('estado', 'Activo')
            ->first();
        $inicio = $this->siguienteRango();
        $chequera = TesoChequera::create([
            'teso_cuenta_bancaria_id' => $cuenta->id,
            'descripcion' => 'Chequera prueba emisión',
            'numero_inicial' => $inicio,
            'numero_final' => $inicio + 10,
            'consecutivo_actual' => $inicio,
            'estado' => 'Activo'
        ]);

        $linea = (object)[
            'teso_cuenta_bancaria_id_cheque' => $cuenta->id,
            'teso_chequera_id_cheque' => $chequera->id,
            'numero_cheque' => $inicio,
            'fecha_emision' => $documento->fecha,
            'fecha_cobro' => $documento->fecha,
            'referencia_cheque' => 'TEST',
            'detalle_cheque' => 'Emisión de prueba',
            'valor_cheque' => 2500
        ];

        $resultado = DB::transaction(function () use ($linea, $documento) {
            return (new ChequePaymentService())->emitirDesdeChequera($linea, $documento);
        });

        $cheque = $resultado['cheque'];
        $this->assertSame($inicio, (int)$cheque->numero_cheque);
        $this->assertSame((int)$documento->id, (int)$cheque->teso_doc_encabezado_id);
        $this->assertSame((int)$cuenta->id, (int)$cheque->teso_cuenta_bancaria_id);
        $this->assertSame((int)$chequera->id, (int)$cheque->teso_chequera_id);
        $this->assertSame('Emitido', $cheque->estado);
        $this->assertSame($inicio + 1, (int)$chequera->fresh()->consecutivo_actual);
        $this->assertTrue($documento->cheques_relacionados_pagos()->contains('id', $cheque->id));
    }

    public function test_formulario_de_pago_muestra_cuenta_y_chequera_en_la_nueva_modalidad()
    {
        config(['tesoreria.modalidad_cheques_pago' => 'usar_chequera']);
        $this->be(User::whereNotNull('empresa_id')->first());
        $_SERVER['HTTP_REFERER'] = 'http://localhost/web?id=3&id_modelo=150';

        $response = $this->call(
            'GET',
            '/tesoreria/pagos_cxp/create',
            ['id' => 3, 'id_modelo' => 150, 'id_transaccion' => 33],
            [],
            [],
            ['HTTP_REFERER' => 'http://localhost/web?id=3&id_modelo=150']
        );

        $this->assertResponseOk();
        $this->assertContains('teso_cuenta_bancaria_id_cheque', $response->getContent());
        $this->assertContains('teso_chequera_id_cheque', $response->getContent());
        $this->assertNotContains('CREAR NUEVO CHEQUE', $response->getContent());
        $this->assertNotContains('N° Cheque', $response->getContent());
    }

    public function test_formulario_de_pago_conserva_la_modalidad_actual()
    {
        config(['tesoreria.modalidad_cheques_pago' => 'cheques_creados']);
        $this->be(User::whereNotNull('empresa_id')->first());
        $_SERVER['HTTP_REFERER'] = 'http://localhost/web?id=3&id_modelo=150';

        $response = $this->call(
            'GET',
            '/tesoreria/pagos_cxp/create',
            ['id' => 3, 'id_modelo' => 150, 'id_transaccion' => 33],
            [],
            [],
            ['HTTP_REFERER' => 'http://localhost/web?id=3&id_modelo=150']
        );

        $this->assertResponseOk();
        $this->assertContains('name="caja_id_cheque"', $response->getContent());
        $this->assertNotContains('name="teso_chequera_id_cheque"', $response->getContent());
        $this->assertContains('USAR CHEQUES ALMACENADOS', $response->getContent());
    }

    public function test_endpoint_lista_solo_chequeras_disponibles_de_la_cuenta()
    {
        $usuario = User::whereNotNull('empresa_id')->first();
        $this->be($usuario);
        $cuenta = TesoCuentaBancaria::where('core_empresa_id', $usuario->empresa_id)
            ->where('estado', 'Activo')
            ->first();
        $inicio = $this->siguienteRango();
        $chequera = TesoChequera::create([
            'teso_cuenta_bancaria_id' => $cuenta->id,
            'descripcion' => 'Chequera visible endpoint',
            'numero_inicial' => $inicio,
            'numero_final' => $inicio + 10,
            'consecutivo_actual' => $inicio,
            'estado' => 'Activo'
        ]);

        $response = $this->call('GET', '/teso_cuentas_bancarias/' . $cuenta->id . '/chequeras/disponibles');

        $this->assertResponseOk();
        $json = json_decode($response->getContent(), true);
        $this->assertSame('ok', $json['status']);
        $this->assertContains($chequera->id, array_column($json['chequeras'], 'id'));
        $registro = null;
        foreach ($json['chequeras'] as $item) {
            if ((int)$item['id'] === (int)$chequera->id) {
                $registro = $item;
                break;
            }
        }
        $this->assertNotNull($registro);
        $this->assertSame((int)$chequera->numero_final, (int)$registro['numero_final']);
    }

    public function test_comportamiento_cheque_usa_cuentas_bancarias_como_destino()
    {
        $medio = TesoMedioRecaudo::create([
            'descripcion' => 'Cheque prueba destinos',
            'comportamiento' => 'Cheque',
            'por_defecto' => '',
            'maneja_puntos' => 'No',
            'estado' => 'Activo'
        ]);

        $this->assertTrue($medio->usa_cuenta_bancaria_como_destino());
    }

    public function test_permite_asociar_al_medio_cheque_una_cuenta_con_chequera()
    {
        $usuario = User::whereNotNull('empresa_id')->first();
        $this->be($usuario);
        $cuenta = TesoCuentaBancaria::get_cuentas_permitidas()->first();
        $this->assertNotNull($cuenta);

        $medio = TesoMedioRecaudo::create([
            'descripcion' => 'Cheque prueba relación bancaria',
            'comportamiento' => 'Cheque',
            'por_defecto' => '',
            'maneja_puntos' => 'No',
            'estado' => 'Activo'
        ]);
        $inicio = $this->siguienteRango();
        TesoChequera::create([
            'teso_cuenta_bancaria_id' => $cuenta->id,
            'descripcion' => 'Chequera prueba relación',
            'numero_inicial' => $inicio,
            'numero_final' => $inicio + 10,
            'consecutivo_actual' => $inicio,
            'estado' => 'Activo'
        ]);

        $this->call(
            'POST',
            '/teso_medios_recaudo/' . $medio->id . '/destinos',
            ['teso_cuenta_bancaria_id' => $cuenta->id],
            [],
            [],
            ['HTTP_REFERER' => 'http://localhost/web/' . $medio->id]
        );

        $this->assertResponseStatus(302);
        $this->assertTrue(TesoMedioRecaudoDestino::where('teso_medio_recaudo_id', $medio->id)
            ->where('teso_cuenta_bancaria_id', $cuenta->id)
            ->whereNull('teso_caja_id')
            ->exists());
        $this->assertTrue($medio->tiene_cuenta_bancaria_destino($cuenta->id));
    }

    protected function siguienteRango()
    {
        return max(900000, (int)TesoChequera::max('numero_final') + 100);
    }
}
