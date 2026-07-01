<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddMediosRecaudosToHotelOrderHeaders extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('hotel_order_headers')) {
            return;
        }

        if (!Schema::hasColumn('hotel_order_headers', 'lineas_registros_medios_recaudos')) {
            Schema::table('hotel_order_headers', function (Blueprint $table) {
                $table->text('lineas_registros_medios_recaudos')->nullable()->after('pos_doc_id');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('hotel_order_headers')) {
            return;
        }

        if (Schema::hasColumn('hotel_order_headers', 'lineas_registros_medios_recaudos')) {
            Schema::table('hotel_order_headers', function (Blueprint $table) {
                $table->dropColumn('lineas_registros_medios_recaudos');
            });
        }
    }
}
