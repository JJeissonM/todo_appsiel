<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'pw_teams';
    protected $fillable = ['id', 'title', 'title_color', 'description', 'description_color', 'tipo_fondo', 'fondo', 'repetir', 'direccion', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function teamitems()
    {
        return $this->hasMany(Teamitem::class);
    }
}
