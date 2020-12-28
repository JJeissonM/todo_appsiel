<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class ParametroLiquidacionPrestacionesSociales extends Model
{
    protected $table = 'nom_parametros_liquidacion_prestaciones_sociales';

	protected $fillable = ['concepto_prestacion', 'grupo_empleado_id', 'nom_agrupacion_id', 'nom_agrupacion2_id', 'base_liquidacion', 'cantidad_meses_a_promediar', 'dias_a_liquidar', 'sabado_es_dia_habil'];
	
	public $encabezado_tabla = ['Prestación', 'Grupo empleados', 'Agrupación de conceptos', 'Base liquidación', 'Cantidad meses a promediar', 'Días a liquidar', 'Acción'];
	
	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

	public static function consultar_registros()
	{
	    return ParametroLiquidacionPrestacionesSociales::leftJoin('nom_grupos_empleados', 'nom_grupos_empleados.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.grupo_empleado_id')
                        							->leftJoin('nom_agrupaciones_conceptos', 'nom_agrupaciones_conceptos.id', '=', 'nom_parametros_liquidacion_prestaciones_sociales.nom_agrupacion_id')
                        							->select(
	    												'nom_parametros_liquidacion_prestaciones_sociales.concepto_prestacion AS campo1',
	    												'nom_grupos_empleados.descripcion AS campo2',
	    												'nom_agrupaciones_conceptos.descripcion AS campo3',
	    												'nom_parametros_liquidacion_prestaciones_sociales.base_liquidacion AS campo4',
	    												'nom_parametros_liquidacion_prestaciones_sociales.cantidad_meses_a_promediar AS campo5',
	    												'nom_parametros_liquidacion_prestaciones_sociales.dias_a_liquidar AS campo6',
	    												'nom_parametros_liquidacion_prestaciones_sociales.id AS campo7')
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
}
