<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

//use App\Nomina\EmpleadoPlanilla;

use DB;

class PlanillaGenerada extends Model
{
    protected $table = 'nom_pila_planillas_generadas';
	protected $fillable = ['pila_datos_empresa_id', 'descripcion', 'fecha_final_mes', 'estado'];
	public $encabezado_tabla = ['Código', 'Descripción', 'Estado', 'Acción'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"nom_pila_show/id_fila"}';

    public function empleados()
    {
        return $this->belongsToMany(NomContrato::class,'nom_pila_empleados_planilla','planilla_generada_id','nom_contrato_id');
    }

    public function datos_empresa()
    {
        return $this->belongsTo( PilaDatosEmpresa::class,'pila_datos_empresa_id');
    }

    public function lapso()
    {
        $array_fecha = explode( '-', $this->fecha_final_mes );

        $dia_inicio = '01';

        $dia_fin = '30';
        // Mes de febrero
        if ( $array_fecha[1] == '02' )
        {
            $dia_fin = '28';
        }

        return (object)[ 
                        'fecha_inicial' => $array_fecha[0] . '-' . $array_fecha[1] . '-' . $dia_inicio,
                        'fecha_final' => $array_fecha[0] . '-' . $array_fecha[1] . '-' . $dia_fin
                    ];
    }

	public static function consultar_registros()
	{
	    return PlanillaGenerada::select('nom_pila_planillas_generadas.pila_datos_empresa_id AS campo1', 'nom_pila_planillas_generadas.descripcion AS campo2', 'nom_pila_planillas_generadas.fecha_final_mes AS campo3', 'nom_pila_planillas_generadas.id AS campo4')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PlanillaGenerada::where('nom_pila_planillas_generadas.estado','Activo')
                    ->select('nom_pila_planillas_generadas.id','nom_pila_planillas_generadas.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function store_adicional( $datos, $registro )
    {
    	$fecha_lapso = explode("-", $datos['fecha_final_mes'] );

    	$fecha_inicial = $fecha_lapso[0] . '-' . $fecha_lapso[1] . '-01';

        $empleados = NomDocRegistro::whereBetween( 'fecha', [ $fecha_inicial, $datos['fecha_final_mes'] ])
        							->select('nom_contrato_id')
        							->distinct('nom_contrato_id')
        							->get();

        // Se agregan todos los contratos al documento
        $i = 1;
        foreach ($empleados as $empleado)
        {
        	$contrato = NomContrato::find($empleado->nom_contrato_id);


        	/*
				cambiar la validacion siguiente por un campo que el empleado que diga: genera planilla SI: NO 
        	*/
        	if( $contrato->grupo_empleado_id != 5 ) // Grupo prepensionados
        	{
        		EmpleadoPlanilla::create( [
	                                        'orden' => $i,
	                                        'planilla_generada_id' => $registro->id,
	                                        'nom_contrato_id' => $contrato->id
	                                    ]);
	           	$i++;
        	}	            
        }
            
    }
}
