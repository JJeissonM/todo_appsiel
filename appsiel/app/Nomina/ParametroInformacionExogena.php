<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class ParametroInformacionExogena extends Model
{
   	protected $table = 'nom_parametros_informacion_exogena';
    protected $fillable = ['descripcion', 'tipo_informante', 'agrupacion_salarios_id', 'agrupacion_emolumentos_eclesiasticos_id', 'agrupacion_honorarios_id', 'agrupacion_servicios_id', 'agrupacion_comisiones_id', 'agrupacion_prestaciones_sociales_id', 'agrupacion_viaticos_id', 'agrupacion_gastos_representacion_id', 'agrupacion_trabajo_cooperativo_id', 'agrupacion_otros_pagos_id', 'agrupacion_cesantias_e_intereses_pagadas_id', 'agrupacion_pensiones_jubilacion_id', 'agrupacion_aportes_salud_obligatoria_id', 'agrupacion_aportes_pension_obligatoria_y_fsp_id', 'agrupacion_aportes_voluntarios_pension_id', 'agrupacion_aportes_afc_id', 'agrupacion_aportes_avc_id', 'agrupacion_valores_retefuente_id', 'agrupacion_bonos_id', 'agrupacion_desde_recursos_publicos_para_educacion_id', 'agrupacion_alimentacion_mayores_41uvt_id', 'agrupacion_alimentacion_hasta_41uvt_id', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Descripción', 'Tipo Informante', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';
    
    public static function consultar_registros($nro_registros, $search)
    {
        return ParametroInformacionExogena::select('nom_parametros_informacion_exogena.descripcion AS campo1', 'nom_parametros_informacion_exogena.tipo_informante AS campo2', 'nom_parametros_informacion_exogena.estado AS campo3', 'nom_parametros_informacion_exogena.id AS campo4')
        ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $string = ParametroInformacionExogena::select('nom_parametros_informacion_exogena.descripcion AS campo1', 'nom_parametros_informacion_exogena.tipo_informante AS campo2', 'nom_parametros_informacion_exogena.estado AS campo3', 'nom_parametros_informacion_exogena.id AS campo4')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE PARÁMETROS PARA INFORMACIÓN EXÓGENA";
    }

    public static function opciones_campo_select()
    {
        $opciones = ParametroInformacionExogena::where('nom_parametros_informacion_exogena.estado','Activo')
                    ->select('nom_parametros_informacion_exogena.id','nom_parametros_informacion_exogena.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
