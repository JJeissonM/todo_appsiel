<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;

class CoreEvento extends Model
{
    //protected $table = 'core_eventos'; 

    protected $fillable = ['descripcion', 'fecha_inicio', 'hora_inicio', 'fecha_fin', 'hora_fin', 'color', 'dow'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Inicia', 'Termina', 'Color', 'Día semana'];

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_eventos.fecha_inicio," ",core_eventos.hora_inicio) AS campo2';
        $select_raw2 = 'CONCAT(core_eventos.fecha_fin," ",core_eventos.hora_fin) AS campo3';

        $registros = CoreEvento::select(
            'core_eventos.descripcion AS campo1',
            DB::raw($select_raw),
            DB::raw($select_raw2),
            'core_eventos.color AS campo4',
            'core_eventos.dow AS campo5',
            'core_eventos.id AS campo6'
        )
            ->where("core_eventos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_eventos.fecha_inicio," ",core_eventos.hora_inicio)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_eventos.fecha_fin," ",core_eventos.hora_fin)'), "LIKE", "%$search%")
            ->orWhere("core_eventos.color", "LIKE", "%$search%")
            ->orWhere("core_eventos.dow", "LIKE", "%$search%")
            ->orderBy('core_eventos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }
    public static function sqlString($search)
    {
        $select_raw = 'CONCAT(core_eventos.fecha_inicio," ",core_eventos.hora_inicio) AS INICIA';
        $select_raw2 = 'CONCAT(core_eventos.fecha_fin," ",core_eventos.hora_fin) AS TERMINA';

        $string = CoreEvento::select(
            'core_eventos.descripcion AS DESCRIPCIÓN',
            DB::raw($select_raw),
            DB::raw($select_raw2),
            'core_eventos.color AS COLOR',
            'core_eventos.dow AS DÍA_SEMANA'
        )
            ->where("core_eventos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_eventos.fecha_inicio," ",core_eventos.hora_inicio)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_eventos.fecha_fin," ",core_eventos.hora_fin)'), "LIKE", "%$search%")
            ->orWhere("core_eventos.color", "LIKE", "%$search%")
            ->orWhere("core_eventos.dow", "LIKE", "%$search%")
            ->orderBy('core_eventos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE EVENTOS";
    }
}
