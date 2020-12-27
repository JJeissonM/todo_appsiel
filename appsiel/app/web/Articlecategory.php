<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Articlecategory extends Model
{
    protected  $table = 'pw_articlecategories';
    protected  $fillable = ['id', 'titulo', 'descripcion', 'created_at', 'updated_at'];


    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Título', 'Descripción'];

    public static function consultar_registros($nro_registros)
    {
        return Articlecategory::select(
            'pw_articlecategories.titulo AS campo1',
            'pw_articlecategories.descripcion AS campo2',
            'pw_articlecategories.id AS campo3'
        )
            ->orderBy('pw_articlecategories.created_at', 'DESC')
            ->paginate($nro_registros);
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
