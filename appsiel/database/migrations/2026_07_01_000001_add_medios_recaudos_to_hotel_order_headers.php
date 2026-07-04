<?php

use Illuminate\Database\Migrations\Migration;

class AddMediosRecaudosToHotelOrderHeaders extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        // No-op. Se conserva para compatibilidad si esta migracion ya fue registrada.
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        // No-op.
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
