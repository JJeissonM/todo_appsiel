<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixApmDeviceCrudFieldTypes extends Migration
{
    protected $fieldNames = [
        'device_id',
        'device_name',
        'ip_address',
        'code_page',
        'serial_port',
        'baud_rate',
    ];

    public function up()
    {
        if (!Schema::hasTable('sys_campos')) {
            return;
        }

        DB::table('sys_campos')
            ->whereIn('name', $this->fieldNames)
            ->where('tipo', 'text')
            ->update(['tipo' => 'bsText']);
    }

    public function down()
    {
        if (!Schema::hasTable('sys_campos')) {
            return;
        }

        DB::table('sys_campos')
            ->whereIn('name', $this->fieldNames)
            ->where('tipo', 'bsText')
            ->update(['tipo' => 'text']);
    }
}
