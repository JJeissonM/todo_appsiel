<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Articlecategory extends Model
{
    protected  $table = 'pw_articlecategories';
    protected  $fillable = ['id', 'titulo', 'descripcion', 'created_at', 'updated_at'];


    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Título', 'Descripción'];

    public static function consultar_registros($nro_registros, $search)
    {
        return Articlecategory::select(
            'pw_articlecategories.titulo AS campo1',
            'pw_articlecategories.descripcion AS campo2',
            'pw_articlecategories.id AS campo3'
        )->where("pw_articlecategories.titulo", "LIKE", "%$search%")
            ->orWhere("pw_articlecategories.descripcion", "LIKE", "%$search%")
            ->orderBy('pw_articlecategories.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Articlecategory::select(
            'pw_articlecategories.titulo AS TÍTULO',
            'pw_articlecategories.descripcion AS DESCRIPCIÓN'
        )->where("pw_articlecategories.titulo", "LIKE", "%$search%")
            ->orWhere("pw_articlecategories.descripcion", "LIKE", "%$search%")
            ->orderBy('pw_articlecategories.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CATEGORÍAS DE ARTÍCULOS";
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function articlesetups()
    {
        return $this->hasMany(Articlesetup::class);
    }
}
