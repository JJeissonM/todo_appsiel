<?php

namespace App\Matriculas;

use App\Core\Tercero;
use App\Matriculas\Tiporesponsable;
use App\Matriculas\Estudiante;
use Illuminate\Database\Eloquent\Model;

class Responsableestudiante extends Model
{
    protected $table = 'sga_responsableestudiantes';

    protected $fillable = ['id', 'direccion_trabajo', 'telefono_trabajo', 'puesto_trabajo', 'empresa_labora', 'jefe_inmediato', 'telefono_jefe', 'descripcion_trabajador_independiente', 'ocupacion', 'tiporesponsable_id', 'estudiante_id', 'tercero_id', 'created_at', 'updated_at'];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function tiporesponsable()
    {
        return $this->belongsTo(Tiporesponsable::class);
    }

    public function tercero()
    {
        return $this->belongsTo(Tercero::class);
    }
}
