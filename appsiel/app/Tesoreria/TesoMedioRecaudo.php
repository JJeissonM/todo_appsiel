<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoMedioRecaudo extends Model
{
    protected $table = 'teso_medios_recaudo';

    /*
        comportamiento: { Efectivo | Tarjeta bancaria | Otro }
    */
    protected $fillable = ['descripcion','comportamiento','por_defecto','maneja_puntos'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Comportamiento', 'Por defecto', 'Maneja puntos'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = TesoMedioRecaudo::select(
            'teso_medios_recaudo.descripcion AS campo1',
            'teso_medios_recaudo.comportamiento AS campo2',
            'teso_medios_recaudo.por_defecto AS campo3',
            'teso_medios_recaudo.maneja_puntos AS campo4',
            'teso_medios_recaudo.id AS campo5'
        )

            ->where("teso_medios_recaudo.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.comportamiento", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.por_defecto", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.maneja_puntos", "LIKE", "%$search%")
            ->orderBy('teso_medios_recaudo.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = TesoMedioRecaudo::select(
            'teso_medios_recaudo.descripcion AS DESCRIPCIÓN',
            'teso_medios_recaudo.comportamiento AS COMPORTAMIENTO',
            'teso_medios_recaudo.por_defecto AS POR_DEFECTO',
            'teso_medios_recaudo.maneja_puntos AS MANEJA_PUNTOS'
        )
            ->where("teso_medios_recaudo.descripcion", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.comportamiento", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.por_defecto", "LIKE", "%$search%")
            ->orWhere("teso_medios_recaudo.maneja_puntos", "LIKE", "%$search%")
            ->orderBy('teso_medios_recaudo.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MEDIOS DE RECAUDO";
    }

    public static function opciones_campo_select()
    {
        $opciones = TesoMedioRecaudo::all();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id . '-' . $opcion->comportamiento] = $opcion->descripcion;
        }

        return $vec;
    }
}
