<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use Auth;

class CatalogoAspecto extends Model
{
    protected $table = 'sga_catalogo_aspectos';

    protected $fillable = ['id_tipo_aspecto', 'descripcion', 'orden', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Orden', 'Tipo de aspecto', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros)
    {

        $registros = CatalogoAspecto::join('sga_tipos_aspectos', 'sga_tipos_aspectos.id', '=', 'sga_catalogo_aspectos.id_tipo_aspecto')
            ->orderBy('sga_catalogo_aspectos.orden', 'ASC')
            ->select('sga_catalogo_aspectos.orden AS campo1', 'sga_tipos_aspectos.descripcion AS campo2', 'sga_catalogo_aspectos.descripcion AS campo3', 'sga_catalogo_aspectos.estado AS campo4', 'sga_catalogo_aspectos.id AS campo5')
            ->orderBy('sga_catalogo_aspectos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }
}
