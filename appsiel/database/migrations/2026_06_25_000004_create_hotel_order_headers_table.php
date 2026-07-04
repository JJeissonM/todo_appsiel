<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelOrderHeadersTable extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::create('hotel_order_headers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->integer('stay_id')->unsigned();
            $table->integer('cliente_id')->unsigned();
            $table->string('document_number', 30)->nullable();
            $table->dateTime('order_date');
            $table->string('status', 30)->default('ABIERTO');
            $table->string('invoice_type', 20)->nullable();
            $table->integer('sales_doc_id')->unsigned()->nullable();
            $table->integer('pos_doc_id')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->timestamps();

            $table->index(array('empresa_id', 'stay_id'));
            $table->index(array('empresa_id', 'cliente_id'));
            $table->index(array('empresa_id', 'status'));
            $table->index('sales_doc_id');
            $table->index('pos_doc_id');
        });
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::drop('hotel_order_headers');
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
