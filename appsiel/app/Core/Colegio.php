<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class Colegio extends Model
{
	protected $table = 'sga_colegios';
	
    protected $fillable = ['empresa_id','descripcion', 'slogan', 'resolucion', 'direccion', 'telefonos','ciudad', 'piefirma1','piefirma2', 'maneja_puesto'];


    public $encabezado_tabla = ['ID','Descripcion','Slogan','Resolución','Dirección','Teléfono(s)','Acción'];

    public static function consultar_registros()
    {
    	$registros = Colegio::select('sga_colegios.id AS campo1','sga_colegios.descripcion AS campo2','sga_colegios.slogan AS campo3','sga_colegios.resolucion AS campo4','sga_colegios.direccion AS campo5','sga_colegios.telefonos AS campo6','sga_colegios.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function get_colegio_user()
    {
        return Colegio::where( 'empresa_id', Auth::user()->empresa_id )->get()->first();
    }
}
