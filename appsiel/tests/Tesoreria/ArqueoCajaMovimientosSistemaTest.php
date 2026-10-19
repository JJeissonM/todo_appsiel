<?php

use App\Http\Controllers\Tesoreria\ArqueoCajaController;
use App\Tesoreria\ArqueoCaja;
use App\Tesoreria\TesoCaja;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ArqueoCajaMovimientosSistemaControllerForTest extends ArqueoCajaController
{
    public function recalcularMovimientosParaVista($registro)
    {
        return $this->recalcular_movimientos_sistema_si_estan_vacios($registro);
    }
}

class ArqueoCajaMovimientosSistemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_show_y_print_reconstruyen_el_detalle_aunque_exista_un_total_parcial()
    {
        $configuracionHorasOriginal = config('tesoreria.usar_movimientos_tesoreria_por_hora');

        try {
            config(array('tesoreria.usar_movimientos_tesoreria_por_hora' => 0));

            $caja = TesoCaja::where('id', '>', 0)->first();
            $this->assertNotNull($caja);

            $fecha = '2099-10-19';
            $creadoPor = 'arqueo-movimientos-sistema@appsiel.test';
            $valor = 76543;
            $movimientoId = $this->crearMovimiento($caja, $fecha, $creadoPor, $valor);

            $registro = new ArqueoCaja();
            $registro->fecha = $fecha;
            $registro->core_empresa_id = (int)$caja->core_empresa_id;
            $registro->teso_caja_id = (int)$caja->id;
            $registro->pdv_id = 0;
            $registro->turno_operativo_id = null;
            $registro->fecha_hora_apertura = null;
            $registro->fecha_hora_cierre = null;
            $registro->base = 0;
            $registro->total_billetes = 0;
            $registro->total_monedas = 0;
            $registro->otros_saldos = 0;
            $registro->creado_por = $creadoPor;
            $registro->detalles_mov_entradas = array();
            $registro->detalles_mov_salidas = array();
            // Simula el caso defectuoso: se guardó un total, pero no sus líneas.
            $registro->total_mov_entradas = 1;
            $registro->total_mov_salidas = 0;

            $resultado = (new ArqueoCajaMovimientosSistemaControllerForTest())
                ->recalcularMovimientosParaVista($registro);

            $this->assertSame($valor, (int)$resultado->total_mov_entradas);
            $this->assertNotEmpty($resultado->detalles_mov_entradas);
            $this->assertSame($valor, (int)$resultado->detalles_mov_entradas[0]->valor_movimiento);
            $this->assertSame($movimientoId, (int)DB::table('teso_movimientos')->where('id', $movimientoId)->value('id'));
        } finally {
            config(array('tesoreria.usar_movimientos_tesoreria_por_hora' => $configuracionHorasOriginal));
        }
    }

    public function test_store_calcula_los_movimientos_sin_depender_del_permiso_de_bloqueo()
    {
        $arqueo = ArqueoCaja::first();
        $this->assertNotNull($arqueo);

        $datos = array(
            'url_id' => 3,
            'url_id_modelo' => 158,
            'billetes' => array(),
            'monedas' => array(),
            'movimientos_entradas' => '[]',
            'movimientos_salidas' => '[]',
            'total_billetes' => 0,
            'total_monedas' => 0,
            'otros_saldos' => 0,
            'sumar_efectivo_base_en_saldo_esperado' => 0,
        );

        $datosCalculados = array_merge($datos, array(
            'movimientos_entradas' => '[{"motivo":"Prueba","movimiento":"entrada","codigo_referencia_tercero":"","valor_movimiento":456}]',
            'movimientos_salidas' => '[]',
            'total_mov_entradas' => 456,
            'total_mov_salidas' => 0,
            'lbl_total_sistema' => 456,
            'total_saldo' => -456,
        ));

        $modelo = Mockery::mock(ArqueoCaja::class)->makePartial();
        $modelo->shouldReceive('get_datos_adicionales')->once()->andReturn($datosCalculados);

        $modelo->store_adicional($datos, $arqueo);

        $this->assertSame(456, (int)$arqueo->total_mov_entradas);
        $this->assertSame($datosCalculados['movimientos_entradas'], $arqueo->detalles_mov_entradas);
    }

    protected function crearMovimiento($caja, $fecha, $creadoPor, $valor)
    {
        $plantilla = (array)DB::table('teso_movimientos')->first();
        $this->assertNotEmpty($plantilla);
        unset($plantilla['id']);

        $motivo = DB::table('teso_motivos')->first();
        $this->assertNotNull($motivo);

        $plantilla = array_merge($plantilla, array(
            'fecha' => $fecha,
            'core_empresa_id' => (int)$caja->core_empresa_id,
            'turno_operativo_id' => null,
            'teso_motivo_id' => (int)$motivo->id,
            'teso_caja_id' => (int)$caja->id,
            'teso_cuenta_bancaria_id' => 0,
            'pdv_id' => null,
            'valor_movimiento' => (float)$valor,
            'descripcion' => 'Movimiento para reconstrucción de arqueo',
            'estado' => 'Activo',
            'creado_por' => $creadoPor,
            'modificado_por' => $creadoPor,
            'created_at' => $fecha . ' 10:00:00',
            'updated_at' => $fecha . ' 10:00:00',
        ));

        foreach (array('turno_operativo_id', 'pdv_id') as $columnaOpcional) {
            if (!Schema::hasColumn('teso_movimientos', $columnaOpcional)) {
                unset($plantilla[$columnaOpcional]);
            }
        }

        return (int)DB::table('teso_movimientos')->insertGetId($plantilla);
    }
}
