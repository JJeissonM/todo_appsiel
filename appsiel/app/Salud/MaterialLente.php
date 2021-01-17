<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class MaterialLente extends Model
{
    protected $table = 'salud_material_lentes';
	protected $fillable = ['descripcion','estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];
    public static function consultar_registros($nro_registros)
    {
        $registros = MaterialLente::select('salud_material_lentes.descripcion AS campo1', 'salud_material_lentes.estado AS campo2', 'salud_material_lentes.id AS campo3')
            ->orderBy('salud_material_lentes.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = MaterialLente::select('salud_material_lentes.id','salud_material_lentes.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
