<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\VentasPos\Pdv;

class FacturaPos extends Model
{
    protected $table = 'vtas_pos_doc_encabezados';
	protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'pdv_id', 'cajero_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'descripcion', 'valor_total', 'estado', 'creado_por', 'modificado_por'];

    public $urls_acciones = '{"store":"pos_factura","imprimir":"pos_factura_imprimir/id_fila","show":"no"}'; // ,"eliminar":"pos_factura_anular/id_fila"
	
    public $encabezado_tabla = ['Fecha', 'Documento', 'Cliente', 'Detalle', 'Valor total', 'PDV', 'Estado', 'Acción'];

    public static function consultar_registros()
    {
        //$pdv = Pdv::where('cajero_default_id',Auth::user()->id)->get()->first();

        $core_tipo_transaccion_id = 47; // Facturas POS
        return FacturaPos::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_doc_encabezados.core_tipo_doc_app_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_doc_encabezados.core_tercero_id')
                                ->leftJoin('vtas_pos_puntos_de_ventas', 'vtas_pos_puntos_de_ventas.id', '=', 'vtas_pos_doc_encabezados.pdv_id')
                                //->where('vtas_pos_doc_encabezados.pdv_id', $pdv->id )
                                ->where('vtas_pos_doc_encabezados.estado', 'Pendiente')
                                ->where('vtas_pos_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                ->where('vtas_pos_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                ->select(
                                    'vtas_pos_doc_encabezados.fecha AS campo1',
                                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo) AS campo2'),
                                    DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                                    'vtas_pos_doc_encabezados.descripcion AS campo4',
                                    'vtas_pos_doc_encabezados.valor_total AS campo5',
                                    'vtas_pos_puntos_de_ventas.descripcion AS campo6',
                                    'vtas_pos_doc_encabezados.estado AS campo7',
                                    'vtas_pos_doc_encabezados.id AS campo8'
                                )
                                ->orderBy('vtas_pos_doc_encabezados.created_at', 'DESC')
                                ->get()
                                ->toArray();
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
                'vtas_pos_doc_encabezados.descripcion',
                'vtas_pos_doc_encabezados.estado',
                'vtas_pos_doc_encabezados.creado_por',
                'vtas_pos_doc_encabezados.modificado_por',
                'vtas_pos_doc_encabezados.created_at',
                'vtas_pos_doc_encabezados.orden_compras',
                'vtas_pos_doc_encabezados.ventas_doc_relacionado_id',
                'vtas_pos_doc_encabezados.forma_pago AS condicion_pago',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                'vtas_pos_doc_encabezados.consecutivo AS documento_transaccion_consecutivo',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo) AS documento_transaccion_prefijo_consecutivo'),
                DB::raw('CONCAT(doc_inventarios.prefijo," ",inv_doc_encabezados.consecutivo) AS documento_remision_prefijo_consecutivo'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero_nombre_completo'),
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1',
                DB::raw('CONCAT(vendedores.apellido1, " ",vendedores.apellido2, " ",vendedores.nombre1, " ",vendedores.otros_nombres) AS vendedor_nombre_completo')
            )
            ->get()
            ->first();
    }
}
