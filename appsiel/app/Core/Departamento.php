<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;

class Departamento extends Model
{
    protected $table = 'core_departamentos'; 

    protected $fillable = ['codigo_pais','descripcion'];

    public $encabezado_tabla = ['Ciudad','Departamento/Estado','Pais','Acción'];

    public static function consultar_registros()
    {
    	return Departamento::leftJoin('core_paises','core_paises.id','=','core_departamentos.codigo_pais')
                            ->select(
                                        'core_departamentos.descripcion AS campo1',
                                        'core_paises.descripcion AS campo2',
                                        'core_ciudades.id AS campo3')
                            ->get()
                            ->toArray();
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class,'codigo_pais');
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class,'core_departamento_id');
    }

}
