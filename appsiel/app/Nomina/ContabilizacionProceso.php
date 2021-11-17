<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ContabilizacionProceso extends Model
{
    protected $table = 'nom_contabilizaciones_procesos'; 

    // proceso_contabilizado: { documento_nomina | provision_prestaciones_sociales | planilla_integrada }

    protected $fillable = [ 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'descripcion', 'proceso_contabilizado', 'estado', 'creado_por', 'modificado_por' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Proceso', 'Detalle', 'Estado'];

    public function tipo_transaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo('App\Core\TipoDocApp', 'core_tipo_doc_app_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return ContabilizacionProceso::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'contab_doc_encabezados.core_empresa_id')
            ->where('contab_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'contab_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS campo2'),
                'contab_doc_encabezados.proceso_contabilizado AS campo4',
                'contab_doc_encabezados.descripcion AS campo5',
                'contab_doc_encabezados.estado AS campo6',
                'contab_doc_encabezados.id AS campo7'
            )
            ->where("contab_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("contab_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_doc_encabezados.proceso_contabilizado", "LIKE", "%$search%")
            ->orWhere("contab_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('contab_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ContabilizacionProceso::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'contab_doc_encabezados.core_empresa_id')
            ->where('contab_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'contab_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS DOCUMENTO'),
                'contab_doc_encabezados.proceso_contabilizado AS PROCESO_CONTABILIZADO',
                'contab_doc_encabezados.descripcion AS DETALLE',
                'contab_doc_encabezados.estado AS ESTADO'
            )
            ->where("contab_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("contab_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("contab_doc_encabezados.proceso_contabilizado", "LIKE", "%$search%")
            ->orWhere("contab_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('contab_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PROCESOS CONTABILIZADOS";
    }
}
