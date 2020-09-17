<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\VentasPos\Pdv;

class FacturaPos extends Model
{
    protected $table = 'vtas_pos_doc_encabezados';
	protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'pdv_id', 'cajero_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'lineas_registros_medios_recaudos', 'descripcion', 'valor_total', 'estado', 'creado_por', 'modificado_por'];

    public $urls_acciones = '{"store":"pos_factura","update":"pos_factura/id_fila","imprimir":"pos_factura_imprimir/id_fila","show":"pos_factura/id_fila"}'; // ,"eliminar":"pos_factura_anular/id_fila"
	
    public $encabezado_tabla = ['Fecha', 'Documento', 'Cliente', 'Cond. pago', 'Detalle', 'Valor total', 'PDV', 'Estado', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function cliente()
    {
        return $this->belongsTo( 'App\Ventas\Cliente','cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo( 'App\Ventas\Vendedor','vendedor_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany( DocRegistro::class, 'vtas_pos_doc_encabezado_id' );
    }

    public static function consultar_registros()
    {
        $core_tipo_transaccion_id = 47; // Facturas POS
        return FacturaPos::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_doc_encabezados.core_tipo_doc_app_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_doc_encabezados.core_tercero_id')
                                ->leftJoin('vtas_pos_puntos_de_ventas', 'vtas_pos_puntos_de_ventas.id', '=', 'vtas_pos_doc_encabezados.pdv_id')
                                ->where('vtas_pos_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                ->where('vtas_pos_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                ->select(
                                    'vtas_pos_doc_encabezados.fecha AS campo1',
                                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo) AS campo2'),
                                    DB::raw('core_terceros.descripcion AS campo3'),
                                    'vtas_pos_doc_encabezados.forma_pago AS campo4',
                                    'vtas_pos_doc_encabezados.descripcion AS campo5',
                                    'vtas_pos_doc_encabezados.valor_total AS campo6',
                                    'vtas_pos_puntos_de_ventas.descripcion AS campo7',
                                    'vtas_pos_doc_encabezados.estado AS campo8',
                                    'vtas_pos_doc_encabezados.id AS campo9'
                                )
                                ->orderBy('vtas_pos_doc_encabezados.created_at', 'DESC')
                                ->get()
                                ->toArray();
    }

    public static function consultar_registros2()
    {
        $core_tipo_transaccion_id = 47; // Facturas POS
        return FacturaPos::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_doc_encabezados.core_tipo_doc_app_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_doc_encabezados.core_tercero_id')
                                ->leftJoin('vtas_pos_puntos_de_ventas', 'vtas_pos_puntos_de_ventas.id', '=', 'vtas_pos_doc_encabezados.pdv_id')
                                ->where('vtas_pos_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                ->where('vtas_pos_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                ->select(
                                    'vtas_pos_doc_encabezados.fecha AS campo1',
                                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo) AS campo2'),
                                    DB::raw('core_terceros.descripcion AS campo3'),
                                    'vtas_pos_doc_encabezados.forma_pago AS campo4',
                                    'vtas_pos_doc_encabezados.descripcion AS campo5',
                                    'vtas_pos_doc_encabezados.valor_total AS campo6',
                                    'vtas_pos_puntos_de_ventas.descripcion AS campo7',
                                    'vtas_pos_doc_encabezados.estado AS campo8',
                                    'vtas_pos_doc_encabezados.id AS campo9'
                                )
                                ->orderBy('vtas_pos_doc_encabezados.created_at', 'DESC')
                                ->paginate(500);
    }

	public static function opciones_campo_select()
    {
        $opciones = FacturaPos::where('vtas_pos_doc_encabezados.estado','Activo')
                    ->select('vtas_pos_doc_encabezados.id','vtas_pos_doc_encabezados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registro_impresion($id)
    {
        // ARREGLAR ESTO:     ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','vtas_pos_doc_encabezados.condicion_pago_id')
        return FacturaPos::where('vtas_pos_doc_encabezados.id', $id)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_doc_encabezados.core_tercero_id')
            ->leftJoin('vtas_vendedores', 'vtas_vendedores.id', '=', 'vtas_pos_doc_encabezados.vendedor_id')
            ->leftJoin('inv_doc_encabezados', 'inv_doc_encabezados.id', '=', 'vtas_pos_doc_encabezados.remision_doc_encabezado_id')
            ->leftJoin('core_tipos_docs_apps AS doc_inventarios', 'doc_inventarios.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros AS vendedores', 'vendedores.id', '=', 'vtas_vendedores.core_tercero_id')
            ->select(
                'vtas_pos_doc_encabezados.id',
                'vtas_pos_doc_encabezados.core_empresa_id',
                'vtas_pos_doc_encabezados.core_tercero_id',
                'vtas_pos_doc_encabezados.cliente_id',
                'vtas_pos_doc_encabezados.remision_doc_encabezado_id',
                'vtas_pos_doc_encabezados.core_tipo_transaccion_id',
                'vtas_pos_doc_encabezados.core_tipo_doc_app_id',
                'vtas_pos_doc_encabezados.consecutivo',
                'vtas_pos_doc_encabezados.fecha',
                'vtas_pos_doc_encabezados.fecha_vencimiento',
                'vtas_pos_doc_encabezados.pdv_id',
                'vtas_pos_doc_encabezados.descripcion',
                'vtas_pos_doc_encabezados.estado',
                'vtas_pos_doc_encabezados.creado_por',
                'vtas_pos_doc_encabezados.modificado_por',
                'vtas_pos_doc_encabezados.created_at',
                'vtas_pos_doc_encabezados.lineas_registros_medios_recaudos',
                'vtas_pos_doc_encabezados.ventas_doc_relacionado_id',
                'vtas_pos_doc_encabezados.forma_pago AS condicion_pago',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                'vtas_pos_doc_encabezados.consecutivo AS documento_transaccion_consecutivo',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
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


    public static function consultar_encabezados_documentos( $pdv_id, $fecha_desde, $fecha_hasta, $estado = null )
    {
        $array_wheres = [ 
                            'vtas_pos_doc_encabezados.pdv_id' => $pdv_id,
                            'vtas_pos_doc_encabezados.core_empresa_id' => Auth::user()->empresa_id
                        ];

        // Si se envia nulo el ID del usuario, no lo tienen en cuenta para filtrar
        if ( !is_null( $estado ) )
        {
            $array_wheres = array_merge( $array_wheres, [ 'vtas_pos_doc_encabezados.estado' => $estado ] );
        }

        return FacturaPos::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_doc_encabezados.core_tipo_doc_app_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_doc_encabezados.core_tercero_id')
                                ->leftJoin('vtas_pos_puntos_de_ventas', 'vtas_pos_puntos_de_ventas.id', '=', 'vtas_pos_doc_encabezados.pdv_id')
                                ->where( $array_wheres )
                                ->whereBetween( 'fecha', [ $fecha_desde, $fecha_hasta ] )
                                ->select(
                                    'vtas_pos_doc_encabezados.fecha AS campo1',
                                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo) AS campo2'),
                                    DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                                    'vtas_pos_doc_encabezados.forma_pago AS campo4',
                                    'vtas_pos_doc_encabezados.descripcion AS campo5',
                                    'vtas_pos_doc_encabezados.valor_total AS campo6',
                                    'vtas_pos_doc_encabezados.estado AS campo7',
                                    'vtas_pos_doc_encabezados.id AS campo8'
                                )
                                ->orderBy('vtas_pos_doc_encabezados.created_at', 'DESC')
                                ->get()
                                ->toArray();
    }
}
