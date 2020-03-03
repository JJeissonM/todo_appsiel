<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class CategoriaFooter extends Model
{
    protected  $table = 'pw_categoria_footer';
    protected $fillable = ['id', 'texto', 'footer_id', 'created_at', 'updated_at'];

    public function footer(){
        return $this->belongsTo(Footer::class);
    }

    public function enlaces(){
        return $this->hasMany(EnlaceFooter::class,'categoria_id','id');
    }

}
