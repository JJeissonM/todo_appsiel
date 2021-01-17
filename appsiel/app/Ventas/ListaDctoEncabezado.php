<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ListaDctoEncabezado extends Model
{
    protected $table = 'vtas_listas_dctos_encabezados';
	protected $fillable = ['descripcion', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];
    public static function consultar_registros($nro_registros)
    {
        $registros = ListaDctoEncabezado::select('vtas_listas_dctos_encabezados.descripcion AS campo1', 'vtas_listas_dctos_encabezados.estado AS campo2', 'vtas_listas_dctos_encabezados.id AS campo3')
            ->orderBy('vtas_listas_dctos_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = ListaDctoEncabezado::where('vtas_listas_dctos_encabezados.estado','Activo')
                    ->select('vtas_listas_dctos_encabezados.id','vtas_listas_dctos_encabezados.descripcion')
                    ->get();

        //$vec['']='';
        $vec = [];
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
