<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use Auth;

class TiposAspecto extends Model
{
    protected $table = 'sga_tipos_aspectos';

    protected $fillable = ['descripcion','estado'];

    public $encabezado_tabla = ['Descripción','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = TiposAspecto::select('sga_tipos_aspectos.descripcion AS campo1','sga_tipos_aspectos.estado AS campo2','sga_tipos_aspectos.id AS campo3')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
