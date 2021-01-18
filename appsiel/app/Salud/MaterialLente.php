<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class MaterialLente extends Model
{
    protected $table = 'salud_material_lentes';
    protected $fillable = ['descripcion', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = MaterialLente::select(
            'salud_material_lentes.descripcion AS campo1',
            'salud_material_lentes.estado AS campo2',
            'salud_material_lentes.id AS campo3'
        )
            ->where("salud_material_lentes.descripcion", "LIKE", "%$search%")
            ->orWhere("salud_material_lentes.estado", "LIKE", "%$search%")
            ->orderBy('salud_material_lentes.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = MaterialLente::select(
            'salud_material_lentes.descripcion AS DESCRIPCIÓN',
            'salud_material_lentes.estado AS ESTADO'
        )
            ->where("salud_material_lentes.descripcion", "LIKE", "%$search%")
            ->orWhere("salud_material_lentes.estado", "LIKE", "%$search%")
            ->orderBy('salud_material_lentes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MATERIALES DE LENTES";
    }

    public static function opciones_campo_select()
    {
        $opciones = MaterialLente::select('salud_material_lentes.id', 'salud_material_lentes.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
