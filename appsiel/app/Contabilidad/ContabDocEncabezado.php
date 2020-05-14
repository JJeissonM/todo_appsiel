<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ContabDocEncabezado extends Model
{
    //protected $table = 'contab_doc_encabezados'; 

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_empresa_id','core_tercero_id','documento_soporte','descripcion','valor_total','estado','creado_por','modificado_por','codigo_referencia_tercero'];

    public $encabezado_tabla = ['Fecha','Documento','Tercero','Detalle','Valor documento','Estado','Acción'];

    public static function consultar_registros()
    {
    	return ContabDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_doc_encabezados.core_tercero_id')
                    ->leftJoin('core_empresas', 'core_empresas.id', '=', 'contab_doc_encabezados.core_empresa_id')
                    ->where('contab_doc_encabezados.core_empresa_id',Auth::user()->empresa_id)
                    ->select(
                                'contab_doc_encabezados.fecha AS campo1',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS campo2' ),
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3' ),
                                'contab_doc_encabezados.descripcion AS campo4',
                                'contab_doc_encabezados.valor_total AS campo5',
                                'contab_doc_encabezados.estado AS campo6',
                                'contab_doc_encabezados.id AS campo7')
                    ->get()
                    ->toArray();
    }



    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion( $id )
    {

        return ContabDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_doc_encabezados.core_tercero_id')
                    ->where('contab_doc_encabezados.id', $id)
                    ->select(
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                'contab_doc_encabezados.fecha',
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero_nombre_completo' ),
                                'contab_doc_encabezados.descripcion',
                                'contab_doc_encabezados.documento_soporte',
                                'contab_doc_encabezados.core_tipo_transaccion_id',
                                'contab_doc_encabezados.core_tipo_doc_app_id',
                                'contab_doc_encabezados.id',
                                'contab_doc_encabezados.creado_por',
                                'contab_doc_encabezados.consecutivo',
                                'contab_doc_encabezados.core_empresa_id',
                                'contab_doc_encabezados.valor_total',
                                'contab_doc_encabezados.estado',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                'core_terceros.numero_identificacion')
                    ->get()
                    ->first();
    }


    public function registros()
    {
        return $this->hasMany('App\Contabilidad\ContabDocRegistro');
    }

}
