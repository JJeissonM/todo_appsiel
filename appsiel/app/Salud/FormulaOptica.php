<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use App\Salud\Paciente;

use DB;

use App\Salud\ConsultaMedica;

class FormulaOptica extends Model
{
    protected $table = 'salud_formulas_opticas';
	
	protected $fillable = ['paciente_id', 'consulta_id', 'proximo_control', 'tipo_de_lentes', 'material', 'recomendaciones','uso','diagnostico','filtro'];

	public $encabezado_tabla = ['Nombre Completo', 'Control', 'Tipo de Lentes', 'Material', 'Recomendaciones', 'Acción'];
	
	public static function consultar_registros()
	{
	    $registros = FormulaOptica::select('salud_formulas_opticas.paciente_id AS campo1', 'salud_formulas_opticas.proximo_control AS campo2', 'salud_formulas_opticas.tipo_de_lentes AS campo3', 'salud_formulas_opticas.material AS campo4', 'salud_formulas_opticas.recomendaciones AS campo5', 'salud_formulas_opticas.id AS campo6')
	    ->get()
	    ->toArray();
	    return $registros;
	}

	public static function get_formulas_del_paciente( $paciente_id )
	{
		return FormulaOptica::leftJoin( 'salud_consultas', 'salud_consultas.id', '=', 'salud_formulas_opticas.consulta_id' )
							->leftJoin( 'salud_formula_tiene_examenes', 'salud_formula_tiene_examenes.formula_id', '=', 'salud_formulas_opticas.id' )
							->leftJoin( 'salud_examenes', 'salud_examenes.id', '=', 'salud_formulas_opticas.id' )
							->where( 'salud_formulas_opticas.paciente_id', $paciente_id )
							->select( 
										'salud_consultas.fecha',
										'salud_consultas.id AS consulta_id',
										'salud_formulas_opticas.id AS formula_id',
										'salud_examenes.id AS examen_id',
										'salud_examenes.descripcion' )
							->get()
							->toArray();
	} 

	/*
	  * Citas de control vencidas a la fecha
	*/
	public static function get_citas_control_vencidas( $fecha_desde )
	{
		$select_raw = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2) AS paciente_nombre_completo';

	    $select_raw2 = 'CONCAT( salud_consultas.fecha," ",DATE_FORMAT(salud_consultas.created_at, "%h:%i:%s %p") ) AS fecha';

	    $fecha_control = 'IF( salud_formulas_opticas.proximo_control = "Seis (6) meses", DATE(DATE_ADD(salud_consultas.fecha, INTERVAL 6 MONTH)), DATE(DATE_ADD(salud_consultas.fecha, INTERVAL 1 YEAR)) ) As fecha_control';


	    /*
		  *	hasta la línea ->get() se seleccionan las consultas de cada paciente y se crea un "campo" fecha_control con base en el texto del campo proximo_control
		  * la línea ->unique('paciente_id')->values()->all() elimina las consultas repetidas de cada pacientes, dejando solo la última consulta gracias al ordenamiento DESC por fecha
		  * 
	    */
	    $registros = FormulaOptica::leftJoin('salud_consultas', 'salud_consultas.id', '=', 'salud_formulas_opticas.consulta_id')
                    ->leftJoin('salud_pacientes', 'salud_pacientes.id', '=', 'salud_formulas_opticas.paciente_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'salud_pacientes.core_tercero_id')
                    ->where('salud_consultas.fecha','>=', $fecha_desde)
                    ->select( DB::raw( $select_raw ), 'salud_consultas.tipo_consulta AS tipo', DB::raw( $select_raw2 ), 'salud_pacientes.codigo_historia_clinica', 'salud_consultas.id', 'salud_consultas.fecha', 'salud_pacientes.id AS paciente_id', 'salud_formulas_opticas.proximo_control', DB::raw( $fecha_control ))
                    ->orderBy('salud_consultas.fecha','DESC')
                    ->get()
                    ->unique('paciente_id')->values()->all();

        // La cosulta anterior genera un array de objetos, se deben retirar los objetos (elementos del array) cuya fecha_control sea menor a la fecha de validacion, en este caso $fecha_un_mes_adelante_de_hoy
	    $fecha_un_mes_adelante_de_hoy = date_format( date_modify( date_create( date('Y-m-d') ), '+1 month'), 'Y-m-d' );
	    
	    foreach ($registros as $key => $value)
	    {	
	    	// Se retiran las que todavía no están vencidas ni próximas a vencer 
	    	if ( $value->fecha_control > $fecha_un_mes_adelante_de_hoy)
	    	{
	    		unset( $registros[$key] );
	    	}
	    }


		// Contar los citas de control próximas a vencer ()
	    $proxima_a_vencer = 0;
	    foreach ($registros as $key => $value)
	    {	
	    	$registros[$key]['dias'] = \Carbon\Carbon::parse( $registros[$key]['fecha_control'] )->diff( \Carbon\Carbon::now() )->format('%a días');
	    	if ( $value->fecha_control > date('Y-m-d') )
	    	{
	    		$proxima_a_vencer++;
	    	}
	    }

	    $registros['total_citas_vencidas'] = count($registros) - $proxima_a_vencer;
	    $registros['total_citas_proximas_a_vencer'] = $proxima_a_vencer;

        return $registros;
	}

    public function examenes()
    {
        return $this->belongsToMany( 'App\Salud\ExamenMedico', 'salud_formula_tiene_examenes', 'formula_id', 'examen_id' );
    }

}
