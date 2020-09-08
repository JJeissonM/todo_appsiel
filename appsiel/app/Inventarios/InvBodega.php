<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;
use Auth;

class InvBodega extends Model
{
    //protected $table = 'inv_bodegas'; 

    protected $fillable = ['core_empresa_id','descripcion','estado'];

    public $encabezado_tabla = ['Descripción','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = InvBodega::where('inv_bodegas.core_empresa_id', Auth::user()->empresa_id)
                    ->select('inv_bodegas.descripcion AS campo1','inv_bodegas.estado AS campo2','inv_bodegas.id AS campo3')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function movimientos()
    {
        return $this->hasMany('App\Inventarios\InvMovimiento');
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvBodega::where( 'estado','Activo' )
                            ->where( 'core_empresa_id', Auth::user()->empresa_id )
                            ->get();

        //$vec['']='';
        $vec = [];
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
