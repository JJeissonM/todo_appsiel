<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Priceitem extends Model
{
    protected $table = 'pw_priceitems';
    protected $fillable = ['id', 'imagen_cabecera', 'precio', 'text_color', 'button_color', 'button2_color', 'background_color', 'url', 'lista_items', 'price_id', 'created_at', 'updated_at'];

    public function price()
    {
        return $this->belongsTo(Price::class);
    }
}
