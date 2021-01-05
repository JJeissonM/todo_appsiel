<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

use App\Nomina\NomContrato;

/*
	Desde aquí se deberían tomar todas las operaciones consalarios del sistema.
	Se debe refactorizar. Cambio en el diseño de la Aplicación Nomina.

	Por ahora solo se usa comom soporte para la liquidación de prestaciones sociales.
*/

class CambioSalario extends Model
{
    protected $table = 'nom_cambios_salarios';
	
	protected $fillable = ['nom_contrato_id', 'salario_anterior', 'nuevo_salario', 'fecha_modificacion', 'tipo_modificacion', 'observacion', 'creado_por', 'modificado_por'];
	
	public $encabezado_tabla = ['Empleado', 'Salario anterior', 'Nuevo salario', 'Fecha modificación', 'Tipo modificación', 'Observación', 'Creado por', 'Modificado por', 'Acción'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';
	
	public static function consultar_registros()
	{
	    return CambioSalario::leftJoin('nom_contratos','nom_contratos.id','=','nom_cambios_salarios.nom_contrato_id')
		    				->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
	                        ->select(
	    							'core_terceros.descripcion AS campo1',
	    							'nom_cambios_salarios.salario_anterior AS campo2',
	    							'nom_cambios_salarios.nuevo_salario AS campo3',
	    							'nom_cambios_salarios.fecha_modificacion AS campo4',
	    							'nom_cambios_salarios.tipo_modificacion AS campo5',
	    							'nom_cambios_salarios.observacion AS campo6',
	    							DB::raw('CONCAT(nom_cambios_salarios.creado_por,", ",nom_cambios_salarios.created_at) AS campo7'),
	    							DB::raw('CONCAT(nom_cambios_salarios.modificado_por,", ",nom_cambios_salarios.updated_at) AS campo8'),
	    							'nom_cambios_salarios.id AS campo9')
						    ->get()
						    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = CambioSalario::where('nom_cambios_salarios.estado','Activo')
                    ->select('nom_cambios_salarios.id','nom_cambios_salarios.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional( $datos, $registro)
    {
    	$empleado = NomContrato::find( (int)$datos['nom_contrato_id'] );

    	$registro->salario_anterior = $empleado->sueldo;
    	$registro->tipo_modificacion = 'directa';
    	$registro->save();

    	$empleado->sueldo = $registro->nuevo_salario;
    	$empleado->save();
    }
}
