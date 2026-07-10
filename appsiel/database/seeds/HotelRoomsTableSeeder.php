<?php

use App\Ventas\ListaPrecioDetalle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HotelRoomsTableSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('hotel_rooms') || !Schema::hasTable('inv_productos')) {
            return;
        }

        $empresaId = $this->getEmpresaId();
        $grupoId = $this->getOrCreateHotelGroup($empresaId);

        if ($empresaId <= 0 || $grupoId <= 0) {
            $this->warn('No se pudieron crear habitaciones hoteleras: falta empresa o grupo de inventario.');
            return;
        }

        $productIds = array(
            'SENCILLA' => $this->getOrCreateService($empresaId, $grupoId, 'HOT-SENCILLA', 'Hospedaje habitacion sencilla', 80000),
            'DOBLE' => $this->getOrCreateService($empresaId, $grupoId, 'HOT-DOBLE', 'Hospedaje habitacion doble', 120000),
            'TRIPLE' => $this->getOrCreateService($empresaId, $grupoId, 'HOT-TRIPLE', 'Hospedaje habitacion triple', 150000),
            'FAMILIAR' => $this->getOrCreateService($empresaId, $grupoId, 'HOT-FAMILIAR', 'Hospedaje habitacion familiar', 220000),
            'SUITE' => $this->getOrCreateService($empresaId, $grupoId, 'HOT-SUITE', 'Hospedaje suite', 350000),
        );

        $rooms = array(
            array('101', 'SENCILLA', '1', 1),
            array('102', 'DOBLE', '1', 2),
            array('201', 'TRIPLE', '2', 3),
            array('202', 'FAMILIAR', '2', 4),
            array('301', 'SUITE', '3', 2),
            array('103', 'SENCILLA', '1', 1),
            array('104', 'DOBLE', '1', 2),
            array('105', 'SENCILLA', '1', 1),
            array('106', 'DOBLE', '1', 2),
            array('107', 'SENCILLA', '1', 1),
            array('108', 'DOBLE', '1', 2),
            array('109', 'SENCILLA', '1', 1),
            array('110', 'DOBLE', '1', 2),
            array('203', 'TRIPLE', '2', 3),
            array('204', 'TRIPLE', '2', 3),
        );

        foreach ($rooms as $room) {
            $productId = isset($productIds[$room[1]]) ? (int)$productIds[$room[1]] : 0;
            if ($productId <= 0) {
                continue;
            }

            $roomData = array(
                'empresa_id' => $empresaId,
                'room_number' => $room[0],
                'room_type' => $room[1],
                'inv_producto_id' => $productId,
                'floor' => $room[2],
                'capacity' => $room[3],
                'status' => 'DISPONIBLE',
                'description' => 'Habitacion de ejemplo creada por el seeder de Gestion Hotelera.',
                'is_active' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            );

            if (Schema::hasColumn('hotel_rooms', 'inv_bodega_id')) {
                $roomData['inv_bodega_id'] = $this->getOrCreateRoomWarehouse($empresaId, $room[0]);
            }

            DB::table('hotel_rooms')->updateOrInsert(array(
                'empresa_id' => $empresaId,
                'room_number' => $room[0],
            ), $roomData);
        }

        $this->ensureWarehousesForExistingRooms($empresaId);
    }

    private function ensureWarehousesForExistingRooms($empresaId)
    {
        if (!Schema::hasTable('hotel_rooms') || !Schema::hasColumn('hotel_rooms', 'inv_bodega_id')) {
            return;
        }

        $rooms = DB::table('hotel_rooms')
            ->where('empresa_id', $empresaId)
            ->where(function ($query) {
                $query->whereNull('inv_bodega_id')->orWhere('inv_bodega_id', 0);
            })
            ->get();

        foreach ($rooms as $room) {
            $bodegaId = $this->getOrCreateRoomWarehouse($empresaId, $room->room_number);
            if ($bodegaId > 0) {
                DB::table('hotel_rooms')->where('id', $room->id)->update(array(
                    'inv_bodega_id' => $bodegaId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ));
            }
        }
    }

    private function getEmpresaId()
    {
        if (Schema::hasTable('core_empresas')) {
            $empresaId = (int)DB::table('core_empresas')->orderBy('id')->value('id');
            if ($empresaId > 0) {
                return $empresaId;
            }
        }

        return 1;
    }

    private function getOrCreateHotelGroup($empresaId)
    {
        if (!Schema::hasTable('inv_grupos')) {
            return 0;
        }

        $grupoId = (int)DB::table('inv_grupos')
            ->where('core_empresa_id', $empresaId)
            ->whereIn('descripcion', array('Servicios hoteleros', 'Hospedaje'))
            ->value('id');

        if ($grupoId > 0) {
            DB::table('inv_grupos')->where('id', $grupoId)->update(array(
                'descripcion' => 'Servicios hoteleros',
                'estado' => 'Activo',
                'updated_at' => date('Y-m-d H:i:s'),
            ));
            return $grupoId;
        }

        $cuentaId = $this->getFirstId('contab_cuentas');
        if ($cuentaId <= 0) {
            $this->warn('No se pudo crear el grupo Servicios hoteleros: no existen cuentas contables.');
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        try {
            return (int)DB::table('inv_grupos')->insertGetId(array(
                'core_empresa_id' => $empresaId,
                'descripcion' => 'Servicios hoteleros',
                'nivel_padre' => 0,
                'tipo_nivel' => 'grupo',
                'imagen' => '',
                'orden' => 1,
                'cta_inventarios_id' => $cuentaId,
                'cta_ingresos_id' => $cuentaId,
                'mostrar_en_pagina_web' => 0,
                'estado' => 'Activo',
                'created_at' => $now,
                'updated_at' => $now,
            ));
        } catch (Exception $exception) {
            return 0;
        }
    }

    private function getOrCreateService($empresaId, $grupoId, $reference, $description, $price)
    {
        $serviceId = (int)DB::table('inv_productos')
            ->where('core_empresa_id', $empresaId)
            ->where('referencia', $reference)
            ->value('id');

        $now = date('Y-m-d H:i:s');
        $data = array(
            'descripcion' => $description,
            'core_empresa_id' => $empresaId,
            'tipo' => 'servicio',
            'unidad_medida1' => '94',
            'unidad_medida2' => '',
            'categoria_id' => '',
            'inv_grupo_id' => $grupoId,
            'impuesto_id' => $this->getTaxId(),
            'precio_compra' => 0,
            'precio_venta' => $price,
            'estado' => 'Activo',
            'referencia' => $reference,
            'codigo_barras' => '',
            'imagen' => '',
            'mostrar_en_pagina_web' => 0,
            'creado_por' => 'seeder',
            'modificado_por' => 'seeder',
            'detalle' => 'Servicio base para Gestion Hotelera.',
            'updated_at' => $now,
        );

        if ($serviceId > 0) {
            DB::table('inv_productos')->where('id', $serviceId)->update($data);
            return $serviceId;
        }

        $data['created_at'] = $now;
        $serviceId = (int)DB::table('inv_productos')->insertGetId($data);

        ListaPrecioDetalle::create(array(
            'lista_precios_id' => 1,
            'inv_producto_id' => $serviceId,
            'fecha_activacion' => $now,
            'precio' => $price,
            'created_at' => $now,
            'updated_at' => $now,
        ));

        return $serviceId;
    }

    private function getOrCreateRoomWarehouse($empresaId, $roomNumber)
    {
        if (!Schema::hasTable('inv_bodegas')) {
            return null;
        }

        $description = 'MINIBAR HAB. ' . $roomNumber;
        $warehouseId = (int)DB::table('inv_bodegas')
            ->where('core_empresa_id', $empresaId)
            ->where('descripcion', $description)
            ->value('id');

        $now = date('Y-m-d H:i:s');
        $data = array(
            'core_empresa_id' => $empresaId,
            'descripcion' => $description,
            'estado' => 'Activo',
            'updated_at' => $now,
        );

        $data = $this->onlyExistingColumns('inv_bodegas', $data);

        if ($warehouseId > 0) {
            DB::table('inv_bodegas')->where('id', $warehouseId)->update($data);
            return $warehouseId;
        }

        $data['created_at'] = $now;
        $data = $this->onlyExistingColumns('inv_bodegas', $data);

        return (int)DB::table('inv_bodegas')->insertGetId($data);
    }

    private function getTaxId()
    {
        $taxId = $this->getFirstId('contab_impuestos');
        return $taxId > 0 ? $taxId : 1;
    }

    private function getFirstId($table)
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        return (int)DB::table($table)->orderBy('id')->value('id');
    }

    private function onlyExistingColumns($table, $data)
    {
        foreach (array_keys($data) as $column) {
            if (!Schema::hasColumn($table, $column)) {
                unset($data[$column]);
            }
        }

        return $data;
    }

    private function warn($message)
    {
        if (isset($this->command) && method_exists($this->command, 'warn')) {
            $this->command->warn($message);
        }
    }
}
