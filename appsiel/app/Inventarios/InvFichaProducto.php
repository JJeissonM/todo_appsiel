<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;
use Auth;


class InvFichaProducto extends Model
{

    protected $table = 'inv_ficha_producto';

    protected $fillable = ['id', 'key', 'descripcion', 'producto_id', 'created_at', 'updated_at'];

    public function producto(){
        return $this->belongsTo(InvProducto::class,'producto_id','id');
    }

}
