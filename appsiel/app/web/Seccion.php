<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'pw_seccion';
    protected $fillable = ['id','nombre','descripcion','preview','created_at','updated_at'];

    public $encabezado_tabla = ['ID','Nombre','Descripción','URL imágen','Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","imprimir":"no","eliminar":"no","cambiar_estado":"no"}';

    public function widgets(){
      return $this->hasMany(Widget::class,'seccion_id');
    }


    // METODO PARA LA VISTA INDEX
    public static function consultar_registros()
    {
        return Seccion::select(
								'id AS campo1',
								'nombre AS campo2',
								'descripcion AS campo3',
								'preview AS campo4',
								'id AS campo5'
							)
	                    ->get()
	                    ->toArray();
    }
}
