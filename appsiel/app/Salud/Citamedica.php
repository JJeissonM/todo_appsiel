<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class Citamedica extends Model
{
    protected $table = 'salud_citamedicas';

    protected $fillable = ['id', 'fecha', 'hora_inicio', 'hora_fin', 'estado', 'consultorio_id', 'profesional_id', 'paciente_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = null;
    public static function consultar_registros()
    {
        //
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class);
    }

    public function profesional()
    {
        return $this->belongsTo(ProfesionalSalud::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
