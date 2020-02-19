<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ContabDocEncabezado extends Model
{
    //protected $table = 'contab_doc_encabezados'; 

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_empresa_id','core_tercero_id','documento_soporte','descripcion','valor_total','estado','creado_por','modificado_por','codigo_referencia_tercero'];

    public $encabezado_tabla = ['Documento','Fecha','Tercero','Detalle','Inmueble','Acción'];

    public static function consultar_registros()
    {
    	$select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",contab_doc_encabezados.consecutivo) AS campo1';

        $select_raw2 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3';

        $select_raw3 = 'CONCAT(ph_propiedades.codigo," ",ph_propiedades.nomenclatura) AS campo5';

        $registros = ContabDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_doc_encabezados.core_tercero_id')
                    ->leftJoin('core_empresas', 'core_empresas.id', '=', 'contab_doc_encabezados.core_empresa_id')
                    ->leftJoin('ph_propiedades', 'ph_propiedades.codigo', '=', 'contab_doc_encabezados.codigo_referencia_tercero')
                    ->where('contab_doc_encabezados.core_empresa_id',Auth::user()->empresa_id)
                    ->select(DB::raw($select_raw),'contab_doc_encabezados.fecha AS campo2',DB::raw($select_raw2),'contab_doc_encabezados.descripcion AS campo4',DB::raw($select_raw3),'contab_doc_encabezados.id AS campo6')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function registros()
    {
        return $this->hasMany('App\Contabilidad\ContabDocRegistro');
    }

}
