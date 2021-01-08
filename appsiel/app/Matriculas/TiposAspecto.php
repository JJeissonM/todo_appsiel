<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use Auth;

class TiposAspecto extends Model
{
    protected $table = 'sga_tipos_aspectos';

    protected $fillable = ['descripcion', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = TiposAspecto::select(
            'sga_tipos_aspectos.descripcion AS campo1',
            'sga_tipos_aspectos.estado AS campo2',
            'sga_tipos_aspectos.id AS campo3'
        )->where("sga_tipos_aspectos.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_tipos_aspectos.estado", "LIKE", "%$search%")
            ->orderBy('sga_tipos_aspectos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }
}
