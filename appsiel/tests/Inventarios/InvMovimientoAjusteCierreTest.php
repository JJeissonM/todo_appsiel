<?php

use App\Inventarios\InvMovimiento;

class InvMovimientoAjusteCierreTest extends TestCase
{
    public function test_ajuste_del_dia_siguiente_afecta_el_cierre_anterior_y_el_saldo_del_siguiente_turno()
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('CREATE TABLE inv_movimientos (id INTEGER, core_empresa_id INTEGER, core_tipo_transaccion_id INTEGER, turno_operativo_id INTEGER, inv_doc_encabezado_id INTEGER, cantidad REAL, created_at TEXT)');
        $db->exec('CREATE TABLE core_turnos_operativos (id INTEGER, core_empresa_id INTEGER, abierto_en TEXT, cerrado_en TEXT)');
        $db->exec('CREATE TABLE inv_doc_encabezados (id INTEGER, core_empresa_id INTEGER, core_tipo_transaccion_id INTEGER, turno_operativo_id INTEGER)');
        $db->exec('CREATE TABLE inv_documentos_relacionados (id INTEGER, inv_doc_encabezado_origen_id INTEGER, inv_doc_encabezado_relacionado_id INTEGER, tipo_relacion TEXT)');
        $db->exec("INSERT INTO core_turnos_operativos VALUES (101, 1, '2026-10-06 06:18:09', '2026-10-06 13:17:36')");
        $db->exec("INSERT INTO inv_doc_encabezados VALUES (2326, 1, 27, 101)");
        $db->exec("INSERT INTO inv_documentos_relacionados VALUES (52, 2326, 2341, 'inventario_fisico_ajuste')");
        $db->exec("INSERT INTO inv_movimientos VALUES
            (1, 1, 12, 101, 2324, 18, '2026-10-06 12:44:50'),
            (2, 1, 28, 101, 2341, -3, '2026-10-07 08:01:19')");

        $effective = InvMovimiento::fechaHoraEfectivaInventarioSql();
        $query = "SELECT SUM(cantidad) FROM inv_movimientos WHERE $effective <= '2026-10-06 21:16:01'";
        $this->assertEquals(15, $db->query($query)->fetchColumn());
        $this->assertSame('2026-10-06 13:17:36', $db->query("SELECT $effective FROM inv_movimientos WHERE id=2")->fetchColumn());
        $this->assertSame('2026-10-06 12:44:50', $db->query("SELECT $effective FROM inv_movimientos WHERE id=1")->fetchColumn());

        foreach (['2026-10-06 06:18:09', '2026-10-06 13:17:36'] as $boundary) {
            $db->exec("UPDATE inv_movimientos SET created_at='$boundary' WHERE id=1");
            $this->assertSame($boundary, $db->query("SELECT $effective FROM inv_movimientos WHERE id=1")->fetchColumn());
        }
        $db->exec("UPDATE inv_movimientos SET created_at='2026-10-06 06:18:08' WHERE id=1");
        $this->assertSame('2026-10-06 13:17:36', $db->query("SELECT $effective FROM inv_movimientos WHERE id=1")->fetchColumn());
        // Un ajuste histórico creado dentro del turno conserva su hora real.
        $db->exec("UPDATE inv_movimientos SET turno_operativo_id=NULL, created_at='2026-10-06 12:00:00' WHERE id=2");
        $this->assertSame('2026-10-06 12:00:00', $db->query("SELECT $effective FROM inv_movimientos WHERE id=2")->fetchColumn());
        $db->exec("UPDATE inv_movimientos SET turno_operativo_id=101, created_at='2026-10-07 08:01:19' WHERE id=2");

        // Traslado tardío: también corresponde al cierre del turno asociado.
        $db->exec("UPDATE inv_movimientos SET created_at='2026-10-07 09:00:00' WHERE id=1");
        $this->assertEquals(15, $db->query($query)->fetchColumn());
        $this->assertSame('2026-10-06 13:17:36', $db->query("SELECT $effective FROM inv_movimientos WHERE id=1")->fetchColumn());
        // Sin turno, se conserva la hora real de registro.
        $db->exec('UPDATE inv_movimientos SET turno_operativo_id=NULL WHERE id=1');
        $this->assertSame('2026-10-07 09:00:00', $db->query("SELECT $effective FROM inv_movimientos WHERE id=1")->fetchColumn());
        $db->exec("UPDATE inv_movimientos SET turno_operativo_id=101 WHERE id=1");

        // Ajuste histórico sin turno persistido: se recupera desde su IF de origen.
        $db->exec('UPDATE inv_movimientos SET turno_operativo_id=NULL WHERE id=2');
        $this->assertEquals(15, $db->query($query)->fetchColumn());

        // Una relación de otra empresa no puede imputar el cierre al ajuste.
        $db->exec('UPDATE inv_movimientos SET core_empresa_id=2 WHERE id=2');
        $this->assertSame('2026-10-07 08:01:19', $db->query("SELECT $effective FROM inv_movimientos WHERE id=2")->fetchColumn());
        // Turno nocturno: se comparan fechas y horas completas.
        $db->exec("UPDATE core_turnos_operativos SET abierto_en='2026-10-06 22:00:00', cerrado_en='2026-10-07 06:00:00' WHERE id=101");
        $db->exec("UPDATE inv_movimientos SET created_at='2026-10-07 02:00:00' WHERE id=1");
        $this->assertSame('2026-10-07 02:00:00', $db->query("SELECT $effective FROM inv_movimientos WHERE id=1")->fetchColumn());
        $db->exec("UPDATE inv_movimientos SET created_at='2026-10-07 06:00:01' WHERE id=1");
        $this->assertSame('2026-10-07 06:00:00', $db->query("SELECT $effective FROM inv_movimientos WHERE id=1")->fetchColumn());
        $db->exec('UPDATE core_turnos_operativos SET cerrado_en=NULL WHERE id=101');
        $this->assertSame('2026-10-07 06:00:01', $db->query("SELECT $effective FROM inv_movimientos WHERE id=1")->fetchColumn());

    }
}
