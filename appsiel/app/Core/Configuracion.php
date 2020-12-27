<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'core_configuraciones';

	protected $fillable = ['id_grado_quinto','id_formato_certificado_quinto','id_periodo_certificado_quinto'];
}
