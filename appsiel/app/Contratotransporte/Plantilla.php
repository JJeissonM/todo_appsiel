<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = 'cte_plantillas';
    protected $fillable = ['id', 'titulo', 'direccion', 'telefono', 'correo', 'firma', 'pie_pagina1', 'titulo_atras', 'estado', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Plantilla', '¿Es la Actual?'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Plantilla::all();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->titulo;
        }

        return $vec;
    }

    public static function consultar_registros2($nro_registros)
    {
        return Plantilla::select(
            'cte_plantillas.titulo AS campo1',
            'cte_plantillas.estado AS campo2',
            'cte_plantillas.id AS campo3'
        )
            ->orderBy('cte_plantillas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public function plantillaarticulos()
    {
        return $this->hasMany(Plantillaarticulo::class);
    }

    public function planillacs()
    {
        return $this->hasMany(Planillac::class);
    }
}
