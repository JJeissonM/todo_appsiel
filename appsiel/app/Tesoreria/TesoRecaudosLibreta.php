<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

class TesoRecaudosLibreta extends Model
{
    public $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','id_libreta', 'id_cartera', 'concepto', 'fecha_recaudo', 'teso_medio_recaudo_id', 'cantidad_cuotas','valor_recaudo','mi_token','creado_por','modificado_por'];

    public $encabezado_tabla = ['Documento','Fecha','Tercero','Detalle','Valor','Acción'];

    public static function consultar_registros()
    {
    	return TesoRecaudosLibreta::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->select( 
                    			DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo1'),
                                'teso_doc_encabezados.fecha_recaudo AS campo2',
                                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                                'teso_doc_encabezados.concepto AS campo4',
                                'teso_doc_encabezados.valor_recaudo AS campo5',
                                'teso_doc_encabezados.id AS campo6')
                    ->get()
                    ->toArray();

    }
}
