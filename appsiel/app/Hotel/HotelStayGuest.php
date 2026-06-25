<?php

namespace App\Hotel;

use Illuminate\Database\Eloquent\Model;

class HotelStayGuest extends Model
{
    protected $table = 'hotel_stay_guests';

    protected $fillable = array('empresa_id', 'stay_id', 'cliente_id', 'is_main_guest', 'relationship');

    public function stay()
    {
        return $this->belongsTo('App\Hotel\HotelStay', 'stay_id');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'cliente_id');
    }
}
