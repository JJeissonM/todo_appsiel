<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TesoDocEncabezadoRecaudoCxc extends TesoDocEncabezado
{
    // Apunta a la misma tabla del modelo de Recaudos
    protected $table = 'teso_doc_encabezados'; 

    public $encabezado_tabla = ['Fecha','Documento','Tercero','Detalle','Valor Documento','Estado','Acción'];

    public static function consultar_registros()
    {
        $transaccion_id = 32;
    	return TesoDocEncabezadoRecaudoCxc::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'teso_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'teso_doc_encabezados.core_tercero_id')
                    ->where('teso_doc_encabezados.core_empresa_id',Auth::user()->empresa_id)
                    ->where('teso_doc_encabezados.core_tipo_transaccion_id', $transaccion_id)
                    ->select( 
                                'teso_doc_encabezados.fecha AS campo1',
                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",teso_doc_encabezados.consecutivo) AS campo2'),
                                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                                'teso_doc_encabezados.descripcion AS campo4',
                                'teso_doc_encabezados.valor_total AS campo5',
                                'teso_doc_encabezados.estado AS campo6',
                                'teso_doc_encabezados.id AS campo7')
                    ->get()
                    ->toArray();

    }
}
