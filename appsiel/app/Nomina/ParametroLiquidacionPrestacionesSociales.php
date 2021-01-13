<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB; 

class ParametroLiquidacionPrestacionesSociales extends Model
{
    protected $table = 'nom_parametros_liquidacion_prestaciones_sociales';

    /*
        concepto_prestacion = { vacaciones | prima_legal | cesantias | intereses_cesantias }
        base_liquidacion= { 
                            sueldo: solo el sueldo del contrato 
                            sueldo_mas_promedio_agrupacion: sueldo del contrato + promedios de la agrupación
                            promedio_agrupacion: solo promedios de la agrupación (se debe incluir el sueldo en la agrupación para que lo tenga en cuenta)
                        }
    */
	protected $fillable = ['concepto_prestacion', 'grupo_empleado_id', 'nom_agrupacion_id', 'nom_concepto_id', 'nom_agrupacion2_id', 'base_liquidacion', 'cantidad_meses_a_promediar', 'dias_a_liquidar', 'sabado_es_dia_habil'];
	
	public $encabezado_tabla = ['Prestación', 'Grupo empleados', 'Agrupación de conceptos', 'Concepto', 'Base liquidación', 'Cantidad meses a promediar', 'Días a liquidar', 'Acción'];
	
	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

	public static function consultar_registros()
	{
	    return ParametroLiquidacionPrestacionesSociales::leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.grupo_empleado_id')
                        				->leftJoin('nom_agrupaciones_conceptos', 'nom_agrupaciones_conceptos.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.nom_agrupacion_id')
                                        ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.nom_concepto_id')
                                        ->select(
                        					'nom_parametros_liquidacion_prestaciones_sociales.concepto_prestacion AS campo1',
                        					'nom_grupos_empleados.descripcion AS campo2',
                                            'nom_agrupaciones_conceptos.descripcion AS campo3',
                                            DB::raw('CONCAT(nom_conceptos.id," - ",nom_conceptos.descripcion) AS campo4'),
                        					'nom_parametros_liquidacion_prestaciones_sociales.base_liquidacion AS campo5',
                        					'nom_parametros_liquidacion_prestaciones_sociales.cantidad_meses_a_promediar AS campo6',
                        					'nom_parametros_liquidacion_prestaciones_sociales.dias_a_liquidar AS campo7',
                        					'nom_parametros_liquidacion_prestaciones_sociales.id AS campo8')
                        			    ->get()
                        			    ->toArray();
	}

	public static function opciones_campo_select()
    {
        $opciones = ParametroLiquidacionPrestacionesSociales::where('nom_parametros_liquidacion_prestaciones_sociales.estado','Activo')
                    ->select('nom_parametros_liquidacion_prestaciones_sociales.id','nom_parametros_liquidacion_prestaciones_sociales.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{}';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
