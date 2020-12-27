<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'pw_prices';
    protected $fillable = ['id', 'title', 'tipo_fondo', 'fondo', 'repetir', 'direccion', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function priceitems()
    {
        return $this->hasMany(Priceitem::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
