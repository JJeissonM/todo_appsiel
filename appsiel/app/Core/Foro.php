<?php

namespace App\Core;

use App\Calificaciones\Asignatura;
use App\Matriculas\Curso;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Foro extends Model
{
    protected $table = 'core_foros';
    protected $fillable = ['id', 'titulo', 'contenido', 'periodo_id', 'user_id', 'curso_id', 'asignatura_id', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function asignatura()
    {
        return $this->belongsTo(Asignatura::class);
    }

    public function fororespuestas()
    {
        return $this->hasMany(Fororespuesta::class);
    }
}
