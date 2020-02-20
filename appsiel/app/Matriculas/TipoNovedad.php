<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class TipoNovedad extends Model
{
    protected $table = 'sga_tipos_novedades';    

    protected $fillable = ['colegio_id', 'descripcion', 'estado'];

    public $encabezado_tabla = ['Descripción','Estado','Acción'];

    public static function consultar_registros()
    {
        $registros = TipoNovedad::select('sga_tipos_novedades.descripcion AS campo1','sga_tipos_novedades.estado AS campo2','sga_tipos_novedades.id AS campo3')
            ->get()
            ->toArray();

        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = TipoNovedad::where('estado','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
