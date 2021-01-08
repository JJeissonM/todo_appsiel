<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class PilaNovedades extends Model
{
    protected $table = 'nom_pila_liquidacion_novedades';
	protected $fillable = ['planilla_generada_id', 'nom_contrato_id', 'fecha_final_mes', 'ing', 'ret', 'tde', 'tae', 'tdp', 'tap', 'vsp', 'cor', 'vst', 'sln', 'ige', 'lma', 'vac', 'avp', 'vct', 'irl', 'salario_basico', 'tipo_de_salario','cantidad_dias_laborados', 'fecha_de_ingreso', 'fecha_de_retiro', 'fecha_inicial_variacion_permanente_de_salario_vsp', 'fecha_inicial_suspension_temporal_del_contrato_sln', 'fecha_final_suspension_temporal_del_contrato_sln', 'fecha_inicial_incapacidad_enfermedad_general_ige', 'fecha_final_incapacidad_enfermedad_general_ige', 'fecha_inicial_licencia_por_maternidad_lma', 'fecha_final_licencia_por_maternidad_lma', 'fecha_inicial_vacaciones_licencias_remuneradas_vac', 'fecha_final_vacaciones_licencias_remuneradas_vac', 'fecha_inicial_variacion_centro_de_trabajo_vct', 'fecha_final_variacion_centro_de_trabajo_vct', 'fecha_inicial_incapacidad_riesgos_laborales_irl', 'fecha_final_incapacidad_riesgos_laborales_irl', 'estado','aux_ibc_salud','aux_cantidad_dias_laborados','empleado_planilla_id'];

	public $encabezado_tabla = ['Planilla generada', 'Empleado', 'Fecha PILA', 'Novedades', 'Salario Basico', 'Tipo De Salario', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';
    
	public static function consultar_registros()
	{
	    return PilaNovedades::leftJoin('nom_contratos','nom_contratos.id','=','nom_pila_liquidacion_novedades.nom_contrato_id')
                                ->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
                                ->select(
                                    'nom_pila_liquidacion_novedades.planilla_generada_id AS campo1',
                                    'core_terceros.descripcion AS campo2',
                                    'nom_pila_liquidacion_novedades.fecha_final_mes AS campo3',
                                    'nom_pila_liquidacion_novedades.ing AS campo4',
                                    'nom_pila_liquidacion_novedades.salario_basico AS campo5',
                                    'nom_pila_liquidacion_novedades.tipo_de_salario AS campo6',
                                    'nom_pila_liquidacion_novedades.id AS campo7')
                        	    ->get()
                        	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PilaNovedades::where('nom_pila_liquidacion_novedades.estado','Activo')
                    ->select('nom_pila_liquidacion_novedades.id','nom_pila_liquidacion_novedades.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
