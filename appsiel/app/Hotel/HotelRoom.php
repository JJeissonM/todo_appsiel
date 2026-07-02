<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelRoom extends Model
{
    const TYPE_SENCILLA = 'SENCILLA';
    const TYPE_DOBLE = 'DOBLE';
    const TYPE_TRIPLE = 'TRIPLE';
    const TYPE_FAMILIAR = 'FAMILIAR';
    const TYPE_SUITE = 'SUITE';

    const STATUS_DISPONIBLE = 'DISPONIBLE';
    const STATUS_RESERVADA = 'RESERVADA';
    const STATUS_OCUPADA = 'OCUPADA';
    const STATUS_LIMPIEZA = 'LIMPIEZA';
    const STATUS_MANTENIMIENTO = 'MANTENIMIENTO';
    const STATUS_BLOQUEADA = 'BLOQUEADA';

    protected $table = 'hotel_rooms';

    protected $fillable = array('empresa_id', 'room_number', 'room_type', 'inv_producto_id', 'floor', 'capacity', 'status', 'description', 'is_active');

    public $encabezado_tabla = array('<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Numero', 'Tipo', 'Producto', 'Piso', 'Capacidad', 'Estado', 'Activa');

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($room) {
            if (empty($room->empresa_id) && Auth::check()) {
                $room->empresa_id = Auth::user()->empresa_id;
            }

            if (empty($room->status)) {
                $room->status = self::STATUS_DISPONIBLE;
            }

            if ($room->is_active === null) {
                $room->is_active = 1;
            }
        });
    }

    public static function roomTypes()
    {
        return array(self::TYPE_SENCILLA, self::TYPE_DOBLE, self::TYPE_TRIPLE, self::TYPE_FAMILIAR, self::TYPE_SUITE);
    }

    public static function statuses()
    {
        return array(self::STATUS_DISPONIBLE, self::STATUS_RESERVADA, self::STATUS_OCUPADA, self::STATUS_LIMPIEZA, self::STATUS_MANTENIMIENTO, self::STATUS_BLOQUEADA);
    }

    public static function options($values)
    {
        $options = array();
        foreach ($values as $value) {
            $options[$value] = $value;
        }
        return $options;
    }

    public function product()
    {
        return $this->belongsTo('App\Inventarios\InvProducto', 'inv_producto_id');
    }

    public function stays()
    {
        return $this->hasMany('App\Hotel\HotelStay', 'room_id');
    }

    public function activeStay()
    {
        return $this->stays()->where('status', HotelStay::STATUS_ACTIVA);
    }

    public function reservations()
    {
        return $this->hasMany('App\Hotel\HotelReservation', 'room_id');
    }

    public function activeTodayReservation()
    {
        $today = date('Y-m-d');
        return $this->reservations()
            ->where('status', HotelReservation::STATUS_ACTIVA)
            ->where('reserved_from', '<=', $today)
            ->where('reserved_until', '>=', $today);
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_rooms.room_number AS campo1',
                'hotel_rooms.room_type AS campo2',
                DB::raw('CONCAT(inv_productos.id, " ", inv_productos.descripcion) AS campo3'),
                'hotel_rooms.floor AS campo4',
                'hotel_rooms.capacity AS campo5',
                'hotel_rooms.status AS campo6',
                DB::raw('IF(hotel_rooms.is_active = 1, "Si", "No") AS campo7'),
                'hotel_rooms.id AS campo8'
            )
            ->orderBy('hotel_rooms.room_number')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        return self::queryForIndex($search)
            ->select(
                'hotel_rooms.room_number AS NUMERO',
                'hotel_rooms.room_type AS TIPO',
                'inv_productos.descripcion AS PRODUCTO',
                'hotel_rooms.floor AS PISO',
                'hotel_rooms.capacity AS CAPACIDAD',
                'hotel_rooms.status AS ESTADO',
                'hotel_rooms.is_active AS ACTIVA'
            )
            ->toSql();
    }

    public static function tituloExport()
    {
        return 'HABITACIONES HOTELERAS';
    }

    public static function opciones_campo_select()
    {
        $query = self::orderBy('room_number');
        if (Auth::check()) {
            $query->where('empresa_id', Auth::user()->empresa_id);
        }

        $options = array('' => '');
        foreach ($query->get() as $room) {
            $options[$room->id] = $room->room_number . ' - ' . $room->room_type;
        }

        return $options;
    }

    public function validar_eliminacion($id)
    {
        $history = HotelStay::where('room_id', $id)->count();
        if ($history > 0) {
            return 'La habitacion tiene historial. Use inactivar en lugar de eliminar.';
        }

        return 'ok';
    }

    private static function queryForIndex($search)
    {
        $query = self::leftJoin('inv_productos', 'inv_productos.id', '=', 'hotel_rooms.inv_producto_id');

        if (Auth::check()) {
            $query->where('hotel_rooms.empresa_id', Auth::user()->empresa_id);
        }

        if ($search != '') {
            $query->where(function ($q) use ($search) {
                $q->where('hotel_rooms.room_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_rooms.room_type', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_rooms.floor', 'LIKE', '%' . $search . '%')
                    ->orWhere('hotel_rooms.status', 'LIKE', '%' . $search . '%')
                    ->orWhere('inv_productos.descripcion', 'LIKE', '%' . $search . '%');
            });
        }

        return $query;
    }
}
