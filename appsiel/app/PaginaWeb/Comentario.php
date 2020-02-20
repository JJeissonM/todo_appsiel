<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $table = 'pw_comentarios'; 

    protected $fillable = ['contenido', 'user_id', 'articulo_id', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['ID', 'Artículo','Contenido','Estado','Acción'];

    public static function consultar_registros()
    {

    	$registros = Comentario::leftJoin('pw_articulos','pw_articulos.id','=','pw_comentarios.articulo_id')
                                ->select('pw_comentarios.id AS campo1','pw_articulos.resumen AS campo2','pw_comentarios.contenido AS campo3','pw_comentarios.estado AS campo4','pw_comentarios.id AS campo5')
                                ->get()
                                ->toArray();

        return $registros;
    }

    public function user()
    {
        return $this->belongsTo('App\User')->withTrashed();
    }
    
    public function articulos()
    {
        return $this->belongsTo(Articulo::class)->withTrashed();
    }
}
