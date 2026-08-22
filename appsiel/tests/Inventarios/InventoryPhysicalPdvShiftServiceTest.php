<?php

use App\Inventarios\Services\InventoryPhysicalPdvShiftService;

class InventoryPhysicalPdvShiftServiceTest extends PHPUnit_Framework_TestCase
{
    public function test_uses_the_last_completed_shift_and_ignores_a_new_open_shift()
    {
        $service = new InventoryPhysicalPdvShiftService();

        $shift = $service->selectLastClosedShift([
            $this->movement(11, 2, 6, '2026-08-12 23:12:19'),
            $this->movement(10, 2, 6, '2026-08-12 23:02:01')
        ], [
            $this->movement(20, 2, 6, '2026-08-12 23:08:36')
        ]);

        $this->assertSame(10, $shift['apertura_id']);
        $this->assertSame(20, $shift['cierre_id']);
        $this->assertSame('23:02:01', $shift['hora_inicio']);
        $this->assertSame('23:08:36', $shift['hora_finalizacion']);
    }

    public function test_does_not_reuse_a_closing_that_belongs_after_the_next_opening()
    {
        $service = new InventoryPhysicalPdvShiftService();

        $shift = $service->selectLastClosedShift([
            $this->movement(11, 2, 6, '2026-08-12 23:12:19'),
            $this->movement(10, 2, 6, '2026-08-12 23:02:01')
        ], [
            $this->movement(20, 2, 6, '2026-08-12 23:20:00')
        ]);

        $this->assertSame(11, $shift['apertura_id']);
        $this->assertSame(20, $shift['cierre_id']);
    }

    public function test_supports_a_night_shift_that_closes_the_next_day()
    {
        $service = new InventoryPhysicalPdvShiftService();

        $shift = $service->selectLastClosedShift([
            $this->movement(10, 2, 6, '2026-08-12 22:00:00')
        ], [
            $this->movement(20, 2, 6, '2026-08-13 06:00:00')
        ]);

        $this->assertSame('2026-08-12', $shift['fecha']);
        $this->assertSame('22:00:00', $shift['hora_inicio']);
        $this->assertSame('06:00:00', $shift['hora_finalizacion']);
        $this->assertSame('2026-08-13 06:00:00', $shift['fecha_hora_cierre']);
    }

    private function movement($id, $pdvId, $cashierId, $createdAt)
    {
        return (object)[
            'id' => $id,
            'pdv_id' => $pdvId,
            'cajero_id' => $cashierId,
            'created_at' => $createdAt
        ];
    }
}
