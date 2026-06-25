<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;

class HotelOrderLine extends Model
{
    const SOURCE_ROOM = 'ROOM';
    const SOURCE_PRODUCT = 'PRODUCT';
    const SOURCE_SERVICE = 'SERVICE';
    const SOURCE_MANUAL = 'MANUAL';

    protected $table = 'hotel_order_lines';

    protected $fillable = array('empresa_id', 'hotel_order_id', 'producto_id', 'room_id', 'description', 'quantity', 'unit_price', 'discount', 'tax_value', 'line_total', 'source_type', 'source_id');

    public static function sourceTypes()
    {
        return array(self::SOURCE_ROOM, self::SOURCE_PRODUCT, self::SOURCE_SERVICE, self::SOURCE_MANUAL);
    }

    public static function calculateTotal($quantity, $unitPrice, $discount, $taxValue)
    {
        return ($quantity * $unitPrice) - $discount + $taxValue;
    }

    public function order()
    {
        return $this->belongsTo('App\Hotel\HotelOrderHeader', 'hotel_order_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Inventarios\InvProducto', 'producto_id');
    }

    public function room()
    {
        return $this->belongsTo('App\Hotel\HotelRoom', 'room_id');
    }
}
