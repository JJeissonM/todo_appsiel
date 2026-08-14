<?php

use App\Inventarios\Services\InventoryPhysicalShiftService;

class InventoryPhysicalShiftServiceTest extends PHPUnit_Framework_TestCase
{
    public function test_builds_range_for_shift_on_same_day()
    {
        $range = (new InventoryPhysicalShiftService())->getRange('2026-08-12', '06:00', '14:00');

        $this->assertSame('2026-08-12 06:00:00', $range['opening_at']);
        $this->assertSame('2026-08-12 14:00:00', $range['closing_at']);
        $this->assertSame('2026-08-12', $range['closing_date']);
    }

    public function test_night_shift_closes_on_next_day()
    {
        $range = (new InventoryPhysicalShiftService())->getRange('2026-08-12', '22:00:00', '06:00:00');

        $this->assertSame('2026-08-12 22:00:00', $range['opening_at']);
        $this->assertSame('2026-08-13 06:00:00', $range['closing_at']);
        $this->assertSame('2026-08-13', $range['closing_date']);
    }

    public function test_returns_null_when_range_is_incomplete_or_invalid()
    {
        $service = new InventoryPhysicalShiftService();

        $this->assertNull($service->getRange('2026-08-12', '06:00:00', null));
        $this->assertNull($service->getRange('12-08-2026', '06:00:00', '14:00:00'));
        $this->assertNull($service->getRange('2026-08-12', '25:00:00', '14:00:00'));
    }
}
