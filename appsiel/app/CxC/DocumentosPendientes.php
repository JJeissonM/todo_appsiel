<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class DocumentosPendientes extends Model
{

    protected $table = 'cxc_movimientos';

    protected $fillable = [ 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'core_empresa_id', 'core_tercero_id', 'modelo_referencia_tercero_index', 'referencia_tercero_id', 'fecha', 'fecha_vencimiento', 'valor_documento', 'valor_pagado', 'saldo_pendiente', 'creado_por', 'modificado_por', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Proveedor', 'Documento prov.', 'Fecha', 'Valor documento', 'Valor pagado', 'Saldo pendiente', 'Estado'];

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public static function consultar_registros($nro_registros)
    {
        return DocumentosPendientes::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_movimientos.core_tercero_id')
            ->where('cxc_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo1'),
                DB::raw('CONCAT(cxc_movimientos.doc_proveedor_prefijo," ",cxc_movimientos.doc_proveedor_consecutivo) AS campo2'),
                'cxc_movimientos.fecha AS campo3',
                'cxc_movimientos.valor_documento AS campo4',
                'cxc_movimientos.valor_pagado AS campo5',
                'cxc_movimientos.saldo_pendiente AS campo6',
                'cxc_movimientos.estado AS campo7',
                'cxc_movimientos.id AS campo8'
            )
            ->orderBy('cxc_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);
    }
    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/compras/cxc_docuemntos_pendientes.js';

    public static function get_documentos_referencia_tercero($operador, $cadena, $fecha_corte = null ) 
    {
        $array_wheres = [
                            [ 'cxc_movimientos.core_empresa_id', '=', Auth::user()->empresa_id],
                            [ 'cxc_movimientos.core_tercero_id', $operador, $cadena]
                        ];

        if( $fecha_corte != '' && !is_null($fecha_corte) )
        {
            $array_wheres = array_merge($array_wheres, [[ 'cxc_movimientos.fecha', '<=', $fecha_corte]]);
        }

        $movimiento = DocumentosPendientes::leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_movimientos.core_tercero_id')
                                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
                                    ->where( $array_wheres )
                                    ->whereNotBetween( 'cxc_movimientos.saldo_pendiente', [-10,10] )
                                    ->select(
                                                'cxc_movimientos.id',
                                                'cxc_movimientos.core_tipo_transaccion_id',
                                                'cxc_movimientos.core_tipo_doc_app_id',
                                                'cxc_movimientos.consecutivo',
                                                'core_terceros.descripcion AS tercero',
                                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_movimientos.consecutivo) AS documento'),
                                                'cxc_movimientos.fecha',
                                                'cxc_movimientos.fecha_vencimiento',
                                                'cxc_movimientos.valor_documento',
                                                'cxc_movimientos.valor_pagado',
                                                'cxc_movimientos.saldo_pendiente',
                                                'cxc_movimientos.core_tercero_id')
                                    ->orderBy('cxc_movimientos.fecha')
                                    ->get()->toArray();

        foreach( $movimiento as $key => $value )
        {
            $array_wheres2 = [
                                ['doc_cxc_transacc_id', '=', $movimiento[$key]['core_tipo_transaccion_id'] ],
                                ['doc_cxc_tipo_doc_id', '=', $movimiento[$key]['core_tipo_doc_app_id'] ],
                                ['doc_cxc_consecutivo', '=', $movimiento[$key]['consecutivo'] ],
                                ['core_tercero_id', '=', $movimiento[$key]['core_tercero_id'] ]
                            ];
            if( $fecha_corte != '' )
            {
                $array_wheres2 = array_merge( $array_wheres2, [ ['fecha', '<=', $fecha_corte ] ] );
            }
            
            $abonos = CxcAbono::where( $array_wheres2)->sum('abono');

            $movimiento[$key]['valor_pagado'] = $abonos;
            $movimiento[$key]['saldo_pendiente'] = $movimiento[$key]['valor_documento'] - $abonos;
        }

        return $movimiento;
    }

    public static function get_documentos_pendientes_clase_cliente( $clase, $fecha_corte )
    {
        $array_wheres = [
                            [ 'cxc_movimientos.core_empresa_id', '=', Auth::user()->empresa_id],
                            [ 'vtas_clientes.clase_cliente_id', '=', $clase]
                        ];

        if( $fecha_corte != '' )
        {
            $array_wheres = array_merge($array_wheres, [[ 'cxc_movimientos.fecha', '<=', $fecha_corte]]);
        }

        $movimiento = DocumentosPendientes::leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_movimientos.core_tercero_id')
                                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
                                    ->leftJoin('vtas_clientes', 'vtas_clientes.core_tercero_id', '=', 'cxc_movimientos.core_tercero_id')
                                    ->where( $array_wheres )
                                    ->whereNotBetween( 'cxc_movimientos.saldo_pendiente', [-10,10] )
                                    ->select(
                                            'cxc_movimientos.id',
                                            'cxc_movimientos.core_tipo_transaccion_id',
                                            'cxc_movimientos.core_tipo_doc_app_id',
                                            'cxc_movimientos.consecutivo',
                                            'core_terceros.descripcion AS tercero',
                                            DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_movimientos.consecutivo) AS documento'),
                                            'cxc_movimientos.fecha',
                                            'cxc_movimientos.fecha_vencimiento',
                                            'cxc_movimientos.valor_documento',
                                            'cxc_movimientos.valor_pagado',
                                            'cxc_movimientos.saldo_pendiente',
                                            'vtas_clientes.clase_cliente_id',
                                            'cxc_movimientos.core_tercero_id')
                                    ->orderBy('cxc_movimientos.fecha')
                                    ->get()->toArray();

        foreach( $movimiento as $key => $value )
        {
            $array_wheres2 = [
                                ['doc_cxc_transacc_id', '=', $movimiento[$key]['core_tipo_transaccion_id'] ],
                                ['doc_cxc_tipo_doc_id', '=', $movimiento[$key]['core_tipo_doc_app_id'] ],
                                ['doc_cxc_consecutivo', '=', $movimiento[$key]['consecutivo'] ],
                                ['core_tercero_id', '=', $movimiento[$key]['core_tercero_id'] ]
                            ];
                            
            if( $fecha_corte != '' )
            {
                $array_wheres2 = array_merge( $array_wheres2, [ ['fecha', '<=', $fecha_corte ] ] );
            }
            
            $abonos = CxcAbono::where( $array_wheres2)->sum('abono');

            $movimiento[$key]['valor_pagado'] = $abonos;
            $movimiento[$key]['saldo_pendiente'] = $movimiento[$key]['valor_documento'] - $abonos;
        }

        return $movimiento;
    }

}
