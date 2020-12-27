<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class EnlaceFooter extends Model
{

    protected  $table = 'pw_enlace_footer';
    protected $fillable = [ 'enlace', 'texto', 'icono', 'categoria_id', 'created_at', 'updated_at'];

    public function categoria(){
        return $this->belongsTo(CategoriaFooter::class);
    }


}
