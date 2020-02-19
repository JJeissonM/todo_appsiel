<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'pw_categorias'; 

    protected $fillable = ['descripcion', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['ID', 'Descripción','Estado','Acción'];

    public static function consultar_registros()
    {

    	$registros = Categoria::select('pw_categorias.id AS campo1','pw_categorias.descripcion AS campo2','pw_categorias.estado AS campo3','pw_categorias.id AS campo4')
            ->get()
            ->toArray();

        return $registros;
    }
    
    public function articulos()
    {
        return $this->belongsTo(Articulo::class)->withTrashed();
    }

    public static function opciones_campo_select()
    {
        $opciones = Categoria::where('estado','Activo')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
