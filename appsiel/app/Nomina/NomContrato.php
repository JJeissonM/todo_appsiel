<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Core\Tercero;
use App\Nomina\MovimientoIbcEmpleado;
use App\Nomina\NomDocRegistro;

class NomContrato extends Model
{
    //protected $table = 'nom_contratos';
	protected $fillable = [ 'core_tercero_id', 'clase_contrato', 'cargo_id', 'horas_laborales', 'sueldo', 'salario_integral', 'fecha_ingreso', 'contrato_hasta', 'entidad_salud_id', 'entidad_pension_id', 'entidad_arl_id', 'estado', 'liquida_subsidio_transporte', 'planilla_pila_id', 'es_pasante_sena', 'entidad_cesantias_id', 'entidad_caja_compensacion_id', 'grupo_empleado_id'];

	public $encabezado_tabla = [ 'Núm. identificación', 'Empleado', 'Cargo', 'Sueldo', 'Fecha ingreso', 'Contrato hasta', 'Estado', 'Acción'];

    public function tercero()
    {
        return $this->belongsTo(Tercero::class,'core_tercero_id');
    }

    public function cargo()
    {
        return $this->belongsTo(NomCargo::class,'cargo_id');
    }

    public function entidad_salud()
    {
        return $this->belongsTo(NomEntidad::class,'entidad_salud_id');
    }

    public function entidad_pension()
    {
        return $this->belongsTo(NomEntidad::class,'entidad_pension_id');
    }

    public function entidad_arl()
    {
        return $this->belongsTo(NomEntidad::class,'entidad_arl_id');
    }

    public function entidad_cesantias()
    {
        return $this->belongsTo(NomEntidad::class,'entidad_cesantias_id');
    }

    public function entidad_caja_compensacion()
    {
        return $this->belongsTo(NomEntidad::class,'entidad_caja_compensacion_id');
    }

    public function planilla_pila()
    {
        return $this->belongsTo(NomEntidad::class,'planilla_pila_id');
    }

    public function registros_documentos_nomina()
    {
        return $this->hasMany( NomDocRegistro::class, 'core_tercero_id', 'core_tercero_id' );
    }

    public function salario_x_hora()
    {
        return $this->sueldo / config('nomina.horas_laborales');
    }

    public function valor_ibc()
    {
        $valor_ibc = MovimientoIbcEmpleado::where( 'nom_contrato_id', $this->id )->get()->last(); 

        if ( is_null( $valor_ibc ) )
        {
            return $this->sueldo;
        }
        
        return $valor_ibc;
    }

    public function get_registros_documentos_nomina_entre_fechas( $fecha_inicial, $fecha_final)
    {
        $todos_los_registros = $this->registros_documentos_nomina;
        $coleccion = collect();
        foreach ($todos_los_registros as $registro)
        {
            if ( $registro->fecha >= $fecha_inicial && $registro->fecha <= $fecha_final )
            {
                $coleccion[] = $registro;
            }
        }
        
        return $coleccion;
    }

	public static function consultar_registros()
	{
	    return NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
                        ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
                        ->select(
                                    'core_terceros.numero_identificacion AS campo1',
                                    'core_terceros.descripcion AS campo2',
                                    'nom_cargos.descripcion AS campo3',
                                    'nom_contratos.sueldo AS campo4',
                                    'nom_contratos.fecha_ingreso AS campo5',
                                    'nom_contratos.contrato_hasta AS campo6',
                                    'nom_contratos.estado AS campo7',
                                    'nom_contratos.id AS campo8')
            		    ->get()
            		    ->toArray();
	}


	public static function get_empleados($estado)
	{
		return NomContrato::leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
                        ->leftJoin('nom_cargos', 'nom_cargos.id', '=', 'nom_contratos.cargo_id')
                        ->where('nom_contratos.estado','LIKE',$estado.'%')
                        ->select(
                                    'core_terceros.descripcion AS empleado',
                                    'core_terceros.id AS core_tercero_id',
                                    'nom_cargos.descripcion AS cargo',
                                    'nom_contratos.sueldo AS salario',
                                    'core_terceros.numero_identificacion AS cedula')
                        ->get();
	}

    public static function opciones_campo_select()
    {
        $opciones = NomContrato::leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
                                ->where('nom_contratos.estado','Activo')
                                ->select('core_terceros.id','core_terceros.descripcion','core_terceros.numero_identificacion')
                                ->orderby('core_terceros.descripcion')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->numero_identificacion . ')';
        }

        return $vec;
    }
}
