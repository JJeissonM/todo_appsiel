<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class PilaDatosEmpresa extends Model
{
    protected $table = 'nom_pila_datos_empresa';
    protected $fillable = ['core_empresa_id', 'tipo_aportante', 'clase_aportante', 'forma_presentacion', 'tipo_persona', 'naturaleza_juridica', 'tipo_pagador_pensiones', 'tipo_accion', 'administradora_riesgos_laborales_id', 'actividad_economica_ciiu', 'rep_legal_core_tercero_id', 'porcentaje_sena', 'porcentaje_icbf', 'porcentaje_caja_compensacion', 'estado'];

    public function empresa()
    {
        return $this->belongsTo('App\Core\Empresa', 'core_empresa_id');
    }

    public function entidad_arl()
    {
        return $this->belongsTo(NomEntidad::class, 'administradora_riesgos_laborales_id');
    }

    public function representante_legal()
    {
        return $this->belongsTo('App\Core\Tercero', 'rep_legal_core_tercero_id');
    }

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empresa', 'Tipo de aportante', 'Clase de aportante', 'Forma de presentación', 'Tipo de persona', 'Naturaleza jurídica', 'Tipo pagador pensiones', 'Tipo de acción', 'Administradora de riesgos laborales', 'Actividad económica (CIIU)', 'Representante Legal', '% SENA', '% ICBF', '% CCF', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';


    public static function consultar_registros($nro_registros, $search)
    {
        return PilaDatosEmpresa::select(
            'nom_pila_datos_empresa.core_empresa_id AS campo1',
            'nom_pila_datos_empresa.tipo_aportante AS campo2',
            'nom_pila_datos_empresa.clase_aportante AS campo3',
            'nom_pila_datos_empresa.forma_presentacion AS campo4',
            'nom_pila_datos_empresa.tipo_persona AS campo5',
            'nom_pila_datos_empresa.naturaleza_juridica AS campo6',
            'nom_pila_datos_empresa.tipo_pagador_pensiones AS campo7',
            'nom_pila_datos_empresa.tipo_accion AS campo8',
            'nom_pila_datos_empresa.administradora_riesgos_laborales_id AS campo9',
            'nom_pila_datos_empresa.actividad_economica_ciiu AS campo10',
            'nom_pila_datos_empresa.rep_legal_core_tercero_id AS campo11',
            'nom_pila_datos_empresa.porcentaje_sena AS campo12',
            'nom_pila_datos_empresa.porcentaje_icbf AS campo13',
            'nom_pila_datos_empresa.porcentaje_caja_compensacion AS campo14',
            'nom_pila_datos_empresa.estado AS campo15',
            'nom_pila_datos_empresa.id AS campo16'
        )
            ->where("nom_pila_datos_empresa.core_empresa_id", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.tipo_aportante", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.clase_aportante", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.forma_presentacion", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.tipo_persona", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.naturaleza_juridica", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.tipo_pagador_pensiones", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.tipo_accion", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.administradora_riesgos_laborales_id", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.actividad_economica_ciiu", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.rep_legal_core_tercero_id", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.porcentaje_sena", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.porcentaje_icbf", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.porcentaje_caja_compensacion", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.estado", "LIKE", "%$search%")

            ->orderBy('nom_pila_datos_empresa.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = PilaDatosEmpresa::select(
            'nom_pila_datos_empresa.core_empresa_id AS EMPRESA',
            'nom_pila_datos_empresa.tipo_aportante AS TIPO_DE_APORTANTE',
            'nom_pila_datos_empresa.clase_aportante AS CLASE_DE_APORTANTE',
            'nom_pila_datos_empresa.forma_presentacion AS FORMA_DE_PRESENTACIÓN',
            'nom_pila_datos_empresa.tipo_persona AS TIPO_DE_PERSONA',
            'nom_pila_datos_empresa.naturaleza_juridica AS NATURALEZA_JURÍDICA',
            'nom_pila_datos_empresa.tipo_pagador_pensiones AS TIPO_PAGADOR_PENSIONES',
            'nom_pila_datos_empresa.tipo_accion AS TIPO_DE_ACCIÓN',
            'nom_pila_datos_empresa.administradora_riesgos_laborales_id AS ADM_DE_RIESGOS_LABORALES',
            'nom_pila_datos_empresa.actividad_economica_ciiu AS ACTIVIDAD_ECONÓMICA_(CIIU)',
            'nom_pila_datos_empresa.rep_legal_core_tercero_id AS %REPRESENTANTE_LEGAL',
            'nom_pila_datos_empresa.porcentaje_sena AS %_SENA',
            'nom_pila_datos_empresa.porcentaje_icbf AS %_ICBF',
            'nom_pila_datos_empresa.porcentaje_caja_compensacion AS %_CCF',
            'nom_pila_datos_empresa.estado AS ESTADO'
        )
            ->where("nom_pila_datos_empresa.core_empresa_id", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.tipo_aportante", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.clase_aportante", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.forma_presentacion", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.tipo_persona", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.naturaleza_juridica", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.tipo_pagador_pensiones", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.tipo_accion", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.administradora_riesgos_laborales_id", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.actividad_economica_ciiu", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.rep_legal_core_tercero_id", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.porcentaje_sena", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.porcentaje_icbf", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.porcentaje_caja_compensacion", "LIKE", "%$search%")
            ->orWhere("nom_pila_datos_empresa.estado", "LIKE", "%$search%")

            ->orderBy('nom_pila_datos_empresa.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PILA DATOS EMPRESA";
    }

    public static function opciones_campo_select()
    {
        $opciones = PilaDatosEmpresa::leftJoin('core_empresas', 'core_empresas.id', '=', 'nom_pila_datos_empresa.core_empresa_id')
            ->where('nom_pila_datos_empresa.estado', 'Activo')
            ->select('nom_pila_datos_empresa.id', 'core_empresas.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
