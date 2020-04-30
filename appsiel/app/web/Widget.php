<?php

namespace App\web;

use App\web\Aboutus;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $table = 'pw_widget';
    protected $fillable = ['id', 'orden', 'estado', 'pagina_id', 'seccion_id', 'created_at', 'updated_at'];

    public function pagina()
    {
        return $this->belongsTo(Pagina::class, 'pagina_id');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }

    public function aboutuss()
    {
        return $this->hasMany(Aboutus::class);
    }

    public function galerias()
    {
        return $this->hasMany(Galeria::class);
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }

    public function articlesetups()
    {
        return $this->hasMany(Articlesetup::class);
    }

    public function contactenoss()
    {
        return $this->hasMany(Contactenos::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }

    public function preguntas()
    {
        return $this->hasMany(Preguntasfrecuentes::class);
    }

    public function testimoniales()
    {
        return $this->hasMany(Testimoniale::class);
    }

    public function tiendas()
    {
        return $this->hasMany(Tienda::class);
    }

    public function customhtml()
    {
        return $this->hasMany(CustomHtml::class);
    }
}
