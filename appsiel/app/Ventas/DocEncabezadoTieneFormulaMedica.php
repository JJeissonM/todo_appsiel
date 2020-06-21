<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class DocEncabezadoTieneFormulaMedica extends Model
{
	protected $table = 'vtas_doc_encabezado_tiene_formula_medica';

    protected $fillable = ['vtas_doc_encabezado_id','formula_medica_id'];

    public $timestamps = false;
}
