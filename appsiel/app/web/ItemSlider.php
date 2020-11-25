<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class ItemSlider extends Model
{
    protected $table =  'pw_itemslider';
    protected  $fillable = ['id', 'imagen', 'titulo', 'descripcion', 'button', 'enlace', 'colorTitle', 'colorText', 'slider_id', 'created_at', 'updated_at'];

    public function slider()
    {
        return $this->belongsTo(Slider::class);
    }
}
