<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTimeToHotelReservations extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('hotel_reservations')) {
            return;
        }

        Schema::table('hotel_reservations', function (Blueprint $table) {
            $table->dateTime('reserved_from')->change();
            $table->dateTime('reserved_until')->change();
        });

        // Antes ambos campos eran DATE e incluían todo el día final. Como el
        // nuevo intervalo usa un final exclusivo, éste pasa a las 00:00:00 del
        // día siguiente para conservar exactamente la duración histórica.
        DB::table('hotel_reservations')
            ->whereRaw("TIME(reserved_until) = '00:00:00'")
            ->update(array(
                'reserved_until' => DB::raw("DATE_ADD(reserved_until, INTERVAL 1 DAY)"),
            ));

        $this->updateCrudFields('fecha_hora', 'Reserva desde', 'Reserva hasta');
    }

    public function down()
    {
        if (!Schema::hasTable('hotel_reservations')) {
            return;
        }

        $this->updateCrudFields('fecha', 'Fecha desde', 'Fecha hasta');

        // Al volver al esquema inclusivo por días, el último instante ocupado
        // determina la fecha final que debe conservarse.
        DB::table('hotel_reservations')->update(array(
            'reserved_until' => DB::raw("DATE_SUB(reserved_until, INTERVAL 1 SECOND)"),
        ));

        Schema::table('hotel_reservations', function (Blueprint $table) {
            $table->date('reserved_from')->change();
            $table->date('reserved_until')->change();
        });
    }

    private function updateCrudFields($type, $fromLabel, $untilLabel)
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $modelId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\Hotel\\HotelReservation')
            ->value('id');

        if ($modelId == 0) {
            return;
        }

        $fields = DB::table('sys_modelo_tiene_campos AS model_field')
            ->join('sys_campos AS field', 'field.id', '=', 'model_field.core_campo_id')
            ->where('model_field.core_modelo_id', $modelId)
            ->whereIn('field.name', array('reserved_from', 'reserved_until'))
            ->select('field.id', 'field.name')
            ->get();

        foreach ($fields as $field) {
            DB::table('sys_campos')->where('id', $field->id)->update(array(
                'tipo' => $type,
                'descripcion' => $field->name == 'reserved_from' ? $fromLabel : $untilLabel,
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        }
    }
}
