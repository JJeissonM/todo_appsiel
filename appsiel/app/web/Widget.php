<?php

namespace App\web;

use App\web\Aboutus;
use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $table = 'pw_widget';

    protected $fillable = ['id', 'orden', 'estado', 'pagina_id', 'seccion_id', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Widget::leftJoin('pw_paginas', 'pw_paginas.id', '=', 'pw_widget.pagina_id')
            ->leftJoin('pw_seccion', 'pw_seccion.id', '=', 'pw_widget.seccion_id')
            ->select(
                'pw_widget.id',
                'pw_paginas.titulo AS pagina_titulo',
                'pw_paginas.descripcion AS pagina_descripcion',
                'pw_seccion.nombre AS seccion_descripcion'
            )
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = 'Página: ' . $opcion->pagina_titulo . '(' . $opcion->pagina_descripcion . ') > Sección: ' . $opcion->seccion_descripcion;
        }

        return $vec;
    }



    public function elements_design()
    {
        return $this->belongsTo(WidgetsElementsDesign::class, 'widget_id');
    }


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

    public function comparallaxes()
    {
        return $this->hasMany(Comparallax::class);
    }

    public function stickies()
    {
        return $this->hasMany(Sticky::class);
    }

    public function guias_academicas()
    {
        return $this->hasMany(Sticky::class);
    }

    public function logins(){
        return $this->hasMany(Login::class);
    }
}
