<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxcDocCruceEncabezado extends CxcDocEncabezado
{
    protected $table = 'cxc_doc_encabezados'; 

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','fecha_vencimiento','core_empresa_id','core_tercero_id','tipo_movimiento','documento_soporte','descripcion','valor_total','estado','creado_por','modificado_por','codigo_referencia_tercero'];

    public $encabezado_tabla = ['Fecha','Documento','Valor Total','Tercero','Detalle','Acción'];

    
    // Se consultan los documentos para la empresa que tiene asignada el usuario
    public static function consultar_registros()
    {        
        $core_tipo_transaccion_id = 16;

        return CxcDocCruceEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_doc_encabezados.core_tipo_doc_app_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_doc_encabezados.core_tercero_id')
                                ->where('cxc_doc_encabezados.core_empresa_id', Auth::user()->empresa_id )
                                ->where('cxc_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id )
                                ->select(
                                            'cxc_doc_encabezados.fecha AS campo1',
                                            DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo) AS campo2' ),
                                            'cxc_doc_encabezados.valor_total AS campo3',
                                            'core_terceros.descripcion as campo4',
                                            'cxc_doc_encabezados.descripcion AS campo5',
                                            'cxc_doc_encabezados.id AS campo6')
                                ->get()
                                ->toArray();
    }
}
