<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;

class Ciudad extends Model
{
    protected $table = 'core_ciudades'; 

    protected $fillable = ['core_departamento_id','descripcion'];

    public $encabezado_tabla = ['Ciudad','Departamento/Estado','Pais','Acción'];

    public static function consultar_registros()
    {    	
    	return Ciudad::leftJoin('core_departamentos','core_departamentos.id','=','core_ciudades.core_departamento_id')
                            ->leftJoin('core_paises','core_paises.id','=','core_departamentos.codigo_pais')
                            ->select(
                                        'core_ciudades.descripcion AS campo1',
                                        'core_departamentos.descripcion AS campo2',
                                        'core_paises.descripcion AS campo3',
                                        'core_ciudades.id AS campo4')
                            ->get()
                            ->toArray();
    }

    public static function opciones_campo_select()
    {
        $opciones = Ciudad::all();

        $vec[''] = '';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion . ', ' . $opcion->departamento->descripcion;
        }

        return $vec;
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class,'core_departamento_id');
    }

}
