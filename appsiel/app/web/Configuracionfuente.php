<?php

namespace App\web;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class Configuracionfuente extends Model
{
    protected $table = 'pw_configuracionfuentes';
    protected $fillable = ['id', 'fuente_id', 'configuracion_id', 'created_at', 'updated_at'];

    public function fuente()
    {
        return $this->belongsTo(Fuente::class);
    }

    public function configuracion()
    {
        return $this->belongsTo(Configuraciones::class);
    }

    public function navegacions()
    {
        return $this->hasMany(Navegacion::class);
    }

    public function sliders()
    {
        return $this->hasMany(Slider::class);
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function preguntas()
    {
        return $this->hasMany(Preguntasfrecuentes::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function clientes()
    {
        return $this->hasMany(Cliente::class);
    }

    public function testimoniales()
    {
        return $this->hasMany(Testimoniale::class);
    }

    public function footers()
    {
        return $this->hasMany(Footer::class);
    }

    public function aboutuses()
    {
        return $this->hasMany(Aboutus::class);
    }

    public function articlesetups()
    {
        return $this->hasMany(Articlesetup::class);
    }

    public function contactenos()
    {
        return $this->hasMany(Contactenos::class);
    }

    public function modals()
    {
        return $this->hasMany(Modal::class);
    }

    public function galerias()
    {
        return $this->hasMany(Galeria::class);
    }
}
