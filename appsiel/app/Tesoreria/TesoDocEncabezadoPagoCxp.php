<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TesoDocEncabezadoPagoCxp extends Model
{
    // Apunta a la misma tabla de los modelos de tesorería
    protected $table = 'teso_doc_encabezados';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'codigo_referencia_tercero', 'teso_tipo_motivo', 'documento_soporte', 'descripcion', 'teso_medio_recaudo_id', 'teso_caja_id', 'teso_cuenta_bancaria_id', 'valor_total', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Tercero', 'Detalle', 'Valor', 'Estado'];

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public static function consultar_registros($nro_registros)
    {
        $core_tipo_transaccion_id = 33;
        return TesoDocEncabezadoPagoCxp::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('teso_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'teso_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'teso_doc_encabezados.descripcion AS campo4',
                'teso_doc_encabezados.valor_total AS campo5',
                'teso_doc_encabezados.estado AS campo6',
                'teso_doc_encabezados.id AS campo7'
            )
            ->orderBy('teso_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }


    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS documento';

        return TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
            ->where('teso_doc_encabezados.id', $id)
            ->select(
                DB::raw($select_raw),
                'teso_doc_encabezados.fecha',
                'core_terceros.descripcion AS tercero',
                'teso_doc_encabezados.descripcion AS detalle',
                'teso_doc_encabezados.documento_soporte',
                'teso_doc_encabezados.core_tipo_transaccion_id',
                'teso_doc_encabezados.core_tipo_doc_app_id',
                'teso_doc_encabezados.id',
                'teso_doc_encabezados.creado_por',
                'teso_doc_encabezados.created_at',
                'teso_doc_encabezados.consecutivo',
                'teso_doc_encabezados.core_empresa_id',
                'teso_doc_encabezados.core_tercero_id',
                'teso_doc_encabezados.teso_tipo_motivo'
            )
            ->get()[0];
    }
}
