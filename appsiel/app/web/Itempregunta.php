<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Itempregunta extends Model
{
    protected $table = 'pw_itempreguntas';
    protected $fillable = ['id', 'pregunta', 'respuesta', 'pregunta_id', 'created_at', 'updated_at'];

    public function preguntasfercuente()
    {
        return $this->belongsTo(Preguntasfrecuentes::class);
    }
}
