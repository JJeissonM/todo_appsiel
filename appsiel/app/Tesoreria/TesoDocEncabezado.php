<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMedioRecaudo;

class TesoDocEncabezado extends Model
{
    //protected $table = 'teso_doc_encabezados'; 

    // teso_tipo_motivo debe desaparecer, pues se una segun el motivo de cada registro del documento, en un mismom documento pueden haber varios teso_tipo_motivo
    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_empresa_id','core_tercero_id','codigo_referencia_tercero','teso_tipo_motivo','documento_soporte','descripcion','teso_medio_recaudo_id','teso_caja_id','teso_cuenta_bancaria_id','valor_total','estado','creado_por','modificado_por'];

    public $encabezado_tabla = ['Documento','Fecha','Tercero','Detalle','Acción'];

    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function caja()
    {
        return $this->belongsTo(TesoCaja::class, 'teso_caja_id');
    }

    public function cuenta_bancaria()
    {
        return $this->belongsTo(TesoCuentaBancaria::class, 'teso_cuenta_bancaria_id');
    }

    public function medio_recaudo()
    {
        return $this->belongsTo(TesoMedioRecaudo::class, 'teso_medio_recaudo_id');
    }

    public static function consultar_registros()
    {
    	return TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->where('teso_doc_encabezados.core_empresa_id',Auth::user()->empresa_id)
                    ->select( DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo1'),
                                'teso_doc_encabezados.fecha AS campo2',
                                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                                'teso_doc_encabezados.descripcion AS campo4',
                                'teso_doc_encabezados.id AS campo5')
                    ->get()
                    ->toArray();

    }


    public static function get_un_registro($id)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS documento';

        return TesoDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->where('teso_doc_encabezados.id', $id)
                    ->select(DB::raw($select_raw),'teso_doc_encabezados.fecha','core_terceros.descripcion AS tercero','teso_doc_encabezados.descripcion AS detalle','teso_doc_encabezados.documento_soporte','teso_doc_encabezados.core_tipo_transaccion_id','teso_doc_encabezados.core_tipo_doc_app_id','teso_doc_encabezados.id','teso_doc_encabezados.creado_por','teso_doc_encabezados.consecutivo','teso_doc_encabezados.core_empresa_id','teso_doc_encabezados.core_tercero_id','teso_doc_encabezados.teso_tipo_motivo')
                    ->get()[0];
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {

        return TesoDocEncabezado::where('teso_doc_encabezados.id',$id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->select(
                                'teso_doc_encabezados.id',
                                'teso_doc_encabezados.core_empresa_id',
                                'teso_doc_encabezados.core_tercero_id',
                                'teso_doc_encabezados.core_tipo_transaccion_id',
                                'teso_doc_encabezados.core_tipo_doc_app_id',
                                'teso_doc_encabezados.consecutivo',
                                'teso_doc_encabezados.teso_caja_id',
                                'teso_doc_encabezados.fecha',
                                'teso_doc_encabezados.descripcion',
                                'teso_doc_encabezados.teso_medio_recaudo_id',
                                'teso_doc_encabezados.teso_caja_id',
                                'teso_doc_encabezados.teso_cuenta_bancaria_id',
                                'teso_doc_encabezados.valor_total',
                                'teso_doc_encabezados.estado',
                                'teso_doc_encabezados.creado_por',
                                'teso_doc_encabezados.created_at',
                                'teso_doc_encabezados.modificado_por',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo' ),
                                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social," (",core_terceros.descripcion,")") AS tercero_nombre_completo' ),
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get()
                    ->first();
    }
}
