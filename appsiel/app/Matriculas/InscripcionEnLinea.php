<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Core\Tercero;
use App\Matriculas\Estudiante;

class InscripcionEnLinea extends Model
{
    protected $table = 'sga_inscripciones';

    protected $fillable = ['codigo', 'fecha', 'sga_grado_id', 'core_tercero_id', 'genero', 'fecha_nacimiento', 'ciudad_nacimiento', 'origen', 'enterado_por', 'observacion', 'acudiente', 'colegio_anterior', 'estado', 'creado_por', 'modificado_por'];

	public $vistas = '{"create":"matriculas.inscripciones.en_linea.create"}';

    public function grado()
    {
        return $this->belongsTo('App\Matriculas\Grado', 'sga_grado_id');
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }
}
