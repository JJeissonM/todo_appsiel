<?php

namespace App\CxP;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxpDocEncabezado extends Model
{
    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'fecha_vencimiento', 'core_empresa_id', 'core_tercero_id', 'tipo_documento', 'documento_soporte', 'descripcion', 'valor_total', 'estado', 'creado_por', 'modificado_por', 'codigo_referencia_tercero'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Tercero', 'Detalle', 'Valor Total', 'Estado'];

    // Este array se puede usar para automatizar los campos que se muestran en la vista index (ruta /web), permitiendo agregar o quitar campos a la tabla
    public $campos_vista_index = [
        [
            'modo_select' => 'raw',
            'etiqueta' => 'Documento',
            'campo' => 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo)'
        ],
        [
            'modo_select' => 'normal',
            'etiqueta' => 'Fecha',
            'campo' => 'cxp_doc_encabezados.fecha'
        ],
        [
            'modo_select' => 'normal',
            'etiqueta' => 'Propietario',
            'campo' => 'core_terceros.descripcion'
        ],
        [
            'modo_select' => 'normal',
            'etiqueta' => 'Detalle',
            'campo' => 'cxp_doc_encabezados.descripcion'
        ],
        [
            'modo_select' => 'normal',
            'etiqueta' => 'Acción',
            'campo' => 'cxp_doc_encabezados.id'
        ]
    ];


    // Se consultan los documentos para la empresa que tiene asignada el usuario
    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 39; // Cruce de cxp

        return CxpDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_doc_encabezados.core_tercero_id')
            ->where('cxp_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('cxp_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'cxp_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS campo2'),
                'core_terceros.descripcion as campo3',
                'cxp_doc_encabezados.descripcion AS campo4',
                'cxp_doc_encabezados.valor_total AS campo5',
                'cxp_doc_encabezados.estado AS campo6',
                'cxp_doc_encabezados.id AS campo7'
            )
            ->where("cxp_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("cxp_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("cxp_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("cxp_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('cxp_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 39; // Cruce de cxp

        $string = CxpDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_doc_encabezados.core_tercero_id')
            ->where('cxp_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('cxp_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'cxp_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS DOCUMENTO'),
                'core_terceros.descripcion as TERCERO',
                'cxp_doc_encabezados.descripcion AS DETALLE',
                'cxp_doc_encabezados.valor_total AS VALOR_TOTAL',
                'cxp_doc_encabezados.estado AS ESTADO'
            )
            ->where("cxp_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("cxp_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("cxp_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("cxp_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('cxp_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCUMENTOS PAGOS DE CxP";
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public function documentos()
    {
        return $this->hasMany('App\CxC\CxcDocumento');
    }

    public function registros()
    {
        return $this->hasMany('App\CxC\CxcRegistro');
    }

    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS documento_app';

        $registro = CxpDocEncabezado::where('cxp_doc_encabezados.id', $id)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'cxp_doc_encabezados.core_empresa_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_doc_encabezados.core_tercero_id')
            ->select(
                DB::raw($select_raw),
                'cxp_doc_encabezados.id',
                'cxp_doc_encabezados.fecha',
                'core_terceros.descripcion',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1',
                'cxp_doc_encabezados.codigo_referencia_tercero',
                'cxp_doc_encabezados.descripcion AS detalle',
                'cxp_doc_encabezados.core_empresa_id',
                'cxp_doc_encabezados.core_tipo_transaccion_id',
                'cxp_doc_encabezados.core_tipo_doc_app_id',
                'cxp_doc_encabezados.consecutivo',
                'cxp_doc_encabezados.fecha_vencimiento',
                'cxp_doc_encabezados.core_tercero_id',
                'cxp_doc_encabezados.valor_total',
                'cxp_doc_encabezados.creado_por'
            )
            ->get()[0];

        return $registro;
    }

    public static function get_registro_impresion($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS documento_app';

        $registro = CxpDocEncabezado::where('cxp_doc_encabezados.id', $id)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_empresas', 'core_empresas.id', '=', 'cxp_doc_encabezados.core_empresa_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_doc_encabezados.core_tercero_id')
            ->select(
                DB::raw($select_raw),
                'cxp_doc_encabezados.id',
                'cxp_doc_encabezados.fecha',
                'cxp_doc_encabezados.descripcion',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1',
                'cxp_doc_encabezados.codigo_referencia_tercero',
                'cxp_doc_encabezados.descripcion AS detalle',
                'cxp_doc_encabezados.core_empresa_id',
                'cxp_doc_encabezados.core_tipo_transaccion_id',
                'cxp_doc_encabezados.core_tipo_doc_app_id',
                'cxp_doc_encabezados.consecutivo',
                'cxp_doc_encabezados.fecha_vencimiento',
                'cxp_doc_encabezados.core_tercero_id',
                'cxp_doc_encabezados.valor_total',
                'cxp_doc_encabezados.creado_por',
                'cxp_doc_encabezados.created_at',
                'cxp_doc_encabezados.estado',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo')
            )
            ->get()[0];

        return $registro;
    }
}
