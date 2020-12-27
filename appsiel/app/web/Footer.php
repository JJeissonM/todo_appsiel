<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Footer extends Model
{
    protected  $table = 'pw_footer';
    protected $fillable = ['ubicacion', 'copyright', 'texto', 'background', 'background2', 'ondas', 'animacion', 'color', 'configuracionfuente_id', 'created_at', 'updated_at'];

    public function categorias()
    {
        return $this->hasMany(CategoriaFooter::class, 'footer_id', 'id');
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
