<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;

class HotelRoom extends Model
{
    const TYPE_SENCILLA = 'SENCILLA';
    const TYPE_DOBLE = 'DOBLE';
    const TYPE_TRIPLE = 'TRIPLE';
    const TYPE_FAMILIAR = 'FAMILIAR';
    const TYPE_SUITE = 'SUITE';

    const STATUS_DISPONIBLE = 'DISPONIBLE';
    const STATUS_OCUPADA = 'OCUPADA';
    const STATUS_LIMPIEZA = 'LIMPIEZA';
    const STATUS_MANTENIMIENTO = 'MANTENIMIENTO';
    const STATUS_BLOQUEADA = 'BLOQUEADA';

    protected $table = 'hotel_rooms';

    protected $fillable = array('empresa_id', 'room_number', 'room_type', 'inv_producto_id', 'floor', 'capacity', 'status', 'description', 'is_active');

    public static function roomTypes()
    {
        return array(self::TYPE_SENCILLA, self::TYPE_DOBLE, self::TYPE_TRIPLE, self::TYPE_FAMILIAR, self::TYPE_SUITE);
    }

    public static function statuses()
    {
        return array(self::STATUS_DISPONIBLE, self::STATUS_OCUPADA, self::STATUS_LIMPIEZA, self::STATUS_MANTENIMIENTO, self::STATUS_BLOQUEADA);
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
}
