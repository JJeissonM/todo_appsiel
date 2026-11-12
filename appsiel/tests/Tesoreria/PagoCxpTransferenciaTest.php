<?php

use App\Contabilidad\ContabMovimiento;
use App\Tesoreria\RegistroDeTransferenciaConsignacion;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMedioRecaudoDestino;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMovimiento;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PagoCxpTransferenciaTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown()
    {
        config(['tesoreria.medios_recaudo_por_tipo_registro.transferencia_consignacion' => []]);
        parent::tearDown();
    }

    public function test_guarda_una_transferencia_sin_descartar_la_ultima_linea_del_json()
    {
        $usuario = User::whereNotNull('empresa_id')->get()->first(function ($item) {
            return !$item->hasRole('Agencia');
        });
        $this->assertNotNull($usuario);
        $this->be($usuario);

        $documento = TesoDocEncabezado::where('core_empresa_id', $usuario->empresa_id)->first();
        $motivo = TesoMotivo::where('core_empresa_id', $usuario->empresa_id)
            ->where('movimiento', 'salida')
            ->where('teso_tipo_motivo', 'pago-proveedores')
            ->where('estado', 'Activo')
            ->first();
        $cuentaPlantilla = TesoCuentaBancaria::where('contab_cuenta_id', '>', 0)->first();

        $this->assertNotNull($documento);
        $this->assertNotNull($motivo);
        $this->assertNotNull($cuentaPlantilla);

        $cuenta = TesoCuentaBancaria::create([
            'core_empresa_id' => $usuario->empresa_id,
            'entidad_financiera_id' => $cuentaPlantilla->entidad_financiera_id,
            'tipo_cuenta' => $cuentaPlantilla->tipo_cuenta,
            'descripcion' => 'CUENTA TRANSFERENCIA PRUEBA GUARDADO',
            'por_defecto' => 'No',
            'estado' => 'Activo',
            'contab_cuenta_id' => $cuentaPlantilla->contab_cuenta_id
        ]);
        $medio = TesoMedioRecaudo::create([
            'descripcion' => 'Transferencia prueba guardado',
            'comportamiento' => 'Tarjeta bancaria',
            'por_defecto' => '',
            'maneja_puntos' => 'No',
            'estado' => 'Activo'
        ]);
        TesoMedioRecaudoDestino::create([
            'teso_medio_recaudo_id' => $medio->id,
            'teso_caja_id' => null,
            'teso_cuenta_bancaria_id' => $cuenta->id,
            'estado' => 'Activo'
        ]);
        config([
            'tesoreria.medios_recaudo_por_tipo_registro.transferencia_consignacion' => [$medio->descripcion]
        ]);

        $lineas = json_encode([[
            'tipo_operacion_id_transferencia_consignacion' => 'pago-proveedores',
            'teso_motivo_id_transferencia_consignacion' => $motivo->id,
            'banco_id_transferencia_consignacion' => $cuenta->id,
            'valor_transferencia_consignacion' => 1234,
            'numero_comprobante_transferencia_consignacion' => 'TEST-TRANSFERENCIA'
        ]]);

        (new RegistroDeTransferenciaConsignacion())->almacenar_registros($lineas, $documento);

        $this->assertTrue(TesoDocRegistro::where('teso_encabezado_id', $documento->id)
            ->where('teso_medio_recaudo_id', $medio->id)
            ->where('teso_cuenta_bancaria_id', $cuenta->id)
            ->where('valor', 1234)
            ->exists());
        $this->assertTrue(TesoMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo)
            ->where('teso_cuenta_bancaria_id', $cuenta->id)
            ->where('documento_soporte', 'Comprobante numero TEST-TRANSFERENCIA')
            ->exists());
        $this->assertTrue(ContabMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo)
            ->where('teso_cuenta_bancaria_id', $cuenta->id)
            ->where('contab_cuenta_id', $cuenta->contab_cuenta_id)
            ->exists());
    }
}
