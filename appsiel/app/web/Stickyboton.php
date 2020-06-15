<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Stickyboton extends Model
{
    protected $table = 'pw_stickybotons';
    protected $fillable = ['id', 'color', 'icono', 'enlace', 'texto', 'sticky_id', 'created_at', 'updated_at'];

    public function sticky()
    {
        return $this->belongsTo(Sticky::class);
    }
}
