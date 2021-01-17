<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class VariableExamen extends Model
{
    protected $table = 'salud_catalogo_variables_examenes';
	protected $fillable = ['descripcion', 'abreviatura', 'orden', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Abreviatura', 'Orden', 'Estado'];
    public static function consultar_registros($nro_registros)
    {
        $registros = VariableExamen::select('salud_catalogo_variables_examenes.descripcion AS campo1', 'salud_catalogo_variables_examenes.abreviatura AS campo2', 'salud_catalogo_variables_examenes.orden AS campo3', 'salud_catalogo_variables_examenes.estado AS campo4', 'salud_catalogo_variables_examenes.id AS campo5')
            ->orderBy('salud_catalogo_variables_examenes.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = VariableExamen::select('id','descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
