<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Itemtestimonial extends Model
{
    protected $table = 'pw_itemtestimonials';
    protected $fillable = ['id', 'nombre', 'testimonio', 'cargo', 'foto', 'testimoniale_id', 'created_at', 'updated_at'];

    public function testimoniale()
    {
        return $this->belongsTo(Testimoniale::class);
    }
}
