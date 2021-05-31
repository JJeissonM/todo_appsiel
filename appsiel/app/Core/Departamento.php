<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;

class Departamento extends Model
{
    protected $table = 'core_departamentos'; 

    protected $fillable = ['codigo_pais','descripcion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción','Pais'];

    public static function consultar_registros($nro_registros, $search)
    {
        return Departamento::leftJoin('core_paises','core_paises.id','=','core_departamentos.codigo_pais')
                            ->select(
                                    'core_departamentos.descripcion AS campo1',
                                    'core_paises.descripcion AS campo2',
                                    'core_departamentos.id AS campo3')
                            ->where("core_departamentos.descripcion", "LIKE", "%$search%")
                            ->orWhere("core_paises.descripcion", "LIKE", "%$search%")
                            ->orderBy('core_departamentos.descripcion')
                            ->paginate($nro_registros);
    }

    public function pais()
    {
        return $this->belongsTo(Pais::class,'codigo_pais');
    }

    public function ciudades()
    {
        return $this->hasMany(Ciudad::class,'core_departamento_id');
    }

    public static function opciones_campo_select()
    {
        $opciones = Departamento::all();

        $vec[''] = '';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

}
