<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;
use App\Salud\ExamenMedicoConsulta;

use DB;

class Odontograma extends Model
{

    protected $table = 'salud_odontograma';
    
    protected $fillable = [ 'id_consultas', 'odontograma_data', 'observaciones'];

    public $encabezado_tabla = null;
    public static function consultar_registros()
    {
        //
    }

    public function consultas()
    {
        return $this->belongsTo(ExamenMedicoConsulta::class);
    }
}