<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Inventarios\InvDocEncabezado;

class VtasDocEncabezado extends Model
{
    //protected $table = 'vtas_doc_encabezados'; 

    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'valor_total'];

    public $encabezado_tabla = ['Fecha', 'Documento', 'Cliente', 'Detalle', 'Valor total', 'Estado', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function cliente()
    {
        return $this->belongsTo( Cliente::class,'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo( Vendedor::class,'vendedor_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany( VtasDocRegistro::class, 'vtas_doc_encabezado_id' );
    }

    public function movimientos()
    {
        return $this->hasMany( VtasMovimiento::class );
    }
    

    public static function consultar_registros()
    {
        $core_tipo_transaccion_id = 23; // Facturas
        return VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('core_terceros.descripcion AS campo3'),
                'vtas_doc_encabezados.descripcion AS campo4',
                'vtas_doc_encabezados.valor_total AS campo5',
                'vtas_doc_encabezados.estado AS campo6',
                'vtas_doc_encabezados.id AS campo7'
            )
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->get()
            ->toArray();
        /*
                    ->leftJoin('vtas_doc_registros', 'vtas_doc_registros.vtas_doc_encabezado_id', '=', 'vtas_doc_encabezados.id')
                                DB::raw( 'SUM(vtas_doc_registros.precio_total) AS campo5' ),
                    */
    }

    public static function consultar_registros2()
    {
        $core_tipo_transaccion_id = 23; // Facturas
        return VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('core_terceros.descripcion AS campo3'),
                'vtas_doc_encabezados.descripcion AS campo4',
                'vtas_doc_encabezados.valor_total AS campo5',
                'vtas_doc_encabezados.estado AS campo6',
                'vtas_doc_encabezados.id AS campo7'
            )
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->paginate(500);
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {
        // ARREGLAR ESTO:     ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','vtas_doc_encabezados.condicion_pago_id')
        return VtasDocEncabezado::where('vtas_doc_encabezados.id', $id)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->leftJoin('vtas_vendedores', 'vtas_vendedores.id', '=', 'vtas_doc_encabezados.vendedor_id')
            ->leftJoin('inv_doc_encabezados', 'inv_doc_encabezados.id', '=', 'vtas_doc_encabezados.remision_doc_encabezado_id')
            ->leftJoin('core_tipos_docs_apps AS doc_inventarios', 'doc_inventarios.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros AS vendedores', 'vendedores.id', '=', 'vtas_vendedores.core_tercero_id')
            ->select(
                'vtas_doc_encabezados.id',
                'vtas_doc_encabezados.core_empresa_id',
                'vtas_doc_encabezados.core_tercero_id',
                'vtas_doc_encabezados.cliente_id',
                'vtas_doc_encabezados.remision_doc_encabezado_id',
                'vtas_doc_encabezados.core_tipo_transaccion_id',
                'vtas_doc_encabezados.core_tipo_doc_app_id',
                'vtas_doc_encabezados.consecutivo',
                'vtas_doc_encabezados.fecha',
                'vtas_doc_encabezados.fecha_vencimiento',
                'vtas_doc_encabezados.vendedor_id',
                'vtas_doc_encabezados.descripcion',
                'vtas_doc_encabezados.estado',
                'vtas_doc_encabezados.creado_por',
                'vtas_doc_encabezados.modificado_por',
                'vtas_doc_encabezados.created_at',
                'vtas_doc_encabezados.orden_compras',
                'vtas_doc_encabezados.ventas_doc_relacionado_id',
                'vtas_doc_encabezados.forma_pago AS condicion_pago',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                'vtas_doc_encabezados.consecutivo AS documento_transaccion_consecutivo',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
                DB::raw('CONCAT(doc_inventarios.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_remision_prefijo_consecutivo'),
                DB::raw('core_terceros.descripcion AS tercero_nombre_completo'),
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1',
                DB::raw('CONCAT(vendedores.apellido1, " ",vendedores.apellido2, " ",vendedores.nombre1, " ",vendedores.otros_nombres) AS vendedor_nombre_completo')
            )
            ->get()
            ->first();
    }

    public static function get_documentos_relacionados($doc_encabezado)
    {
        $mas_de_uno = false;
        $ids_documentos_relacionados = explode(',', $doc_encabezado->remision_doc_encabezado_id);

        $app_id = 13; // Ventas
        $modelo_doc_relacionado_id = 164; // Remisiones de ventas
        $transaccion_doc_relacionado_id = 24; // Remisión de ventas

        $cant_registros = count($ids_documentos_relacionados);

        $lista = '';
        $primer = true;
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $un_documento = InvDocEncabezado::get_registro_impresion( $ids_documentos_relacionados[$i] );
            if ( !is_null($un_documento) )
            {
                if ($primer)
                {
                    $lista .= '<a href="'.url( 'inventarios/'.$un_documento->id.'?id='.$app_id.'&id_modelo='.$modelo_doc_relacionado_id.'&id_transaccion='.$transaccion_doc_relacionado_id ).'" target="_blank">'.$un_documento->documento_transaccion_prefijo_consecutivo.'</a>';
                    $primer = false;
                }else{
                    $lista .= ', &nbsp; <a href="'.url( 'inventarios/'.$un_documento->id.'?id='.$app_id.'&id_modelo='.$modelo_doc_relacionado_id.'&id_transaccion='.$transaccion_doc_relacionado_id ).'" target="_blank">'.$un_documento->documento_transaccion_prefijo_consecutivo.'</a>';
                    $mas_de_uno = true;
                }
            }
        }
        return [$lista,$mas_de_uno];
    }

}
