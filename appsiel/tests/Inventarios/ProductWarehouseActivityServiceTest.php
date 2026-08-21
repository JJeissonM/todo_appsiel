<?php

use App\Inventarios\Services\ProductWarehouseActivityService;

class ProductWarehouseActivityServiceTest extends PHPUnit_Framework_TestCase
{
    public function test_zero_days_disables_inactivity_filter()
    {
        $service = new ProductWarehouseActivityService(0);

        $this->assertFalse($service->isEnabled());
        $this->assertNull($service->getInactivityLimitDate('2026-08-21'));
    }

    public function test_calculates_limit_date_from_report_cutoff()
    {
        $service = new ProductWarehouseActivityService(30);

        $this->assertTrue($service->isEnabled());
        $this->assertSame('2026-07-22', $service->getInactivityLimitDate('2026-08-21'));
    }

    public function test_rejects_invalid_cutoff_date()
    {
        $service = new ProductWarehouseActivityService(30);

        $this->assertNull($service->getInactivityLimitDate('21-08-2026'));
        $this->assertNull($service->getInactivityLimitDate(''));
    }

    public function test_negative_days_are_treated_as_disabled()
    {
        $service = new ProductWarehouseActivityService(-5);

        $this->assertFalse($service->isEnabled());
    }
}
