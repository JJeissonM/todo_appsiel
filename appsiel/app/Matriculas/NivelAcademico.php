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

    //CAMBIOS POR COMPROBAR
    /*
    public static function sqlString($search)
    {
        $string = NivelAcademico::all()
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE NIVEL ACADEMICO";
    }*/
}
