<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class ResultadoExamenMedico extends Model
{
    protected $table = 'salud_resultados_examenes';
	protected $fillable = ['paciente_id', 'consulta_id', 'examen_id', 'variable_id', 'organo_del_cuerpo_id', 'valor_resultado'];
	public $encabezado_tabla = ['', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ResultadoExamenMedico::select('salud_resultados_examenes.paciente_id AS campo', 'salud_resultados_examenes.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
