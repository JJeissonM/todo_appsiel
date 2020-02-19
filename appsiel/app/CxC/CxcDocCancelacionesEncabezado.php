<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxcDocCancelacionesEncabezado extends Model
{
    protected $table = 'cxc_doc_encabezados'; 

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','fecha_vencimiento','core_empresa_id','core_tercero_id','tipo_movimiento','documento_soporte','descripcion','valor_total','estado','creado_por','modificado_por','codigo_referencia_tercero'];

    public $encabezado_tabla = ['Documento','Fecha','Inmueble','Propietario','Detalle','Acción'];

    
    // Se consultan los documentos para la empresa que tiene asignada el usuario
    public static function consultar_registros()
    {        
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo) AS campo1';

        $select_raw2 = 'CONCAT(ph_propiedades.codigo," ",ph_propiedades.nomenclatura) AS campo3';

        $core_tipo_transaccion_id = 18;

        $registros = CxcDocCancelacionesEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('ph_propiedades', 'ph_propiedades.id', '=', 'cxc_doc_encabezados.codigo_referencia_tercero')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_doc_encabezados.core_tercero_id')
                    ->where('cxc_doc_encabezados.core_empresa_id', Auth::user()->empresa_id )
                    ->where('cxc_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id )
                    ->select(DB::raw($select_raw),'cxc_doc_encabezados.fecha AS campo2',DB::raw($select_raw2),'core_terceros.descripcion as campo4','cxc_doc_encabezados.descripcion AS campo5','cxc_doc_encabezados.id AS campo6')
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
                    ->leftJoin('ph_propiedades', 'ph_propiedades.id', '=', 'cxc_doc_encabezados.codigo_referencia_tercero')
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
                        'cxc_doc_encabezados.valor_total',
                        'ph_propiedades.nomenclatura',
                        'ph_propiedades.nombre_arrendatario',
                        'ph_propiedades.telefono_arrendatario',
                        'ph_propiedades.email_arrendatario',
                        'ph_propiedades.tipo_propiedad',
                        'ph_propiedades.codigo AS codigo_inmueble')
                    ->get()[0];

        return $registro;
    }
}
