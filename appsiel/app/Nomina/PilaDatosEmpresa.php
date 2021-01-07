<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class PilaDatosEmpresa extends Model
{
    protected $table = 'nom_pila_datos_empresa';
	protected $fillable = ['core_empresa_id', 'tipo_aportante', 'clase_aportante', 'forma_presentacion', 'tipo_persona', 'naturaleza_juridica', 'tipo_pagador_pensiones', 'tipo_accion', 'administradora_riesgos_laborales_id', 'actividad_economica_ciiu', 'rep_legal_core_tercero_id', 'porcentaje_sena', 'porcentaje_icbf', 'porcentaje_caja_compensacion', 'estado'];

    public function empresa()
    {
        return $this->belongsTo( 'App\Core\Empresa','core_empresa_id');
    }

    public function entidad_arl()
    {
        return $this->belongsTo(NomEntidad::class,'administradora_riesgos_laborales_id');
    }

    public function representante_legal()
    {
        return $this->belongsTo('App\Core\tercero','rep_legal_core_tercero_id');
    }

	public $encabezado_tabla = ['Empresa', 'Tipo de aportante', 'Clase de aportante', 'Forma de presentación', 'Tipo de persona', 'Naturaleza jurídica', 'Tipo pagador pensiones', 'Tipo de acción', 'Administradora de riesgos laborales', 'Actividad económica (CIIU)', 'Representante Legal', '% SENA', '% ICBF', '% CCF', 'Estado', 'Acción'];

	public $urls_acciones = '{"create":"web/create"}';

	
	public static function consultar_registros()
	{
	    return PilaDatosEmpresa::select('nom_pila_datos_empresa.core_empresa_id AS campo1', 'nom_pila_datos_empresa.tipo_aportante AS campo2', 'nom_pila_datos_empresa.clase_aportante AS campo3', 'nom_pila_datos_empresa.forma_presentacion AS campo4', 'nom_pila_datos_empresa.tipo_persona AS campo5', 'nom_pila_datos_empresa.naturaleza_juridica AS campo6', 'nom_pila_datos_empresa.tipo_pagador_pensiones AS campo7', 'nom_pila_datos_empresa.tipo_accion AS campo8', 'nom_pila_datos_empresa.administradora_riesgos_laborales_id AS campo9', 'nom_pila_datos_empresa.actividad_economica_ciiu AS campo10', 'nom_pila_datos_empresa.rep_legal_core_tercero_id AS campo11', 'nom_pila_datos_empresa.porcentaje_sena AS campo12', 'nom_pila_datos_empresa.porcentaje_icbf AS campo13', 'nom_pila_datos_empresa.porcentaje_caja_compensacion AS campo14', 'nom_pila_datos_empresa.estado AS campo15', 'nom_pila_datos_empresa.id AS campo16')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PilaDatosEmpresa::leftJoin('core_empresas','core_empresas.id','=','nom_pila_datos_empresa.core_empresa_id')
        						->where('nom_pila_datos_empresa.estado','Activo')
                    ->select('nom_pila_datos_empresa.id','core_empresas.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
