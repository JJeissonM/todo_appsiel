<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HotelRoomsTableSeeder extends Seeder
{
    public function run()
    {
        /*
         * Este seeder no crea productos porque inv_productos tiene reglas propias.
         * Ajuste $empresaId y $productoId a datos reales antes de ejecutarlo.
         */
        $empresaId = 1;
        $productoId = 0;

        if ($productoId <= 0) {
            return;
        }

        $rooms = array(
            array('101', 'SENCILLA', '1', 1),
            array('102', 'DOBLE', '1', 2),
            array('201', 'TRIPLE', '2', 3),
            array('202', 'FAMILIAR', '2', 4),
            array('301', 'SUITE', '3', 2),
        );

        foreach ($rooms as $room) {
            DB::table('hotel_rooms')->insert(array(
                'empresa_id' => $empresaId,
                'room_number' => $room[0],
                'room_type' => $room[1],
                'inv_producto_id' => $productoId,
                'floor' => $room[2],
                'capacity' => $room[3],
                'status' => 'DISPONIBLE',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ));
        }
    }
}
