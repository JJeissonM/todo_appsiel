<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Movimiento extends Model
{
    protected $table = 'vtas_pos_movimientos';
	protected $fillable = ['pdv_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'codigo_referencia_tercero', 'remision_doc_encabezado_id', 'cliente_id', 'vendedor_id', 'cajero_id', 'zona_id', 'clase_cliente_id', 'equipo_ventas_id', 'forma_pago', 'fecha_vencimiento', 'orden_compras', 'inv_producto_id', 'inv_bodega_id', 'vtas_motivo_id', 'inv_motivo_id', 'precio_unitario', 'cantidad', 'precio_total', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'base_impuesto_total', 'tasa_descuento', 'valor_total_descuento', 'estado', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Producto', 'Precio Unit.', 'Cantidad', 'Precio total', 'Estado'];
    
    public function tipo_transaccion()
    {
        return $this->belongsTo( 'App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id' );
    }
    
    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function pdv()
    {
        return $this->belongsTo(Pdv::class,'pdv_id');
    }

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return Movimiento::select(
            'vtas_pos_movimientos.fecha AS campo1',
            'vtas_pos_movimientos.core_empresa_id AS campo2',
            'vtas_pos_movimientos.cliente_id AS campo3',
            'vtas_pos_movimientos.inv_producto_id AS campo4',
            'vtas_pos_movimientos.precio_unitario AS campo5',
            'vtas_pos_movimientos.cantidad AS campo6',
            'vtas_pos_movimientos.precio_total AS campo7',
            'vtas_pos_movimientos.estado AS campo8',
            'vtas_pos_movimientos.id AS campo9'
        )
            ->where("vtas_pos_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.core_empresa_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cliente_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.inv_producto_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Movimiento::select(
            'vtas_pos_movimientos.fecha AS FECHA',
            'vtas_pos_movimientos.core_empresa_id AS DOCUMENTO',
            'vtas_pos_movimientos.cliente_id AS CLIENTE',
            'vtas_pos_movimientos.inv_producto_id AS PRODUCTO',
            'vtas_pos_movimientos.precio_unitario AS PRECIO_UNIT.',
            'vtas_pos_movimientos.cantidad AS CANTIDAD',
            'vtas_pos_movimientos.precio_total AS PRECIO_TOTAL',
            'vtas_pos_movimientos.estado AS ESTADO'
        )
            ->where("vtas_pos_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.core_empresa_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cliente_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.inv_producto_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTOS POS";
    }

	public static function opciones_campo_select()
    {
        $opciones = Movimiento::where('vtas_pos_movimientos.estado','Activo')
                    ->select('vtas_pos_movimientos.id','vtas_pos_movimientos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_movimiento_ventas( $fecha_desde, $fecha_hasta, $agrupar_por )
    {
        switch ( $agrupar_por )
        {
            case 'pdv_id':
                $agrupar_por = 'pdv_id';
                break;
            case 'cliente_id':
                $agrupar_por = 'cliente';
                break;
            case 'core_tercero_id':
                $agrupar_por = 'core_tercero_id';
                break;
            case 'inv_producto_id':
                $agrupar_por = 'producto';
                break;
                case 'tasa_impuesto':
                    $agrupar_por = 'tasa_impuesto';
                    break;
            case 'vendedor_id':
                $agrupar_por = 'vendedor_id';
                break;
            case 'clase_cliente_id':
                $agrupar_por = 'clase_cliente';
                break;
            case 'core_tipo_transaccion_id':
                $agrupar_por = 'descripcion_tipo_transaccion';
                break;
            case 'forma_pago':
                $agrupar_por = 'forma_pago';
                break;
            
            default:
                break;
        }

        $movimiento = Movimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_pos_movimientos.inv_producto_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_movimientos.core_tercero_id')
                            ->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_pos_movimientos.clase_cliente_id')
                            ->leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'vtas_pos_movimientos.core_tipo_transaccion_id')
                            ->where('vtas_pos_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->select(
                                        'vtas_pos_movimientos.inv_producto_id',
                                        DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, " ", inv_productos.unidad_medida2, ")" ) AS producto'),
                                        DB::raw('CONCAT( core_terceros.numero_identificacion, " - ", core_terceros.descripcion ) AS cliente'),
                                        'vtas_pos_movimientos.cliente_id',
                                        'vtas_pos_movimientos.core_tercero_id',
                                        'vtas_clases_clientes.descripcion AS clase_cliente',
                                        'vtas_pos_movimientos.tasa_impuesto AS tasa_impuesto',
                                        'sys_tipos_transacciones.descripcion AS descripcion_tipo_transaccion',
                                        'vtas_pos_movimientos.pdv_id',
                                        'vtas_pos_movimientos.forma_pago',
                                        'vtas_pos_movimientos.vendedor_id',
                                        'vtas_pos_movimientos.cantidad',
                                        'vtas_pos_movimientos.precio_total',
                                        'vtas_pos_movimientos.base_impuesto_total',// AS base_imp_tot
                                        'vtas_pos_movimientos.tasa_descuento',
                                        'vtas_pos_movimientos.valor_total_descuento')
                            ->get();

        foreach ($movimiento as $fila)
        {
            $fila->base_impuesto_total = (float) $fila->precio_total / (1 + (float)$fila->tasa_impuesto / 100 );


            $fila->tasa_impuesto = (string)$fila->tasa_impuesto; // para poder agrupar
        }

        return $movimiento->groupBy( $agrupar_por );
    }
}
