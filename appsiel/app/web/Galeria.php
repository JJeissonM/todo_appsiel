<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Galeria extends Model
{
    protected $table = 'pw_galerias';
    protected $fillable = ['id', 'titulo', 'disposicion', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function albums()
    {
        return $this->hasMany(Album::class);
    }
}
