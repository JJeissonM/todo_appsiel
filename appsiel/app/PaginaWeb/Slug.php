<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

class Slug extends Model
{
    protected $table = 'pw_slugs';
	protected $fillable = ['slug', 'name_space_modelo', 'estado'];
	public $encabezado_tabla = ['Slug', 'Modelo', 'Estado', 'Acción'];
	
	public static function consultar_registros()
	{
	    return Slug::select('pw_slugs.slug AS campo1', 'pw_slugs.name_space_modelo AS campo2', 'pw_slugs.estado AS campo3', 'pw_slugs.id AS campo4')
				    ->get()
				    ->toArray();
	}
	
	public static function opciones_campo_select()
    {
        $opciones = Slug::where('pw_slugs.estado','Activo')
                    ->select('pw_slugs.id','pw_slugs.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public static function get_datos_relacionados( $slug_id )
    {
        $slug = Slug::find( $slug_id );

        $registro_relacionado = app( $slug->name_space_modelo )::where('slug_id',$slug_id)->get()->first();

        $registro_relacionado->name_space_modelo = $slug->name_space_modelo;
        $registro_relacionado->slug = $slug->slug;

        return $registro_relacionado;
    }
}
