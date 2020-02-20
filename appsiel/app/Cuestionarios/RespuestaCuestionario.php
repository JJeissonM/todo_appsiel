<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;

class RespuestaCuestionario extends Model
{
    protected $table = 'sga_respuestas_cuestionarios';

    protected $fillable = ['estudiante_id','actividad_id','cuestionario_id','respuesta_enviada','calificacion'];

    //public $encabezado_tabla = ['Nombre','Detalle','Estado','Acción'];

    /*public static function consultar_registros()
    {
        $registros = RespuestaCuestionario::select(sga_cuestionarios.name AS campo1',sga_cuestionarios.descripcion AS campo2',sga_cuestionarios.estado AS campo3',sga_cuestionarios.id AS campo4')
                    ->get()
                    ->toArray();

        return $registros;
    }
    */
}