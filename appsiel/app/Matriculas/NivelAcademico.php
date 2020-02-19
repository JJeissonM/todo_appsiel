<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class NivelAcademico extends Model
{
    protected $table = 'sga_niveles';

    protected $fillable = [];

    public $encabezado_tabla = [];

   

    public static function consultar_registros()
    {
    	return NivelAcademico::all()->toArray();
    }
}
