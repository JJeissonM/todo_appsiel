<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Articlecategory extends Model
{
    protected  $table = 'pw_articlecategories';
    protected  $fillable = ['id', 'titulo', 'descripcion', 'created_at', 'updated_at'];


    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public $encabezado_tabla = ['ID', 'Título', 'Descripción', 'Acción'];

    public static function consultar_registros()
    {
        return Articlecategory::select(
            'pw_articlecategories.titulo AS campo1',
            'pw_articlecategories.titulo AS campo2',
            'pw_articlecategories.descripcion AS campo3',
            'pw_articlecategories.id AS campo4'
        )
            ->get()
            ->toArray();
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
