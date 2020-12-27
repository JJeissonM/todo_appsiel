<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Cuestionarios\Cuestionario;

class Pregunta extends Model
{
    protected $table = 'sga_preguntas';

    protected $fillable = ['descripcion', 'tipo', 'opciones', 'respuesta_correcta', 'estado', 'created_by'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Tipo', 'Opciones', 'Estado'];

    public static function consultar_registros($nro_registros)
    {
        return Pregunta::where('created_by', Auth::user()->id)
            ->select('sga_preguntas.descripcion AS campo1', 'sga_preguntas.tipo AS campo2', 'sga_preguntas.opciones AS campo3', 'sga_preguntas.estado AS campo4', 'sga_preguntas.id AS campo5')
            ->orderBy('sga_preguntas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/calificaciones/actividades_escolares/preguntas.js';

    public function cuestionarios()
    {
        return $this->belongsToMany('App\Cuestionarios\Cuestionario', 'sga_cuestionario_tiene_preguntas');
    }
}
