<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;

class HotelStay extends Model
{
    const STATUS_ACTIVA = 'ACTIVA';
    const STATUS_CERRADA = 'CERRADA';
    const STATUS_ANULADA = 'ANULADA';

    protected $table = 'hotel_stays';

    protected $fillable = array('empresa_id', 'main_cliente_id', 'room_id', 'check_in_at', 'expected_check_out_at', 'check_out_at', 'adults_count', 'children_count', 'total_guests', 'status', 'notes', 'created_by', 'closed_by');

    public static function statuses()
    {
        return array(self::STATUS_ACTIVA, self::STATUS_CERRADA, self::STATUS_ANULADA);
    }

    public function room()
    {
        return $this->belongsTo('App\Hotel\HotelRoom', 'room_id');
    }

    public function mainGuest()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'main_cliente_id');
    }

    public function guests()
    {
        return $this->hasMany('App\Hotel\HotelStayGuest', 'stay_id');
    }

    public function order()
    {
        return $this->hasOne('App\Hotel\HotelOrderHeader', 'stay_id');
    }
}
