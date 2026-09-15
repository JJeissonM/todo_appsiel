<?php

use App\Core\Services\TurnoContext;
use App\Core\TurnoOperativo;
use App\Http\Controllers\Tesoreria\PagoController;
use App\Tesoreria\TesoDocEncabezadoPago;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoControllerUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_edita_el_motivo_y_conserva_el_turno_cerrado_del_pago()
    {
        $this->autenticarUsuario();
        list($documento, $turno) = $this->crearPagoConTurnoCerrado();

        $request = $this->requestEdicion($documento, 28);
        $response = (new PagoController())->update($request, $documento->id);

        $movimientos = $this->movimientosDelDocumento($documento)->get();
        $registro = TesoDocRegistro::where('teso_encabezado_id', $documento->id)->first();

        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        $this->assertCount(1, $movimientos);
        $this->assertNotNull($registro);
        $this->assertSame(28, (int)$movimientos->first()->teso_motivo_id);
        $this->assertSame(28, (int)$registro->teso_motivo_id);
        $this->assertSame((int)$turno->id, (int)$movimientos->first()->turno_operativo_id);
        $this->assertNull(app(TurnoContext::class)->current());
    }

    public function test_revierte_las_eliminaciones_si_falla_la_reconstruccion_del_pago()
    {
        $this->autenticarUsuario();
        list($documento) = $this->crearPagoConTurnoCerrado();
        $movimientoOriginal = $this->movimientosDelDocumento($documento)->first();
        $registroOriginal = TesoDocRegistro::where('teso_encabezado_id', $documento->id)->first();

        try {
            (new PagoController())->update($this->requestEdicion($documento, 999999), $documento->id);
            $this->fail('La edición con un motivo inexistente debió fallar.');
        } catch (\Exception $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        $this->assertNotNull(TesoMovimiento::find($movimientoOriginal->id));
        $this->assertNotNull(TesoDocRegistro::find($registroOriginal->id));
        $this->assertSame(27, (int)TesoMovimiento::find($movimientoOriginal->id)->teso_motivo_id);
        $this->assertNull(app(TurnoContext::class)->current());
    }

    protected function autenticarUsuario()
    {
        $user = User::where('empresa_id', 1)->first();
        $this->assertNotNull($user);
        $this->be($user);
    }

    protected function crearPagoConTurnoCerrado()
    {
        $user = User::where('empresa_id', 1)->first();
        $codigo = 'TEST-PAGO-EDIT-' . uniqid();

        $turnoId = DB::table('core_turnos_operativos')->insertGetId([
            'core_empresa_id' => 1,
            'contexto_tipo' => 'pdv',
            'contexto_id' => 1,
            'pdv_id' => 1,
            'teso_caja_id' => 1,
            'fecha_operativa' => '2026-09-10',
            'abierto_en' => '2026-09-10 08:00:00',
            'cerrado_en' => '2026-09-10 18:00:00',
            'abierto_por' => $user->id,
            'cerrado_por' => $user->id,
            'saldo_inicial' => 0,
            'saldo_cierre' => 100,
            'estado' => TurnoOperativo::ESTADO_CERRADO,
            'codigo' => $codigo,
            'clave_contexto_abierto' => null,
            'observaciones' => 'Prueba edición de pago',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $consecutivo = (int)DB::table('teso_doc_encabezados')
            ->where('core_tipo_transaccion_id', 17)
            ->max('consecutivo') + 1000;

        $documentoId = DB::table('teso_doc_encabezados')->insertGetId([
            'core_tipo_transaccion_id' => 17,
            'core_tipo_doc_app_id' => 23,
            'consecutivo' => $consecutivo,
            'fecha' => '2026-09-10',
            'core_empresa_id' => 1,
            'core_tercero_id' => 1,
            'turno_operativo_id' => $turnoId,
            'codigo_referencia_tercero' => '',
            'teso_tipo_motivo' => 'otros-pagos',
            'documento_soporte' => '',
            'descripcion' => 'Pago antes de editar',
            'teso_medio_recaudo_id' => 1,
            'teso_caja_id' => 1,
            'teso_cuenta_bancaria_id' => 0,
            'valor_total' => 100,
            'estado' => 'Activo',
            'creado_por' => $user->email,
            'modificado_por' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('teso_doc_registros')->insert([
            'teso_encabezado_id' => $documentoId,
            'teso_motivo_id' => 27,
            'core_tercero_id' => 1,
            'teso_medio_recaudo_id' => 1,
            'teso_caja_id' => 1,
            'teso_cuenta_bancaria_id' => 0,
            'detalle_operacion' => 'Línea original',
            'valor' => 100,
            'estado' => 'Activo',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        DB::table('teso_movimientos')->insert([
            'fecha' => '2026-09-10',
            'core_empresa_id' => 1,
            'core_tercero_id' => 1,
            'codigo_referencia_tercero' => '',
            'core_tipo_transaccion_id' => 17,
            'core_tipo_doc_app_id' => 23,
            'consecutivo' => $consecutivo,
            'turno_operativo_id' => $turnoId,
            'teso_medio_recaudo_id' => 1,
            'teso_motivo_id' => 27,
            'teso_caja_id' => 1,
            'teso_cuenta_bancaria_id' => 0,
            'pdv_id' => 1,
            'valor_movimiento' => -100,
            'documento_soporte' => '',
            'descripcion' => 'Línea original',
            'estado' => 'Activo',
            'creado_por' => $user->email,
            'modificado_por' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return [TesoDocEncabezadoPago::find($documentoId), TurnoOperativo::find($turnoId)];
    }

    protected function requestEdicion($documento, $motivoId)
    {
        $linea = [
            'teso_motivo_id' => $motivoId . '-Motivo',
            'linea_tercero_id' => '-',
            'detalle' => 'Línea editada',
            'valor' => '$100'
        ];

        return new Request([
            'url_id' => 3,
            'url_id_modelo' => 54,
            'url_id_transaccion' => 17,
            'fecha' => '2026-09-10',
            'core_empresa_id' => 1,
            'core_tercero_id' => 1,
            'pdv_id' => 1,
            'teso_tipo_motivo' => 'otros-pagos',
            'teso_medio_recaudo_id' => '1-Efectivo',
            'teso_caja_id' => 1,
            'teso_cuenta_bancaria_id' => 0,
            'valor_total' => 100,
            'documento_soporte' => '',
            'descripcion' => 'Pago después de editar',
            'estado' => 'Activo',
            'tabla_registros_documento' => json_encode([$linea, [], [], []])
        ]);
    }

    protected function movimientosDelDocumento($documento)
    {
        return TesoMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo);
    }
}
