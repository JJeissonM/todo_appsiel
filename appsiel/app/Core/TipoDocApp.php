<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use App\Core\ConsecutivoDocumento;

class TipoDocApp extends Model
{
    protected $table = 'core_tipos_docs_apps';

    protected $fillable = ['prefijo', 'descripcion', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'ID', 'Prefijo', 'Descripción', 'Estado'];

    public function resolucion_facturacion()
    {
        return $this->hasMany('App\Ventas\ResolucionFacturacion', 'tipo_doc_app_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = TipoDocApp::select(
            'core_tipos_docs_apps.id AS campo1',
            'core_tipos_docs_apps.prefijo AS campo2',
            'core_tipos_docs_apps.descripcion AS campo3',
            'core_tipos_docs_apps.estado AS campo4',
            'core_tipos_docs_apps.id AS campo5'
        )
            ->where("core_tipos_docs_apps.id", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.prefijo", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.descripcion", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.estado", "LIKE", "%$search%")
            ->orderBy('core_tipos_docs_apps.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = TipoDocApp::select(
            'core_tipos_docs_apps.id AS ID',
            'core_tipos_docs_apps.prefijo AS PREFIJO',
            'core_tipos_docs_apps.descripcion AS DESCRIPCIÓN',
            'core_tipos_docs_apps.estado AS ESTADO'
        )
            ->where("core_tipos_docs_apps.id", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.prefijo", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.descripcion", "LIKE", "%$search%")
            ->orWhere("core_tipos_docs_apps.estado", "LIKE", "%$search%")
            ->orderBy('core_tipos_docs_apps.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TIPOS DOCS APPS";
    }

    public static function opciones_campo_select()
    {
        $opciones = TipoDocApp::select('core_tipos_docs_apps.id', 'core_tipos_docs_apps.descripcion', 'core_tipos_docs_apps.prefijo')
            ->orderBy('descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->prefijo . ' ' . $opcion->descripcion . ' (' . $opcion->id . ')';
        }

        return $vec;
    }

    // Reading a counter is a preview, never a reservation.
    public static function get_consecutivo_actual($core_empresa_id, $tipo_doc_app_id)
    {
        $value = ConsecutivoDocumento::where('core_empresa_id', $core_empresa_id)
            ->where('core_documento_app_id', $tipo_doc_app_id)
            ->value('consecutivo_actual');

        return $value === null ? 0 : (int)$value;
    }

    public static function reservar_consecutivo($core_empresa_id, $tipo_doc_app_id)
    {
        return \App\Core\Services\DocumentSequenceService::reserve($core_empresa_id, $tipo_doc_app_id);
    }

    /** @deprecated Use reservar_consecutivo and its returned number directly. */
    public static function aumentar_consecutivo($core_empresa_id, $tipo_doc_app_id)
    {
        throw new \LogicException('Use reservar_consecutivo y su número retornado; la lectura y el incremento separados no son seguros.');
    }
}
