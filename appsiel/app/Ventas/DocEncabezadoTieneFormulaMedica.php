<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class DocEncabezadoTieneFormulaMedica extends Model
{
	protected $table = 'vtas_doc_encabezado_tiene_formula_medica';

	// contenido_formula almacena una cadena JSON cuando el cliente no es un paciente
    protected $fillable = ['vtas_doc_encabezado_id','formula_medica_id','contenido_formula'];

    public $timestamps = false;
}
