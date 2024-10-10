<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

use App\Inventarios\InvDocEncabezado;
use App\VentasPos\Pdv;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\ResolucionFacturacion;
use Illuminate\Support\Facades\Auth;

class FacturaPos extends Model
{
    protected $table = 'vtas_pos_doc_encabezados';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'datos_temporales_cliente', 'vendedor_id', 'pdv_id', 'cajero_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'lineas_registros_medios_recaudos', 'descripcion', 'lote_acumulacion', 'valor_total', 'estado', 'creado_por', 'modificado_por'];

    public $urls_acciones = '{"store":"pos_factura","update":"pos_factura/id_fila","imprimir":"pos_factura_imprimir/id_fila","show":"pos_factura/id_fila"}'; // ,"eliminar":"pos_factura_anular/id_fila"

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Cond. pago', 'Detalle', 'Valor total', 'PDV', 'Estado'];

    public $vistas = '{"index":"layouts.index3"}';

    public function tipo_transaccion()
    {
        return $this->belongsTo('App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id');
    }

    public function tipo_documento_app()
    {
        return $this->belongsTo('App\Core\TipoDocApp', 'core_tipo_doc_app_id');
    }


    public function empresa()
    {
        return $this->belongsTo('App\Core\Empresa', 'core_empresa_id');
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public function cliente()
    {
        return $this->belongsTo('App\Ventas\Cliente', 'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo('App\Ventas\Vendedor', 'vendedor_id');
    }

    public function pdv()
    {
        return $this->belongsTo(Pdv::class, 'pdv_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany(DocRegistro::class, 'vtas_pos_doc_encabezado_id');
    }

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    } 

    // Doc. desde el cual fue generado
    public function documento_ventas_padre()
    {
        $doc_padre = FacturaPos::find( $this->ventas_doc_relacionado_id );

        if ( is_null( $doc_padre ) )
        {
            return null;
        }

        return $doc_padre;
    }

    // Doc. que se generó a partir de este
    public function documento_ventas_hijo()
    {
        $doc_hijo = FacturaPos::where( 'ventas_doc_relacionado_id', $this->id )->get()->first();
        
        if ( is_null( $doc_hijo ) )
        {
            return null;
        }

        return $doc_hijo;
    }

    public function enlaces_remisiones_hijas()
    {
        $remisiones = $this->remisiones_hijas();
        $lista = '';
        $es_el_primero = true;
        foreach ($remisiones as $remision )
        {
            if ( $es_el_primero )
            {
                $lista = $remision->enlace_show_documento();
                $es_el_primero = false;
            }else{
                $lista .= ', ' . $remision->enlace_show_documento();
            }
        }
        return $lista;
    }

    public function remisiones_hijas()
    {
        return InvDocEncabezado::where( 'vtas_doc_encabezado_origen_id', $this->id )->get();
    }

    public function enlace_show_documento()
    {
        switch ( $this->core_tipo_transaccion_id )
        {
            case '23':
                $url = 'ventas/';
                break;
            
            
            case '42':
                $url = 'vtas_pedidos/';
                break;
            
            
            case '30':
                $url = 'vtas_cotizacion/';
                break;
            
            
            case '47':
                $url = 'pos_factura/';
                break;
            
            default:
                $url = 'ventas/';
                break;
        }

        $enlace = '<a href="' . url( $url . $this->id . '?id=' . Input::get('id') . '&id_modelo=' . $this->tipo_transaccion->core_modelo_id . '&id_transaccion=' . $this->core_tipo_transaccion_id ) . '" target="_blank">' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo . '</a>';

        return $enlace;
    }

    public function resolucion_facturacion()
    {
        return ResolucionFacturacion::where( 'tipo_doc_app_id', $this->core_tipo_doc_app_id )
                                //->where('estado','Activo')
                                ->get()
                                ->last();
    }

    // SOLO PARA UN MOVIMIENTO. No funciona Si se paga con varios medios de pago
    public function movimiento_tesoreria()
    {
        return TesoMovimiento::where( [
                                        ['core_tipo_transaccion_id', $this->core_tipo_transaccion_id ],
                                        ['core_tipo_doc_app_id', $this->core_tipo_doc_app_id ],
                                        ['consecutivo', $this->consecutivo ]
                                    ] 
                                )
                                ->get()
                                ->first();
    }

    public function medio_pago()
    {
        $lineas_registros_medios_recaudos = $this->lineas_registros_medios_recaudos;

        $linea_medio_pago = json_decode($lineas_registros_medios_recaudos)[0];

        return explode('-', $linea_medio_pago->teso_medio_recaudo_id)[1];
    }

    public function caja_banco()
    {
        $lineas_registros_medios_recaudos = $this->lineas_registros_medios_recaudos;

        $linea_medio_pago = json_decode($lineas_registros_medios_recaudos)[0];

        $caja = explode('-', $linea_medio_pago->teso_caja_id);
        if ( $caja[0] != 0 )
        {
            return $caja[1];
        }

        $cuenta_bancaria = explode('-', $linea_medio_pago->teso_cuenta_bancaria_id);
        if ( $cuenta_bancaria[0] != 0 )
        {
            return $cuenta_bancaria[1];
        }
    }

    public static function consultar_registros($nro_registros, $search)
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
            ->where("vtas_pos_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
            ->orWhere("vtas_pos_doc_encabezados.forma_pago", "LIKE", "%$search%")
            ->orWhere("vtas_pos_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("vtas_pos_puntos_de_ventas.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 47; // Facturas POS
        
        $texto_busqueda = '%' . str_replace( " ", "%", $search ) . '%';

        $string = FacturaPos::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_doc_encabezados.core_tercero_id')
            ->leftJoin('vtas_pos_puntos_de_ventas', 'vtas_pos_puntos_de_ventas.id', '=', 'vtas_pos_doc_encabezados.pdv_id')
            ->where('vtas_pos_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_pos_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->having('nueva_cadena', 'LIKE', $texto_busqueda)
            ->select(
                DB::raw('CONCAT( vtas_pos_doc_encabezados.fecha, " ", core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo, " ", core_terceros.descripcion, " ", vtas_pos_doc_encabezados.descripcion, " ", vtas_pos_doc_encabezados.valor_total, " ", vtas_pos_doc_encabezados.forma_pago, " ", vtas_pos_doc_encabezados.estado) AS nueva_cadena'),
                'vtas_pos_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('core_terceros.numero_identificacion AS CC_NIT'),
                DB::raw('core_terceros.descripcion AS CLIENTE'),
                'vtas_pos_doc_encabezados.forma_pago AS COND._PAGO',
                'vtas_pos_doc_encabezados.descripcion AS DETALLE',
                'vtas_pos_doc_encabezados.valor_total AS VALOR_TOTAL',
                'vtas_pos_puntos_de_ventas.descripcion AS PDV',
                'vtas_pos_doc_encabezados.estado AS ESTADO'
            )
            ->orderBy('vtas_pos_doc_encabezados.created_at', 'DESC')
            ->toSql();
            
        $string = str_replace('`vtas_pos_doc_encabezados`.`core_empresa_id` = ?', '`vtas_pos_doc_encabezados`.`core_empresa_id` = ' . Auth::user()->empresa_id, $string);
        
        $string = str_replace('`vtas_pos_doc_encabezados`.`core_tipo_transaccion_id` = ?', '`vtas_pos_doc_encabezados`.`core_tipo_transaccion_id` = ' . $core_tipo_transaccion_id, $string);

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE FACTURAS POS";
    }

    public static function consultar_registros2($nro_registros, $search)
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
            ->where("vtas_pos_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
            ->orWhere("vtas_pos_doc_encabezados.forma_pago", "LIKE", "%$search%")
            ->orWhere("vtas_pos_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("vtas_pos_puntos_de_ventas.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function opciones_campo_select()
    {
        $opciones = FacturaPos::where('vtas_pos_doc_encabezados.estado', 'Activo')
            ->select('vtas_pos_doc_encabezados.id', 'vtas_pos_doc_encabezados.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
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
                'vtas_pos_doc_encabezados.vendedor_id',
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
                'vtas_pos_doc_encabezados.valor_total',
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


    public static function consultar_encabezados_documentos($pdv_id, $fecha_desde, $fecha_hasta, $estado = null)
    {
        $array_wheres = [
            'vtas_pos_doc_encabezados.pdv_id' => $pdv_id,
            'vtas_pos_doc_encabezados.core_empresa_id' => Auth::user()->empresa_id
        ];

        // Si se envia nulo el ID del usuario, no lo tienen en cuenta para filtrar
        if (!is_null($estado)) {
            $array_wheres = array_merge($array_wheres, ['vtas_pos_doc_encabezados.estado' => $estado]);
        }

        return FacturaPos::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_doc_encabezados.core_tercero_id')
            ->leftJoin('vtas_pos_puntos_de_ventas', 'vtas_pos_puntos_de_ventas.id', '=', 'vtas_pos_doc_encabezados.pdv_id')
            ->where($array_wheres)
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->select(
                'vtas_pos_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                'vtas_pos_doc_encabezados.forma_pago AS campo4',
                'vtas_pos_doc_encabezados.descripcion AS campo5',
                'vtas_pos_doc_encabezados.valor_total AS campo6',
                'vtas_pos_doc_encabezados.lineas_registros_medios_recaudos AS campo7',
                'vtas_pos_doc_encabezados.estado AS campo8',
                'vtas_pos_doc_encabezados.id AS campo9'
            )
            ->orderBy('vtas_pos_doc_encabezados.created_at', 'DESC')
            ->get()
            ->toArray();
    }
}
