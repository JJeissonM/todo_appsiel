<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class DiaFestivo extends Model
{
    protected $table = 'nom_dias_festivos';

    protected $fillable = ['fecha', 'observacion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Observación'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return DiaFestivo::select(
            'nom_dias_festivos.fecha AS campo1',
            'nom_dias_festivos.observacion AS campo2',
            'nom_dias_festivos.id AS campo3'
        )
            ->orWhere("nom_dias_festivos.fecha", "LIKE", "%$search%")
            ->orWhere("nom_dias_festivos.observacion", "LIKE", "%$search%")
            ->orderBy('nom_dias_festivos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = DiaFestivo::select(
            'nom_dias_festivos.fecha AS FECHA',
            'nom_dias_festivos.observacion AS OBSERVACIÓN',
            'nom_dias_festivos.id AS campo3'
        )
            ->orWhere("nom_dias_festivos.fecha", "LIKE", "%$search%")
            ->orWhere("nom_dias_festivos.observacion", "LIKE", "%$search%")
            ->orderBy('nom_dias_festivos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DIAS FESTIVO";
    }

    public static function opciones_campo_select()
    {
        $opciones = DiaFestivo::where('nom_dias_festivos.estado', 'Activo')
            ->select('nom_dias_festivos.id', 'nom_dias_festivos.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function validar_eliminacion($id)
    {
        return 'ok';
    }
}
