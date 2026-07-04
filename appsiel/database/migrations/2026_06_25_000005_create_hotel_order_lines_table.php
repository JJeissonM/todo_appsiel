<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelOrderLinesTable extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::create('hotel_order_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->integer('hotel_order_id')->unsigned();
            $table->integer('producto_id')->unsigned();
            $table->integer('room_id')->unsigned()->nullable();
            $table->string('description', 255)->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_value', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->string('source_type', 30)->default('MANUAL');
            $table->integer('source_id')->unsigned()->nullable();
            $table->timestamps();

            $table->index(array('empresa_id', 'hotel_order_id'));
            $table->index('producto_id');
            $table->index('room_id');
            $table->index(array('source_type', 'source_id'));
        });
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::drop('hotel_order_lines');
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
