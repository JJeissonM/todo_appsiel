<?php

namespace App\Core;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Fororespuesta extends Model
{
    protected $table = 'core_fororespuestas';
    protected $fillable = ['id', 'contenido', 'user_id', 'foro_id', 'created_at', 'updated_at'];

    public function foro()
    {
        return $this->belongsTo(Foro::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
