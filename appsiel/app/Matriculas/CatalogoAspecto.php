<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use Auth;

class CatalogoAspecto extends Model
{
    protected $table = 'sga_catalogo_aspectos';

    protected $fillable = ['id_tipo_aspecto','descripcion','orden','estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Orden', 'Tipo de aspecto', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {

        $registros = CatalogoAspecto::join('sga_tipos_aspectos', 'sga_tipos_aspectos.id', '=', 'sga_catalogo_aspectos.id_tipo_aspecto')
            ->select(
                'sga_catalogo_aspectos.orden AS campo1',
                'sga_tipos_aspectos.descripcion AS campo2',
                'sga_catalogo_aspectos.descripcion AS campo3',
                'sga_catalogo_aspectos.estado AS campo4',
                'sga_catalogo_aspectos.id AS campo5'
            )
            ->where("sga_catalogo_aspectos.orden", "LIKE", "%$search%")
            ->orWhere("sga_tipos_aspectos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_catalogo_aspectos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_catalogo_aspectos.estado", "LIKE", "%$search%")
            ->orderBy('sga_catalogo_aspectos.orden', 'ASC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = CatalogoAspecto::join('sga_tipos_aspectos', 'sga_tipos_aspectos.id', '=', 'sga_catalogo_aspectos.id_tipo_aspecto')
            ->select(
                'sga_catalogo_aspectos.orden AS ORDEN',
                'sga_tipos_aspectos.descripcion AS TIPO_DE_ASPECTO',
                'sga_catalogo_aspectos.descripcion AS DESCRIPCION',
                'sga_catalogo_aspectos.estado AS ESTADO'
            )
            ->where("sga_catalogo_aspectos.orden", "LIKE", "%$search%")
            ->orWhere("sga_tipos_aspectos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_catalogo_aspectos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_catalogo_aspectos.estado", "LIKE", "%$search%")
            ->orderBy('sga_catalogo_aspectos.orden', 'ASC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO CATALOGO ASPECTO ESTUDIANTES";
    }
}
