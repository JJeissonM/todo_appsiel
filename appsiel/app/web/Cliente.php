<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'pw_clientes';
    protected $fillable = ['id', 'logo', 'nombre', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
