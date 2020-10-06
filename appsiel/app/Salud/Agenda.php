<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $table = 'salud_agendas';

    protected $fillable = ['id', 'fecha', 'hora_inicio', 'hora_fin', 'consultorio_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = null;
    public static function consultar_registros()
    {
        //
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class);
    }
}
