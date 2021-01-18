<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class DifoSeccion extends Model
{
    protected $table = 'difo_secciones';

    protected $fillable = ['descripcion', 'presentacion', 'alineacion', 'cantidad_filas', 'cantidad_columnas', 'cantidad_espacios_despues', 'cantidad_espacios_antes', 'contenido', 'estilo_letra'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Contenido', 'Presentación', 'Alineación'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = DifoSeccion::select(
            'difo_secciones.descripcion AS campo1',
            'difo_secciones.contenido AS campo2',
            'difo_secciones.presentacion AS campo3',
            'difo_secciones.alineacion AS campo4',
            'difo_secciones.id AS campo5'
        )
            ->where("difo_secciones.descripcion", "LIKE", "%$search%")
            ->orWhere("difo_secciones.contenido", "LIKE", "%$search%")
            ->orWhere("difo_secciones.presentacion", "LIKE", "%$search%")
            ->orWhere("difo_secciones.alineacion", "LIKE", "%$search%")
            ->orderBy('difo_secciones.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = DifoSeccion::select(
            'difo_secciones.descripcion AS NOMBRE',
            'difo_secciones.contenido AS CONTENIDO',
            'difo_secciones.presentacion AS PRESENTACIÓN',
            'difo_secciones.alineacion AS ALINEACIÓN'
        )
            ->where("difo_secciones.descripcion", "LIKE", "%$search%")
            ->orWhere("difo_secciones.contenido", "LIKE", "%$search%")
            ->orWhere("difo_secciones.presentacion", "LIKE", "%$search%")
            ->orWhere("difo_secciones.alineacion", "LIKE", "%$search%")
            ->orderBy('difo_secciones.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DIFO SECCIONES";
    }
}
