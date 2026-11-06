<?php
namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

class ColumnaAgrupacionCalificacion extends Model
{
    protected $table = 'sga_agrupacion_columnas';
    protected $fillable = ['columna_calificacion', 'label', 'porcentaje'];
    // El porcentaje interno se normaliza para conservar el peso total del grupo.
    public function getPesoAttribute()
    {
        $grupo = $this->agrupacion;
        $columnas = $grupo->relationLoaded('columnas') ? $grupo->columnas : $grupo->columnas()->get();
        $total = $columnas->sum('porcentaje');
        return $total > 0 ? (float)$grupo->peso * (float)$this->porcentaje / $total : 0;
    }
    public function agrupacion() { return $this->belongsTo(AgrupacionCalificacion::class, 'agrupacion_id'); }
}
