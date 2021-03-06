<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class TipoNovedad extends Model
{
    protected $table = 'sga_tipos_novedades';    

    protected $fillable = ['colegio_id', 'descripcion', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = TipoNovedad::select(
            'sga_tipos_novedades.descripcion AS campo1',
            'sga_tipos_novedades.estado AS campo2',
            'sga_tipos_novedades.id AS campo3'
        )->where("sga_tipos_novedades.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_tipos_novedades.estado", "LIKE", "%$search%")
            ->orderBy('sga_tipos_novedades.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = TipoNovedad::select(
            'sga_tipos_novedades.descripcion AS DESCRIPCIÓN',
            'sga_tipos_novedades.estado AS ESTADO'
        )->where("sga_tipos_novedades.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_tipos_novedades.estado", "LIKE", "%$search%")
            ->orderBy('sga_tipos_novedades.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TIPOS DE NOVEDADES";
    }

    public static function opciones_campo_select()
    {
        $opciones = TipoNovedad::where('estado','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
