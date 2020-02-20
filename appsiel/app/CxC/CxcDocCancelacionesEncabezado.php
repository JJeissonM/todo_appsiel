<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxcDocCancelacionesEncabezado extends Model
{
    protected $table = 'cxc_doc_encabezados'; 

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','fecha_vencimiento','core_empresa_id','core_tercero_id','tipo_movimiento','documento_soporte','descripcion','valor_total','estado','creado_por','modificado_por','codigo_referencia_tercero'];

    public $encabezado_tabla = ['Documento','Fecha','Tercero','Detalle','Acción'];

    
    // Se consultan los documentos para la empresa que tiene asignada el usuario
    public static function consultar_registros()
    {        
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo) AS campo1';

        $core_tipo_transaccion_id = 18;

        $registros = CxcDocCancelacionesEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_doc_encabezados.core_tercero_id')
                    ->where('cxc_doc_encabezados.core_empresa_id', Auth::user()->empresa_id )
                    ->where('cxc_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id )
                    ->select(
                                DB::raw($select_raw),
                                'cxc_doc_encabezados.fecha AS campo2',
                                'core_terceros.descripcion as campo3',
                                'cxc_doc_encabezados.descripcion AS campo4',
                                'cxc_doc_encabezados.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function movimientos()
    {
        return $this->hasMany('App\CxC\CxcMovimiento');
    }

    public function registros()
    {
        return $this->hasMany('App\CxC\CxcRegistro');
    }

    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo) AS documento_app';

        $registro = CxcDocCancelacionesEncabezado::where('cxc_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_empresas', 'core_empresas.id', '=', 'cxc_doc_encabezados.core_empresa_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_doc_encabezados.core_tercero_id')
                    ->select( DB::raw($select_raw),
                        'cxc_doc_encabezados.fecha',
                        'core_terceros.descripcion',
                        'cxc_doc_encabezados.codigo_referencia_tercero',
                        'cxc_doc_encabezados.descripcion AS detalle',
                        'cxc_doc_encabezados.core_empresa_id',
                        'cxc_doc_encabezados.core_tipo_transaccion_id',
                        'cxc_doc_encabezados.core_tipo_doc_app_id',
                        'cxc_doc_encabezados.consecutivo',
                        'cxc_doc_encabezados.fecha_vencimiento',
                        'cxc_doc_encabezados.core_tercero_id',
                        'cxc_doc_encabezados.valor_total')
                    ->get()[0];

        return $registro;
    }
}
