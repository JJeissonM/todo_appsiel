<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use DB;

class ConsultaMedica extends Model
{
    protected $table = 'salud_consultas';
	
	protected $fillable = ['paciente_id', 'tipo_consulta', 'fecha', 'profesional_salud_id', 'consultorio_id', 'nombre_acompañante', 'parentezco_acompañante', 'documento_identidad_acompañante', 'motivo_consulta', 'diagnostico', 'indicaciones'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Paciente', 'Tipo Consulta', 'Fecha', 'Atendido por', 'Diagnóstico', 'Indicaciones'];


    public function formulas()
    {
        return $this->hasMany( 'App\Salud\FormulaOptica', 'consulta_id' );
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }


	public static function consultar_registros($nro_registros)
	{
		$select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS campo1';

		$select_raw2 = 'CONCAT( salud_consultas.fecha," ",DATE_FORMAT(salud_consultas.created_at, "%h:%i:%s %p") ) AS campo3';

		$registros = ConsultaMedica::leftJoin('salud_pacientes', 'salud_pacientes.id', '=', 'salud_consultas.paciente_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
			->select(DB::raw($select_raw), 'salud_consultas.tipo_consulta AS campo2', DB::raw($select_raw2), 'salud_consultas.profesional_salud_id AS campo4', 'salud_consultas.diagnostico AS campo5', 'salud_consultas.indicaciones AS campo6', 'salud_consultas.id AS campo7')
			->orderBy('salud_consultas.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	// El archivo js debe estar en la carpeta public
	//public $archivo_js = 'assets/js/salud_consulta_medica.js';

	public static function get_resumen_consultas( $fecha_desde, $fecha_hasta )
	{
		$select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS paciente_nombre_completo';

	    $select_raw2 = 'CONCAT( salud_consultas.fecha," ",DATE_FORMAT(salud_consultas.created_at, "%h:%i:%s %p") ) AS fecha';

	    return ConsultaMedica::leftJoin('salud_pacientes', 'salud_pacientes.id', '=', 'salud_consultas.paciente_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
                    ->whereBetween( 'salud_consultas.fecha', [ $fecha_desde, $fecha_hasta ] )
                    ->select( DB::raw( $select_raw ), 'salud_consultas.tipo_consulta AS tipo', DB::raw( $select_raw2 ), 'salud_pacientes.codigo_historia_clinica', 'salud_consultas.id', 'salud_pacientes.id AS paciente_id' )
                    ->get();
	}

}
